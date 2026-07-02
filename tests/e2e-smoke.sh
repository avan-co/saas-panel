#!/usr/bin/env bash
# End-to-end smoke tests for BizPanel
set -euo pipefail

BASE="${BASE_URL:-http://127.0.0.1:8080}"
HOST_HEADER="${HOST_HEADER:-}"
COOKIE_JAR="/tmp/bizpanel_e2e_cookies.txt"
PASS=0
FAIL=0

rm -f "$COOKIE_JAR"

curl_get() {
  local url="$1"
  local extra=()
  if [[ -n "$HOST_HEADER" ]]; then
    extra+=(-H "Host: $HOST_HEADER" -H "X-Forwarded-Proto: https")
  fi
  curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" "${extra[@]}" "$url"
}

curl_post() {
  local url="$1"
  local data="$2"
  local extra=()
  if [[ -n "$HOST_HEADER" ]]; then
    extra+=(-H "Host: $HOST_HEADER" -H "X-Forwarded-Proto: https")
  fi
  curl -s -c "$COOKIE_JAR" -b "$COOKIE_JAR" "${extra[@]}" -X POST -d "$data" "$url"
}

curl_code() {
  local url="$1"
  local extra=()
  if [[ -n "$HOST_HEADER" ]]; then
    extra+=(-H "Host: $HOST_HEADER" -H "X-Forwarded-Proto: https")
  fi
  curl -s -o /dev/null -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" "${extra[@]}" "$url"
}

assert_contains() {
  local name="$1"
  local haystack="$2"
  local needle="$3"
  if echo "$haystack" | grep -qiF "$needle"; then
    echo "  PASS: $name"
    PASS=$((PASS + 1))
  else
    echo "  FAIL: $name (expected: $needle)"
    FAIL=$((FAIL + 1))
  fi
}

assert_not_contains() {
  local name="$1"
  local haystack="$2"
  local needle="$3"
  if echo "$haystack" | grep -qF "$needle"; then
    echo "  FAIL: $name (should not contain: $needle)"
    FAIL=$((FAIL + 1))
  else
    echo "  PASS: $name"
    PASS=$((PASS + 1))
  fi
}

assert_code() {
  local name="$1"
  local expected="$2"
  local actual="$3"
  if [[ "$actual" == "$expected" ]]; then
    echo "  PASS: $name ($actual)"
    PASS=$((PASS + 1))
  else
    echo "  FAIL: $name (expected $expected, got $actual)"
    FAIL=$((FAIL + 1))
  fi
}

get_csrf() {
  local html="$1"
  echo "$html" | sed -n 's/.*name="csrf_test_name" value="\([^"]*\)".*/\1/p' | head -1
}

echo "=== BizPanel E2E Smoke Tests ==="
echo "BASE=$BASE HOST_HEADER=${HOST_HEADER:-none}"
echo

# --- Reset install state ---
rm -f /workspace/writable/installed.lock
rm -f /workspace/.env
rm -f "$COOKIE_JAR"

mysql -u bizpanel -pbizpanel -e "DROP DATABASE IF EXISTS bizpanel_test; CREATE DATABASE bizpanel_test;" 2>/dev/null || true

echo "--- 1. Install: requirements ---"
HTML=$(curl_get "$BASE/install")
assert_contains "install page title" "$HTML" "نصب پنل کسب‌وکار"
assert_contains "requirements list" "$HTML" "requirements-list"
assert_not_contains "no debugbar" "$HTML" "debugbar_loader"
assert_not_contains "no DEBUG-VIEW" "$HTML" "DEBUG-VIEW"

if [[ -n "$HOST_HEADER" ]]; then
  assert_contains "portal install link" "$HTML" "https://${HOST_HEADER}/install/database"
  assert_not_contains "no localhost link" "$HTML" "localhost:8080"
fi

echo "--- 2. Install: database form ---"
HTML=$(curl_get "$BASE/install/database")
assert_contains "database form" "$HTML" 'name="database"'
CSRF=$(get_csrf "$HTML")

echo "--- 3. Install: save database ---"
curl_post "$BASE/install/database" "csrf_test_name=${CSRF}&hostname=localhost&port=3306&database=bizpanel_test&username=bizpanel&password=bizpanel" > /dev/null
HTML=$(curl_get "$BASE/install/setup")
assert_contains "setup form" "$HTML" 'name="admin_email"'
CSRF=$(get_csrf "$HTML")

echo "--- 4. Install: run setup ---"
SITE_URL="$BASE/"
if [[ -n "$HOST_HEADER" ]]; then
  SITE_URL="https://${HOST_HEADER}/"
fi
curl_post "$BASE/install/setup" "csrf_test_name=${CSRF}&baseURL=${SITE_URL}&admin_name=E2E+Admin&admin_email=e2e@test.local&admin_password=password123&admin_password_confirm=password123&seed_demo=1" > /dev/null
HTML=$(curl_get "$BASE/install/process")
assert_contains "process spinner" "$HTML" "executeForm"
CSRF=$(get_csrf "$HTML")

echo "--- 5. Install: execute ---"
curl_post "$BASE/install/execute" "csrf_test_name=${CSRF}" > /dev/null
CODE=$(curl_code "$BASE/install")
assert_code "install available after setup" "200" "$CODE"
test -f /workspace/writable/installed.lock && echo "  PASS: installed.lock exists" && PASS=$((PASS+1)) || { echo "  FAIL: installed.lock missing"; FAIL=$((FAIL+1)); }

echo "--- 6. Auth: login page ---"
HTML=$(curl_get "$BASE/login")
assert_contains "login form" "$HTML" 'name="email"'
CSRF=$(get_csrf "$HTML")

echo "--- 7. Auth: login attempt ---"
curl_post "$BASE/login" "csrf_test_name=${CSRF}&email=e2e@test.local&password=password123" > /dev/null
HTML=$(curl_get "$BASE/dashboard")
assert_contains "dashboard loaded" "$HTML" "kpi-grid"

echo "--- 8. Dashboard: KPI cards ---"
assert_contains "kpi card" "$HTML" "kpi-card"

echo "--- 9. Locale: switch to EN ---"
curl_get "$BASE/locale/en" > /dev/null
HTML=$(curl_get "$BASE/dashboard")
assert_contains "english locale" "$HTML" 'lang="en"'

echo "--- 10. Platform admin ---"
CODE=$(curl_code "$BASE/platform/tenants")
assert_code "platform tenants" "200" "$CODE"

echo "--- 11. Finance module ---"
HTML=$(curl_get "$BASE/module/finance")
assert_contains "finance module" "$HTML" "page-finance"

echo "--- 12. Tenant switch (restaurant demo) ---"
CODE=$(curl_code "$BASE/tenant/switch/1")
assert_code "tenant switch" "302" "$CODE"

echo "--- 13. Payroll module ---"
HTML=$(curl_get "$BASE/module/payroll")
assert_contains "payroll module" "$HTML" "page-payroll"

echo "--- 14. Insurance module ---"
HTML=$(curl_get "$BASE/module/insurance")
assert_contains "insurance module" "$HTML" "page-insurance"

echo "--- 15. Tax module ---"
HTML=$(curl_get "$BASE/module/tax")
assert_contains "tax module" "$HTML" "page-tax"

echo "--- 16. Finance transactions form ---"
HTML=$(curl_get "$BASE/module/finance/transactions/new")
assert_contains "new transaction form" "$HTML" 'name="amount"'
assert_contains "jalali date input" "$HTML" 'jalali-date'

echo "--- 17. Finance sub-modules ---"
CODE=$(curl_code "$BASE/module/finance/accounts")
assert_code "finance accounts" "200" "$CODE"
CODE=$(curl_code "$BASE/module/finance/categories")
assert_code "finance categories" "200" "$CODE"
CODE=$(curl_code "$BASE/module/finance/budgets")
assert_code "finance budgets" "200" "$CODE"
CODE=$(curl_code "$BASE/module/finance/reminders")
assert_code "finance reminders" "200" "$CODE"
CODE=$(curl_code "$BASE/module/finance/reports")
assert_code "finance reports" "200" "$CODE"
HTML=$(curl_get "$BASE/module/finance")
assert_contains "ceo dashboard" "$HTML" "ceo-kpi-grid"

echo "--- 18. Settings users ---"
HTML=$(curl_get "$BASE/module/settings/users")
assert_contains "settings users" "$HTML" "page-settings"

echo "--- 19. Finance phase 2-5 modules ---"
for path in contacts invoices checks loans assets; do
  CODE=$(curl_code "$BASE/module/finance/$path")
  assert_code "finance $path" "200" "$CODE"
done

echo "--- 20. Search ---"
HTML=$(curl_get "$BASE/search?q=test")
assert_contains "search page" "$HTML" "page-search"

echo "--- 21. Platform users ---"
CODE=$(curl_code "$BASE/platform/users")
assert_code "platform users" "200" "$CODE"

echo "--- 22. Projects module (agency tenant) ---"
curl_get "$BASE/tenant/switch/3" > /dev/null
HTML=$(curl_get "$BASE/module/projects")
assert_contains "projects module" "$HTML" "page-projects"

echo "--- 23. Projects create form ---"
HTML=$(curl_get "$BASE/module/projects/new")
assert_contains "new project form" "$HTML" 'name="name"'

echo "--- 24. Project detail ---"
HTML=$(curl_get "$BASE/module/projects/1")
assert_contains "project detail" "$HTML" "page-projects"

echo "--- 25. Payroll create form ---"
curl_get "$BASE/tenant/switch/1" > /dev/null
HTML=$(curl_get "$BASE/module/payroll/employees/new")
assert_contains "new employee form" "$HTML" 'name="base_salary"'

echo "--- 26. Settings page ---"
HTML=$(curl_get "$BASE/module/settings")
assert_contains "settings page" "$HTML" "page-settings"

echo "--- 27. Logout ---"
curl_get "$BASE/logout" > /dev/null
CODE=$(curl_code "$BASE/dashboard")
assert_code "dashboard requires auth" "302" "$CODE"

echo "--- 28. Root redirect ---"
CODE=$(curl_code "$BASE/")
assert_code "home redirect" "302" "$CODE"

echo "--- 29. Reinstall: installer accessible ---"
HTML=$(curl_get "$BASE/install")
assert_contains "reinstall accessible" "$HTML" "requirements-list"

echo "--- 30. Reinstall: full install cycle ---"
rm -f /workspace/writable/installed.lock
HTML=$(curl_get "$BASE/install/database")
CSRF=$(get_csrf "$HTML")
curl_post "$BASE/install/database" "csrf_test_name=${CSRF}&hostname=localhost&port=3306&database=bizpanel_test&username=bizpanel&password=bizpanel" > /dev/null
HTML=$(curl_get "$BASE/install/setup")
CSRF=$(get_csrf "$HTML")
SITE_URL="$BASE/"
curl_post "$BASE/install/setup" "csrf_test_name=${CSRF}&baseURL=${SITE_URL}&admin_name=Reinstall+Admin&admin_email=reinstall@test.local&admin_password=password123&admin_password_confirm=password123&seed_demo=1" > /dev/null
HTML=$(curl_get "$BASE/install/process")
CSRF=$(get_csrf "$HTML")
curl_post "$BASE/install/execute" "csrf_test_name=${CSRF}" > /dev/null
HTML=$(curl_get "$BASE/login")
assert_contains "reinstall login page" "$HTML" 'name="email"'
CSRF=$(get_csrf "$HTML")
curl_post "$BASE/login" "csrf_test_name=${CSRF}&email=reinstall@test.local&password=password123" > /dev/null
HTML=$(curl_get "$BASE/dashboard")
assert_contains "reinstall dashboard" "$HTML" "kpi-grid"

echo
echo "=== Results: $PASS passed, $FAIL failed ==="
[[ "$FAIL" -eq 0 ]]
