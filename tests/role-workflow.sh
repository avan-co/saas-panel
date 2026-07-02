#!/usr/bin/env bash
# Role-based workflow smoke test — acts as each demo user
set -euo pipefail

BASE="${BASE_URL:-http://127.0.0.1:8080}"
COOKIE="/tmp/bizpanel_role_test.txt"
PASS=0
FAIL=0
BUGS=()

rm -f "$COOKIE"

curl_get() {
  curl -s -c "$COOKIE" -b "$COOKIE" "$BASE$1"
}

curl_code() {
  curl -s -o /dev/null -w "%{http_code}" -c "$COOKIE" -b "$COOKIE" "$BASE$1"
}

login() {
  local email="$1" pass="$2"
  rm -f "$COOKIE"
  local html csrf
  html=$(curl_get "/login")
  csrf=$(echo "$html" | sed -n 's/.*name="csrf_test_name" value="\([^"]*\)".*/\1/p' | head -1)
  curl -s -c "$COOKIE" -b "$COOKIE" -X POST "$BASE/login" \
    -d "csrf_test_name=${csrf}&email=${email}&password=${pass}" > /dev/null
}

assert_ok() {
  local name="$1" code="$2" expect="${3:-200}"
  if [[ "$code" == "$expect" ]]; then
    echo "  OK: $name ($code)"
    PASS=$((PASS + 1))
  else
    echo "  FAIL: $name (expected $expect, got $code)"
    FAIL=$((FAIL + 1))
    BUGS+=("$name: HTTP $code (expected $expect)")
  fi
}

assert_has() {
  local name="$1" html="$2" needle="$3"
  if echo "$html" | grep -qF "$needle"; then
    echo "  OK: $name"
    PASS=$((PASS + 1))
  else
    echo "  FAIL: $name (missing: $needle)"
    FAIL=$((FAIL + 1))
    BUGS+=("$name: missing '$needle'")
  fi
}

assert_lacks() {
  local name="$1" html="$2" needle="$3"
  if echo "$html" | grep -qF "$needle"; then
    echo "  FAIL: $name (should not contain: $needle)"
    FAIL=$((FAIL + 1))
    BUGS+=("$name: should not show '$needle'")
  else
    echo "  OK: $name"
    PASS=$((PASS + 1))
  fi
}

echo "=== 1. SUPER ADMIN (admin@demo.local) ==="
login "admin@demo.local" "password"
assert_ok "dashboard" "$(curl_code "/dashboard")"
assert_ok "platform tenants" "$(curl_code "/platform/tenants")"
assert_ok "platform tenant create" "$(curl_code "/platform/tenants/new")"
assert_ok "platform users" "$(curl_code "/platform/users")"
assert_ok "platform system" "$(curl_code "/platform/system")"
HTML=$(curl_get "/platform/tenants")
assert_has "tenants list" "$HTML" "agency-demo"
assert_has "subscription column" "$HTML" "اشتراک"

echo "=== 2. TENANT ADMIN (admin@agency.local) ==="
login "admin@agency.local" "password"
curl_get "/tenant/switch/3" > /dev/null
assert_ok "agency dashboard" "$(curl_code "/dashboard")"
assert_ok "settings" "$(curl_code "/module/settings")"
assert_ok "settings users" "$(curl_code "/module/settings/users")"
assert_ok "settings teams" "$(curl_code "/module/settings/teams")"
assert_ok "persons" "$(curl_code "/module/persons")"
assert_ok "projects" "$(curl_code "/module/projects")"
assert_ok "new project" "$(curl_code "/module/projects/new")"
assert_ok "finance" "$(curl_code "/module/finance")"
HTML=$(curl_get "/module/persons")
assert_has "persons customers" "$HTML" "شرکت آلفا"

echo "=== 3. MANAGER (manager@agency.local) ==="
login "manager@agency.local" "password"
curl_get "/tenant/switch/3" > /dev/null
assert_ok "manager projects" "$(curl_code "/module/projects")"
assert_ok "manager new project" "$(curl_code "/module/projects/new")"
assert_ok "manager project detail" "$(curl_code "/module/projects/1")"
assert_ok "manager tasks" "$(curl_code "/module/projects/1/tasks")"
assert_ok "manager settings denied" "$(curl_code "/module/settings/users")" "302"
HTML=$(curl_get "/module/projects")
assert_has "manager sees all projects" "$HTML" "PRJ-001"
assert_has "manager sees project 2" "$HTML" "PRJ-002"

echo "=== 4. EMPLOYEE dev1 (dev1@agency.local) ==="
login "dev1@agency.local" "password"
curl_get "/tenant/switch/3" > /dev/null
HTML=$(curl_get "/module/projects")
assert_has "dev1 sees team project" "$HTML" "PRJ-001"
assert_has "dev1 sees member project" "$HTML" "PRJ-002"
assert_lacks "dev1 hidden project 3" "$HTML" "PRJ-003"
assert_ok "dev1 project access" "$(curl_code "/module/projects/1")"
assert_ok "dev1 tasks" "$(curl_code "/module/projects/1/tasks")"
assert_ok "dev1 cannot create project" "$(curl_code "/module/projects/new")" "302"

echo "=== 5. EMPLOYEE dev2 (dev2@agency.local) ==="
login "dev2@agency.local" "password"
curl_get "/tenant/switch/3" > /dev/null
HTML=$(curl_get "/module/projects")
assert_has "dev2 team project only" "$HTML" "PRJ-001"
assert_lacks "dev2 no direct project 2" "$HTML" "PRJ-002"

echo "=== 6. VIEWER (viewer@agency.local) ==="
login "viewer@agency.local" "password"
curl_get "/tenant/switch/3" > /dev/null
HTML=$(curl_get "/module/projects")
assert_lacks "viewer no projects" "$HTML" "PRJ-001"
assert_ok "viewer finance view" "$(curl_code "/module/finance")"
assert_ok "viewer cannot new project" "$(curl_code "/module/projects/new")" "302"

echo "=== 7. PLATFORM ACCESS DENIED for tenant users ==="
login "admin@agency.local" "password"
assert_ok "tenant admin no platform" "$(curl_code "/platform/tenants")" "302"

echo
echo "=== Results: $PASS passed, $FAIL failed ==="
if [[ ${#BUGS[@]} -gt 0 ]]; then
  echo "Bugs:"
  for b in "${BUGS[@]}"; do echo "  - $b"; done
fi
[[ "$FAIL" -eq 0 ]]
