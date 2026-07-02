#!/bin/bash
# نصب دستی (اختیاری) — روش پیشنهادی: باز کردن /install در مرورگر
set -e

echo "=== BizPanel Manual Install ==="
echo "روش پیشنهادی: https://yourdomain.com/install"
echo ""

if [ ! -f .env ]; then
    echo "فایل .env یافت نشد. ابتدا env.cpanel.example را کپی کنید."
    exit 1
fi

php spark key:generate --force
php spark migrate --all
php spark db:seed PlatformSeeder

chmod -R 755 writable/
chmod -R 755 public/

echo ""
echo "=== نصب کامل شد ==="
echo "ورود با حسابی که در seeder تعریف شده: admin@demo.local / password"
