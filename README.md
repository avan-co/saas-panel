# پنل SaaS چندکسب‌وکاری — BizPanel

پلتفرم مدیریت چند کسب‌وکار با PHP (CodeIgniter 4) و MySQL — مناسب **cPanel + Git Clone**.

## نصب — فقط ۳ قدم

```
Git Clone  →  Pull  →  باز کردن /install
```

همه‌چیز (دیتابیس، تنظیمات، حساب مدیر) از طریق **نصاب وب** انجام می‌شود.  
نیازی به Terminal، composer یا اسکریپت دستی نیست.

> **فایل `db.php` یا تنظیم دستی دیتابیس لازم نیست.**  
> در مرحله نصب، اطلاعات MySQL را وارد می‌کنید و برنامه خودش `.env` را می‌سازد.

---

### مثال: portal.avan-co.ir روی cPanel

| مورد | مقدار |
|------|--------|
| دامنه | `https://portal.avan-co.ir` |
| مسیر پروژه | `/home/h325207/public_html/portal` |
| Document Root (هاستینگ) | همان `/home/h325207/public_html/portal` |
| آدرس نصاب | `https://portal.avan-co.ir/install` |

ریپو شامل `index.php` و `.htaccess` در **ریشه پروژه** است تا وقتی Document Root روی `portal/` است (نه `portal/public`)، برنامه درست کار کند.

اگر cPanel اجازه می‌دهد Document Root را عوض کنید، بهترین حالت:

```
/home/h325207/public_html/portal/public
```

---

### ۱. Git Clone + Pull در cPanel

cPanel → **Git Version Control** → Clone ریپو:

```
https://github.com/avan-co/saas-panel.git
```

مسیر:

```
/home/h325207/public_html/portal
```

بعد از هر تغییر: **Pull** (دکمه Deploy لازم نیست).

---

### ۲. دیتابیس MySQL (خالی)

cPanel → **MySQL Databases**:

1. دیتابیس بسازید — مثلاً `h325207_portal` (پیشوند cPanel خودکار اضافه می‌شود)
2. کاربر MySQL بسازید — مثلاً `h325207_portal`
3. کاربر را به دیتابیس وصل کنید → **All Privileges**

در نصاب وب این مقادیر را وارد کنید:

| فیلد | مقدار معمول cPanel |
|------|---------------------|
| هاست | `localhost` |
| پورت | `3306` |
| نام دیتابیس | `h325207_portal` (نام کامل از cPanel) |
| نام کاربری | `h325207_portal` (نام کامل از cPanel) |
| رمز | همان رمزی که در cPanel ساختید |

charset پیش‌فرض: `utf8mb4` (نیازی به تنظیم دستی نیست).

---

### ۳. PHP

cPanel → **Select PHP Version**:

- PHP **8.2** یا **8.3**
- افزونه‌ها: `mysqli`, `mbstring`, `intl`, `json`, `curl`

---

### ۴. مجوزها

File Manager → پوشه `writable/` → Permissions → **755**

---

### ۵. نصاب وب

```
https://portal.avan-co.ir/install
```

| مرحله | کار |
|--------|-----|
| ۱ | بررسی پیش‌نیازها |
| ۲ | اطلاعات MySQL (`localhost` / `3306`) |
| ۳ | آدرس سایت: `https://portal.avan-co.ir/` + حساب مدیر |
| ۴ | نصب خودکار |

بعد از نصب → `https://portal.avan-co.ir/login`

> `writable/installed.lock` دسترسی به `/install` را می‌بندد.

---

### عیب‌یابی cPanel

| مشکل | راه‌حل |
|------|--------|
| **503** | PHP را 8.2+ کنید؛ Pull بزنید؛ Error Log را در cPanel ببینید |
| **404 روی /install** | Pull کنید؛ مطمئن شوید `index.php` و `.htaccess` در ریشه `portal/` هستند |
| **صفحه لیست پوشه‌ها** | `index.php` در ریشه نیست — Pull بزنید |
| **خطای اتصال DB** | نام کامل دیتابیس/کاربر با پیشوند `h325207_` را از cPanel کپی کنید |
| **لینک به `/public/dashboard`** | Pull آخرین نسخه — `baseURL` خودکار `/public` را حذف می‌کند؛ یا در `.env`: `app.baseURL = 'https://portal.avan-co.ir/'` |
| **لینک به localhost** | Pull آخرین نسخه — baseURL خودکار از دامنه تشخیص داده می‌شود |
| **localhost:8080** | فقط برای توسعه لوکال است — روی هاست از دامنه استفاده کنید |

---

## ساختار

```
saas-panel/
├── public/       ← Document Root
├── vendor/       ← داخل Git (بدون نیاز به composer روی سرور)
├── app/
└── writable/
```

## ویژگی‌ها

- چند کسب‌وکار (Multi-tenant)
- ماژول per کسب‌وکار (مالی، حقوق، بیمه، مالیات، پروژه)
- پنل مدیریت پلتفرم
- تم روشن / تاریک
- دو زبانه fa / en + RTL

## افزودن متن

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
- SSL را فعال کنید
- رمز مدیر را قوی انتخاب کنید
