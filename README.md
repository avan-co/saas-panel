# پنل SaaS چندکسب‌وکاری — BizPanel

پلتفرم مدیریت چند کسب‌وکار با PHP (CodeIgniter 4) و MySQL — مناسب نصب روی **هاست اشتراکی لینوکس + cPanel**.

## ویژگی‌های فعلی (فاز ۰)

- **نصب وب‌محور** — فقط آدرس `/install` را باز کنید
- چند کسب‌وکار (Multi-tenant) با جداسازی داده
- فعال‌سازی ماژول per کسب‌وکار
- پنل مدیریت پلتفرم (Super Admin)
- تم روشن / تاریک / سیستم
- دو زبانه فارسی و انگلیسی
- RTL خودکار برای فارسی

## نصب روی cPanel — ۳ مرحله ساده

### ۱. آپلود فایل‌ها

روی کامپیوتر:
```bash
composer install --no-dev
```

کل پروژه (شامل `vendor/`) را ZIP کنید و در هاست Extract کنید.

### ۲. ساخت دیتابیس MySQL

در cPanel → **MySQL Databases**:
- یک دیتابیس بسازید
- یک کاربر MySQL بسازید و به دیتابیس وصل کنید

> فقط دیتابیس خالی بسازید — جداول خودکار نصب می‌شوند.

### ۳. تنظیم Document Root

در cPanel → **Domains** → Document Root را روی پوشه **`public`** بگذارید:

```
/home/username/saas-panel/public
```

### ۴. اجرای نصاب وب

در مرورگر باز کنید:

```
https://yourdomain.com/install
```

مراحل نصاب:
1. **بررسی پیش‌نیازها** — PHP، افزونه‌ها، مجوز writable
2. **اتصال دیتابیس** — hostname، نام DB، کاربر، رمز
3. **تنظیمات** — آدرس سایت، حساب مدیر، داده نمونه (اختیاری)
4. **نصب خودکار** — migration و راه‌اندازی

بعد از نصب به صفحه ورود هدایت می‌شوید.

> فایل `writable/installed.lock` بعد از نصب ساخته می‌شود و دسترسی به `/install` بسته می‌شود.

### مجوز پوشه‌ها

```bash
chmod -R 755 writable/
```

## نصب دستی (اختیاری — Terminal)

اگر به Terminal دسترسی دارید:

```bash
cp env.cpanel.example .env
# ویرایش .env
php spark migrate
php spark db:seed PlatformSeeder
```

یا: `./install.sh`

## ساختار پروژه

```
public/          ← Document Root
app/
├── Controllers/Install.php   ← نصاب وب
├── Libraries/Installer.php
├── Language/fa|en/
└── Views/install/
```

## افزودن متن (دو زبانه)

```php
// app/Language/fa/Finance.php
return ['invoice' => 'فاکتور'];
```

```php
<?= lang('Finance.invoice') ?>
```

## فازهای بعدی

| فاز | ماژول |
|-----|--------|
| ۱ | مدیریت مالی |
| ۱.۵ | حقوق، بیمه، مالیات |
| ۲ | مدیریت پروژه |

## امنیت

- بعد از نصب `/install` غیرفعال می‌شود
- `CI_ENVIRONMENT = production` در `.env`
- SSL را فعال کنید
