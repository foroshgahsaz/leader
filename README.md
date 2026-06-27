# ExportOS (Leader)

**ExportOS** is a multi-tenant SaaS platform for exporters built with Laravel 12, Livewire 3, and Tailwind CSS. It helps teams discover international buyers, score leads with AI, manage CRM pipelines, and automate sales outreach.

Repository: [github.com/foroshgahsaz/leader](https://github.com/foroshgahsaz/leader)

---

## Features

| Module | Description |
|--------|-------------|
| **Dashboard** | KPIs, charts, pipeline summary, performance metrics |
| **Discover (Lead Finder)** | Search global buyers, save leads, lists, scoring, CSV export/import |
| **AI Sales Assistant** | Email, WhatsApp text, follow-ups, translation, company summary, next best action, risk analysis (OpenAI) |
| **CRM** | Companies, contacts, deals, pipeline board, tasks, meetings, activities, files, notes, timeline, reports |
| **Settings** | Company profile, team invites, notifications, activity log, user preferences |
| **Auth & Teams** | Registration with organization, roles (Admin / Manager / Rep), Spatie permissions |

---

## Requirements

- PHP **8.2+**
- Composer **2.x**
- Node.js **18+** and npm
- SQLite (development) or MySQL/MariaDB (production)
- OpenAI API key (for AI features)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/foroshgahsaz/leader.git
cd leader
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set at minimum:

```env
APP_NAME=ExportOS
APP_URL=http://127.0.0.1:8000

# Database — SQLite (default for local dev)
DB_CONNECTION=sqlite

# OpenAI (required for AI Assistant)
OPENAI_API_KEY=sk-your-key-here
OPENAI_MODEL=gpt-4o-mini
AI_ENABLED=true

# Queue (AI jobs run asynchronously)
QUEUE_CONNECTION=database
```

**MySQL (optional — e.g. XAMPP):**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exportos
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in phpMyAdmin before running migrations.

### 4. Database setup

**SQLite:**

```bash
# Linux / macOS
touch database/database.sqlite

# Windows PowerShell
New-Item -ItemType File -Path database\database.sqlite -Force

php artisan migrate
php artisan db:seed
```

The seeder loads permissions, sample global buyers, and default pipeline stages.

### 5. Frontend assets

```bash
npm install
npm run build
```

For development with hot reload:

```bash
npm run dev
```

### 6. Storage link

```bash
php artisan storage:link
```

### 7. Run the application

**Option A — Laravel built-in server:**

```bash
php artisan serve
```

Open: [http://127.0.0.1:8000](http://127.0.0.1:8000)

**Option B — XAMPP Apache**

Point the document root to the `public/` folder, e.g. `C:\xampp\htdocs\leader\public`.

**Option C — All dev services at once**

```bash
composer dev
```

Runs: HTTP server, queue worker, log viewer (Pail), and Vite.

### 8. Queue worker (required for AI)

AI generation runs in the background. Keep a worker running:

```bash
php artisan queue:work
```

Or use `composer dev` which starts the queue automatically.

---

## First use

1. Go to `/register` and create an account (company name + country).
2. You become **Admin** of a new organization; pipeline stages and permissions are seeded automatically.
3. Use the navigation:
   - **Dashboard** — overview and metrics
   - **Discover** — search and save leads
   - **CRM** — manage companies, deals, tasks, pipeline
   - **Settings** — team, company, preferences

On a lead detail page, open the **AI** tab to generate emails and other sales content.

---

## Routes

| URL | Description |
|-----|-------------|
| `/` | Welcome page |
| `/register`, `/login` | Authentication |
| `/dashboard` | Main dashboard |
| `/discover` | Lead search |
| `/discover/saved` | Saved leads |
| `/discover/leads/{id}` | Lead detail + AI |
| `/crm` | CRM companies |
| `/crm/pipeline` | Deal pipeline board |
| `/crm/tasks` | Tasks |
| `/crm/reports` | Reports |
| `/settings/*` | Organization settings |

---

## Roles & permissions

| Role | Access |
|------|--------|
| **Admin** | Full access |
| **Manager** | Full CRM except deleting companies |
| **Rep** | Dashboard, Discover, AI — limited CRM |

Re-seed permissions after updates:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## Testing

```bash
php artisan test
```

Run module tests only:

```bash
php artisan test --filter=Dashboard
php artisan test --filter=Crm
php artisan test --filter=LeadFinder
php artisan test --filter=AiAssistant
```

---

## Tech stack

- Laravel 12
- Livewire 3 + Volt
- Tailwind CSS 3/4
- Laravel Breeze (auth)
- Spatie Laravel Permission (team-scoped by `organization_id`)
- OpenAI PHP client
- SQLite / MySQL

---

## Production checklist

- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Use MySQL or PostgreSQL
- [ ] Configure real mail driver (SMTP)
- [ ] Run `php artisan config:cache route:cache view:cache`
- [ ] Set up Supervisor or systemd for `queue:work`
- [ ] Use HTTPS and secure `OPENAI_API_KEY`
- [ ] Schedule `php artisan schedule:run` via cron

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| AI stays "processing" | Start `php artisan queue:work` |
| 403 on Dashboard/CRM | Run `php artisan db:seed --class=RolePermissionSeeder` |
| Vite assets missing | Run `npm run build` |
| Permission denied on storage | `php artisan storage:link` and check folder permissions |

---

## License

MIT
