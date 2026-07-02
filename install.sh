#!/bin/bash
# نصب سریع روی cPanel Terminal
set -e

echo "=== BizPanel Install ==="

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
echo "ورود: admin@demo.local / password"
echo "حتماً رمز را عوض کنید!"
