# ExportOS — راهنمای راه‌اندازی (فارسی)

پلتفرم SaaS چند-سازمانی برای صادرکنندگان — [مخزن GitHub](https://github.com/foroshgahsaz/leader)

---

## پیش‌نیازها

- PHP 8.2 یا بالاتر
- Composer
- Node.js 18+ و npm
- XAMPP (اختیاری — برای Apache/MySQL)
- کلید API از OpenAI (برای دستیار فروش AI)

---

## نصب گام‌به‌گام

### ۱. دریافت کد

```powershell
git clone https://github.com/foroshgahsaz/leader.git
cd leader
```

### ۲. نصب وابستگی‌های PHP

```powershell
composer install
```

### ۳. تنظیم فایل محیط

```powershell
copy .env.example .env
php artisan key:generate
```

فایل `.env` را باز کنید و این موارد را تنظیم کنید:

```env
APP_NAME=ExportOS
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

OPENAI_API_KEY=sk-کلید-شما
OPENAI_MODEL=gpt-4o-mini
AI_ENABLED=true
QUEUE_CONNECTION=database
```

**برای MySQL در XAMPP:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exportos
DB_USERNAME=root
DB_PASSWORD=
```

قبل از migrate، دیتابیس `exportos` را در phpMyAdmin بسازید.

### ۴. دیتابیس

**SQLite (پیش‌فرض):**

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate
php artisan db:seed
```

### ۵. فرانت‌اند

```powershell
npm install
npm run build
```

در حالت توسعه:

```powershell
npm run dev
```

### ۶. لینک storage

```powershell
php artisan storage:link
```

### ۷. اجرای برنامه

```powershell
php artisan serve
```

مرورگر: [http://127.0.0.1:8000](http://127.0.0.1:8000)

**یا با XAMPP:** Document Root را روی پوشه `public` پروژه بگذارید.

**همه سرویس‌ها با یک دستور (توسعه):**

```powershell
composer dev
```

### ۸. صف (Queue) — الزامی برای AI

```powershell
php artisan queue:work
```

بدون این دستور، تولید محتوای AI در حالت «در حال پردازش» می‌ماند.

---

## اولین استفاده

1. به `/register` بروید و حساب بسازید (نام شرکت + کشور).
2. نقش شما **Admin** است؛ مراحل Pipeline و دسترسی‌ها خودکار ساخته می‌شوند.
3. از منو:
   - **Dashboard** — آمار و نمودار
   - **Discover** — جستجو و ذخیره لید
   - **CRM** — شرکت، معامله، تسک، Pipeline
   - **Settings** — تیم و تنظیمات

در صفحه جزئیات هر لید، تب **AI** را برای تولید ایمیل و متن فروش باز کنید.

---

## نقش‌های کاربری

| نقش | دسترسی |
|-----|--------|
| Admin | همه بخش‌ها |
| Manager | CRM کامل (بدون حذف شرکت) |
| Rep | Dashboard، Discover، AI |

بروزرسانی دسترسی‌ها:

```powershell
php artisan db:seed --class=RolePermissionSeeder
```

---

## تست

```powershell
php artisan test
```

---

## رفع مشکلات رایج

| مشکل | راه‌حل |
|------|--------|
| AI پردازش نمی‌شود | `php artisan queue:work` را اجرا کنید |
| خطای 403 | `RolePermissionSeeder` را دوباره seed کنید |
| استایل خراب | `npm run build` |
| فایل آپلود نمی‌شود | `php artisan storage:link` |

---

## ماژول‌های پروژه

- **Dashboard** — لید امروز، امتیاز AI، ایمیل، پاسخ، تسک، Pipeline، Performance
- **Discover** — جستجو، ذخیره، لیست، امتیاز، Export/Import
- **AI Assistant** — ایمیل، واتساپ (متن)، Follow-up، ترجمه، خلاصه شرکت
- **CRM** — شرکت، مخاطب، Deal، Pipeline، تسک، جلسه، فایل، گزارش
- **Settings** — تیم، نوتیفیکیشن، Activity Log

---

برای مستندات انگلیسی: [README.md](README.md)
