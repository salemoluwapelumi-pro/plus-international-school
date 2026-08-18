# Plus International School Platform

School website and management portal for Plus International School, Tunga, Minna, Niger State, Nigeria.

Built with PHP 8.1 (server-rendered), MySQL/MariaDB via PDO prepared statements, and vanilla JavaScript. No build step and no package manager are required.

## Features

- Public website: home, about, contact, nursery/primary/secondary programmes, facilities, academic calendar, news, gallery, fee structure, admissions process and requirements, online application, public result checking and fee-payment information.
- Shared portal login for every role (email, admission number, or staff number) with password reset by email and student self-registration that requires an existing admission number.
- Role dashboards: superadmin, subadmin, cashier, teacher, student, parent.
- Results: teacher score entry (CA1 10, CA2 10, assignment 10, exam 70) with live browser totals/grades/remarks, server-side recomputation, subject positions, class average, overall position, publication control, and a printable result sheet.
- Payments: Paystack, Remita, bank transfer and cash with optional proof upload, cashier verification/approval queue, receipt numbering, payment history and class-by-class reports; administrators oversee without approving.
- Permanent student records with document uploads, timetable management (Mon–Fri, 8 periods, class and teacher views), announcements, notifications, database-backed permissions, and an audit log.
- Separate teacher–student chat application with its own registration/login, presence, read state and polling.

## Requirements

- PHP 8.1+ with `pdo_mysql`, `mbstring`, `curl`
- MySQL 8 or MariaDB 10.5+

## Setup

```bash
sudo apt-get install -y php-cli php-mysql php-mbstring php-curl mariadb-server

mysql -uroot -e "CREATE DATABASE plus_international_school CHARACTER SET utf8mb4;"
mysql -uroot -e "CREATE USER 'plus'@'localhost' IDENTIFIED BY 'your-password';"
mysql -uroot -e "GRANT ALL ON plus_international_school.* TO 'plus'@'localhost';"

mysql -uplus -p plus_international_school < database/schema.sql
mysql -uplus -p plus_international_school < database/seed.sql   # demo data, optional

cp .env.example .env   # then edit the values
php -S 127.0.0.1:8000 -t .
```

Open http://127.0.0.1:8000. For production, point an Apache/Nginx virtual host at the project root; set `APP_URL` in `.env` when the site is served from a sub-directory.

## Configuration

All settings live in `.env` (see `.env.example`): database credentials, `APP_URL`, Paystack keys, Remita merchant credentials, and mail options. Nothing secret is committed — `config.php` only holds non-sensitive defaults.

- Paystack: copy the public and secret keys from the Paystack dashboard into `PAYSTACK_PUBLIC_KEY` / `PAYSTACK_SECRET_KEY`.
- Remita: set `REMITA_MERCHANT_ID`, `REMITA_SERVICE_TYPE_ID` and `REMITA_API_KEY`; keep `REMITA_DEMO_MODE=true` until the live credentials are issued.
- Mail: set `MAIL_FROM` and `MAIL_ENABLED=true` once an SMTP-capable host is available (password-reset and notification emails use PHP `mail()`).

## Demo accounts

`database/seed.sql` creates sample accounts, all with the password `Password123`. They are for local development only — change or delete them before going live.

| Role | Sign in with |
| --- | --- |
| Superadmin | `admin@plusinternationalschool.ng` |
| Subadmin | `subadmin@plusinternationalschool.ng` |
| Cashier | `cashier@plusinternationalschool.ng` |
| Teacher | `teacher@plusinternationalschool.ng` |
| Student | `PIS/2025/001` |
| Parent | `parent@plusinternationalschool.ng` |

## Layout

```
frontend/     public website pages and shared partials
portal/       login, registration, password reset, profile, public result check
admin/        superadmin and subadmin pages (users, academics, payments, system)
cashier/      payment queue, receipts and reports
teacher/      results, attendance, assignments, timetable
student/      results, timetable, assignments, fee payment and history
parent/       children's results and payments
chat/         separate chat registration, login and application
backend/api/  form and JSON endpoints
backend/includes/  Database, Auth, Permissions, ResultCalculator, PaymentProcessor,
              TimetableManager, ChatSystem, NotificationSystem, AuditLogger, layouts
assets/       CSS, JavaScript and uploaded files
database/     schema.sql and seed.sql
```

## Checks

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```
