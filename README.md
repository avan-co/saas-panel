# پنل SaaS چندکسب‌وکاری — BizPanel

پلتفرم مدیریت چند کسب‌وکار با PHP (CodeIgniter 4) و MySQL — مناسب نصب روی **هاست اشتراکی لینوکس + cPanel**.

## ویژگی‌های فعلی (فاز ۰)

- چند کسب‌وکار (Multi-tenant) با جداسازی داده
- فعال‌سازی ماژول per کسب‌وکار (مالی، حقوق، بیمه، مالیات، پروژه)
- پنل مدیریت پلتفرم (Super Admin)
- تم روشن / تاریک / سیستم
- دو زبانه فارسی و انگلیسی (فایل‌های زبان CI4)
- RTL خودکار برای فارسی
- منوی باریک قابل گسترش

## پیش‌نیازها (cPanel)

- PHP 8.1 یا بالاتر
- MySQL 5.7+ / MariaDB
- افزونه‌های PHP: `mysqli`, `mbstring`, `intl`, `json`, `curl`
- `mod_rewrite` فعال

## نصب روی cPanel — گام‌به‌گام

### ۱. آپلود فایل‌ها

کل پروژه را در هاست آپلود کنید (مثلاً در `saas-panel/` داخل `home/username/`).

> **روش پیشنهادی:** روی کامپیوتر خودتان `composer install --no-dev` بزنید، سپس کل پوشه (شامل `vendor/`) را ZIP کنید و در File Manager آپلود و Extract کنید.
>
> اگر cPanel شما Terminal دارد، می‌توانید بعد از آپلود فقط سورس را بفرستید و `composer install --no-dev` را روی سرور اجرا کنید.

### ۲. ساخت دیتابیس MySQL

در cPanel → **MySQL Databases**:

1. یک دیتابیس بسازید (مثلاً `username_bizpanel`)
2. یک کاربر MySQL بسازید
3. کاربر را به دیتابیس اضافه کنید (All Privileges)

### ۳. تنظیم Document Root

در cPanel → **Domains** → دامنه یا ساب‌دامین → Document Root را روی پوشه **`public`** پروژه بگذارید:

```
/home/username/saas-panel/public
```

اگر نمی‌توانید Document Root را عوض کنید، محتوای `public/` را در `public_html` کپی کنید و فایل `index.php` را ویرایش کنید:

```php
$pathsPath = FCPATH . '../app/Config/Paths.php';
// مسیر را متناسب با ساختار هاست تنظیم کنید
```

### ۴. فایل `.env`

1. فایل `env.cpanel.example` را کپی کنید به `.env`
2. مقادیر را پر کنید:

```ini
app.baseURL = 'https://yourdomain.com/'
database.default.database = username_bizpanel
database.default.username = username_dbuser
database.default.password = your_password
```

### ۵. مجوز پوشه‌ها

در Terminal cPanel یا File Manager:

```bash
chmod -R 755 writable/
chmod -R 755 public/
```

### ۶. Migration و داده اولیه

در **Terminal** cPanel:

```bash
cd ~/saas-panel
php spark key:generate
php spark migrate
php spark db:seed PlatformSeeder
```

### ۷. ورود

| فیلد | مقدار |
|------|-------|
| ایمیل | `admin@demo.local` |
| رمز | `password` |

بعد از ورود، سوئیچر کسب‌وکار در هدر را امتحان کنید.

## ساختار پروژه

```
app/
├── Controllers/       # کنترلرها
├── Database/
│   ├── Migrations/    # جداول MySQL
│   └── Seeds/         # داده نمونه
├── Filters/           # احراز هویت، tenant، زبان
├── Language/
│   ├── fa/            # فارسی
│   └── en/            # انگلیسی
├── Libraries/         # TenantContext
├── Models/
└── Views/             # قالب UI

public/                # Document Root — همین را در cPanel تنظیم کنید
├── assets/css/app.css
├── assets/js/app.js
└── index.php

writable/              # لاگ، کش، سشن — باید قابل نوشتن باشد
```

## افزودن متن جدید (دو زبانه)

مثل CodeIgniter 4:

```php
// app/Language/fa/Finance.php
return ['invoice' => 'فاکتور'];

// app/Language/en/Finance.php
return ['invoice' => 'Invoice'];
```

در View:

```php
<?= lang('Finance.invoice') ?>
```

## ماژول‌های آینده

| فاز | ماژول |
|-----|--------|
| ۱ | مدیریت مالی کامل |
| ۱.۵ | حقوق، بیمه، مالیات |
| ۲ | مدیریت پروژه |

## توسعه محلی

```bash
cp env .env
# تنظیم MySQL محلی در .env
php spark serve
php spark migrate
php spark db:seed PlatformSeeder
```

## امنیت (Production)

- `CI_ENVIRONMENT = production` در `.env`
- رمز `admin@demo.local` را عوض کنید
- `app.forceGlobalSecureRequests = true` با SSL
- پوشه‌های `app/` و `writable/` خارج از public باشند

## لایسنس

MIT — CodeIgniter 4 Framework
