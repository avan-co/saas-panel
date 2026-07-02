<?php

return [
    'title'              => 'نصب پنل کسب‌وکار',
    'subtitle'           => 'راه‌اندازی سریع روی هاست cPanel',
    'next'               => 'مرحله بعد',
    'back'               => 'مرحله قبل',
    'install_now'        => 'شروع نصب',
    'go_to_login'        => 'ورود به پنل',

    'step_requirements'  => 'بررسی پیش‌نیازها',
    'step_database'      => 'اتصال دیتابیس',
    'step_setup'         => 'تنظیمات و مدیر',
    'step_process'       => 'در حال نصب...',
    'step_complete'      => 'نصب کامل شد',

    'requirements_help'  => 'قبل از ادامه، موارد زیر باید آماده باشند.',
    'database_help'      => 'دیتابیس MySQL را از cPanel بسازید و اطلاعات اتصال را وارد کنید.',
    'setup_help'         => 'آدرس سایت و حساب مدیر اصلی پلتفرم را مشخص کنید.',
    'process_help'       => 'لطفاً صبر کنید، نصب خودکار در حال انجام است...',
    'complete_help'      => 'پلتفرم با موفقیت نصب شد. می‌توانید وارد پنل شوید.',
    'reinstall_warning'  => 'نصب مجدد: با تکمیل نصب، تمام جداول و داده‌های قبلی دیتابیس پاک شده و از نو ساخته می‌شوند.',
    'reset_failed'       => 'پاک‌سازی دیتابیس ناموفق بود:',

    'hostname'           => 'هاست دیتابیس',
    'database'           => 'نام دیتابیس',
    'username'           => 'نام کاربری',
    'password'           => 'رمز عبور',
    'port'               => 'پورت',
    'base_url'           => 'آدرس سایت',
    'admin_name'         => 'نام مدیر',
    'admin_email'        => 'ایمیل مدیر',
    'admin_password'     => 'رمز عبور مدیر',
    'admin_password_confirm' => 'تکرار رمز عبور',
    'seed_demo'          => 'ایجاد داده‌های نمونه (۳ کسب‌وکار آزمایشی)',

    'passed'             => 'تأیید',
    'failed'             => 'ناموفق',

    'db_connection_failed' => 'اتصال به دیتابیس برقرار نشد:',
    'env_write_failed'     => 'نوشتن فایل .env ناموفق بود. مجوز پوشه را بررسی کنید.',
    'migration_failed'     => 'اجرای migration ناموفق بود:',
    'seed_failed'          => 'ایجاد داده‌های اولیه ناموفق بود:',

    'req_php'              => 'PHP >= 8.1 (فعلی: {0})',
    'req_vendor'           => 'پکیج‌های PHP (vendor)',
    'req_mysqli'           => 'افزونه mysqli',
    'req_mbstring'         => 'افزونه mbstring',
    'req_intl'             => 'افزونه intl',
    'req_json'             => 'افزونه json',
    'req_curl'             => 'افزونه curl',
    'req_writable'         => 'پوشه writable/ قابل نوشتن',
    'req_env'              => 'فایل .env قابل نوشتن',
];
