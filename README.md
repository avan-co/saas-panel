# پنل SaaS چندکسب‌وکاری — BizPanel

پلتفرم مدیریت چند کسب‌وکار با PHP (CodeIgniter 4) و MySQL — مناسب **cPanel + Git Clone**.

## نصب — فقط ۳ قدم

```
Git Clone  →  Document Root = public/  →  باز کردن /install
```

همه‌چیز (دیتابیس، تنظیمات، حساب مدیر) از طریق **نصاب وب** انجام می‌شود.  
نیازی به Terminal، composer یا اسکریپت دستی نیست.

---

### ۱. Git Clone در cPanel

cPanel → **Git Version Control** → Clone ریپو

مسیر معمول:
```
/home/username/saas-panel/
```

### ۲. Document Root

cPanel → **Domains** → Document Root:

```
/home/username/saas-panel/public
```

### ۳. دیتابیس MySQL (خالی)

cPanel → **MySQL Databases**:
- یک دیتابیس بسازید
- کاربر MySQL بسازید و به دیتابیس وصل کنید

### ۴. نصاب وب

در مرورگر:

```
https://yourdomain.com/install
```

| مرحله | کار |
|--------|-----|
| ۱ | بررسی پیش‌نیازها (PHP، vendor، افزونه‌ها) |
| ۲ | اطلاعات MySQL — hostname معمولاً `localhost` |
| ۳ | آدرس سایت + حساب مدیر + داده نمونه (اختیاری) |
| ۴ | نصب خودکار — جداول، مدیر، آماده ورود |

بعد از نصب → `/login`

> `writable/installed.lock` دسترسی به `/install` را می‌بندد.

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
