#!/bin/bash
# اجرا بعد از کلون Git در cPanel (یک‌بار قبل از /install)
set -e

cd "$(dirname "$0")"

echo "=== BizPanel — آماده‌سازی بعد از Git Clone ==="

# 1. Composer dependencies (vendor در Git نیست)
if command -v composer >/dev/null 2>&1; then
    composer install --no-dev --optimize-autoloader
else
    echo "⚠️  composer یافت نشد."
    echo "   از cPanel Terminal نصب کنید یا vendor/ را از لوکال آپلود کنید."
    exit 1
fi

# 2. مجوز پوشه‌های لازم
chmod -R 755 writable/ 2>/dev/null || true
chmod -R 755 public/ 2>/dev/null || true

# 3. حذف lock نصب قبلی (اگر وجود داشت — برای نصب تازه)
if [ -f writable/installed.lock ]; then
    echo "⚠️  installed.lock موجود است — نصاب باز نمی‌شود."
    echo "   برای نصب مجدد این فایل را حذف کنید."
fi

echo ""
echo "✅ آماده است!"
echo ""
echo "قدم بعدی:"
echo "  1. دیتابیس MySQL خالی در cPanel بسازید"
echo "  2. Document Root را روی پوشه public/ بگذارید"
echo "  3. در مرورگر باز کنید: https://yourdomain.com/install"
echo ""
