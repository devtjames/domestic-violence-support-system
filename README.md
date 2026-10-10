# Domestic Support Platform

Development of a Web-Based Domestic Violence Awareness and Support Platform.

This Laravel application is an HND final year academic project. It provides calm, privacy-aware domestic violence awareness content and support request workflows for visitors, registered users, support officers, and administrators.

The platform is for education, case intake, internal support coordination, and project demonstration. It is not an emergency service, law enforcement service, medical service, legal service, counselling service, or crisis-response dispatch system.

## Project Objectives

- Provide accessible domestic violence awareness information.
- Allow visitors to learn about abuse, warning signs, safety information, FAQs, and support resources without creating an account.
- Allow registered users to submit support requests and communicate privately with authorized support officers.
- Allow anonymous visitors to submit support requests and track limited status information with a secure access code.
- Give support officers tools to review assigned/open cases, reply to registered users, add private internal notes, update statuses, and keep case history.
- Give administrators tools to manage users, officers, cases, awareness content, resources, FAQs, contact enquiries, reports, audit logs, and system settings.
- Protect sensitive case data through authentication, role authorization, ownership checks, neutral wording, secure attachment access, and privacy-aware dashboards.
- Demonstrate a maintainable Laravel MVC application with migrations, seeders, Blade templates, Tailwind CSS, feature tests, and reusable components.

## Technology Stack

- Laravel 12
- PHP 8.2+
- MySQL for local/application data
- SQLite in memory for automated tests
- Blade templates
- Tailwind CSS
- Alpine.js
- Chart.js
- Laravel authentication
- Laravel validation, middleware, notifications, migrations, seeders, Eloquent ORM, and feature tests

This project is intentionally not an SPA. It does not use React, Vue, Next.js, or another frontend framework.

## Main User Roles

| Role | Purpose | Main Access |
| --- | --- | --- |
| Visitor | Public awareness and anonymous help access | Public website, anonymous support form, anonymous tracking |
| Registered User | Private support requests and case messaging | User dashboard, own cases, own messages, resources, awareness content |
| Support Officer | Authorized case support work | Officer dashboard, assigned cases, open unassigned cases where allowed, case replies, internal notes |
| Administrator | Platform administration | Admin dashboard, all management modules, reports, audit logs, system settings |

Accounts also have an account status. Disabled accounts cannot log in and are logged out when trying to access protected routes.

## Main Features

- Public website with Home, About, Types of Abuse, Warning Signs, Safety, Awareness, Resources, FAQs, Get Support, Contact, Login, and Register pages.
- User authentication, password reset, password update, profile management, account deletion, role-based dashboards, and backend role middleware.
- Awareness categories and articles with slugs, statuses, publishing, archiving, public visibility rules, search, and filters.
- Support resources with organization details, resource type, location, contact fields, availability, active/inactive status, search, and filters.
- Abuse type management with active/inactive status, ordering, search, filters, and historical case protection.
- Registered support case submission with unique case references, abuse type, urgency, status, contact preferences, and safe contact instructions.
- Anonymous support request submission with hashed access codes, secure tracking, and rate-limited lookup.
- Admin and support officer case management with assignment, reassignment, urgency changes, status changes, referrals, resolution, closure, reopening, and activity history.
- Case-based private messaging between registered users and authorized support officers, with read status, timestamps, and unread indicators.
- Private internal case notes for support officers and admins.
- Complete case activity history for major workflow events.
- Quick Exit button with configurable neutral destination and privacy reminders.
- Admin, support officer, and registered user dashboards with privacy-aware summaries.
- FAQ category and FAQ management with ordering and active/inactive status.
- General contact form and admin contact enquiry management, kept separate from support cases.
- Admin user and support officer management with activation/deactivation and case counts.
- Neutral in-system notifications for submissions, assignments, replies, status changes, and high-priority cases.
- Admin reports and statistics with filters, Chart.js summaries, CSV export, and print-friendly views.
- Audit logs for administrative and support actions with actor, module, action, request context, timestamp, and immutable normal UI.
- Admin system settings for platform name, contact details, Quick Exit URL, case reference prefix, critical case guidance, homepage content, and footer content.
- Secure case attachments with approved file types and authorized Laravel download routes.
- Search, filters, pagination, sorting, empty states, responsive tables, shared badges, alerts, modals, and reusable UI components.
- Custom 403, 404, 419, and 500 error pages with neutral wording.
- Development seed data with fake accounts, articles, FAQs, resources, cases, messages, notes, and activity history.
- Feature tests for the main workflows and security/privacy boundaries.

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- Node.js and npm
- PHP extensions normally required by Laravel, including `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, and `xml`
- A Laravel-compatible local environment such as Laragon, XAMPP, Laravel Herd, or the Laravel development server

## Installation

Install PHP and Node dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

## Environment Setup

Update `.env` for your local database and application URL:

```env
APP_NAME="Domestic Support"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domestic_support
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_ENCRYPT=true
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MAIL_MAILER=log

QUICK_EXIT_URL=https://www.google.com
```

Create the database named in `DB_DATABASE` before running migrations.

For production, set `APP_DEBUG=false`, use a strong `APP_KEY`, configure HTTPS, set `SESSION_SECURE_COOKIE=true`, configure real mail delivery if needed, and do not run development seeders against real data.

## Database Setup

Run migrations:

```bash
php artisan migrate
```

Run migrations with demo data:

```bash
php artisan migrate --seed
```

Reset and reseed a local development database:

```bash
php artisan migrate:fresh --seed
```

Create the storage symlink for publicly served article images:

```bash
php artisan storage:link
```

Case attachments are stored on the local disk and downloaded through authorized Laravel routes. They should not be served by direct public file paths.

## Seeders And Demo Data

The main seeder is guarded to run only in `local` and `testing` environments. It creates completely fake development data:

- Demo admin, support officer, and registered user accounts
- Abuse types
- Awareness categories and articles
- FAQ categories and FAQs
- Support resources
- Registered and anonymous support cases
- Case messages
- Internal notes
- Case activity history
- System settings

All seeded people, organizations, phone numbers, messages, notes, cases, and contact details are fictional demonstration data.

## Demo Accounts

These accounts are for local development and demonstration only. All use the password `password`.

| Role | Email |
| --- | --- |
| Admin | `admin@example.com` |
| Support Officer - General Support | `officer@example.com` |
| Support Officer - Counselling | `counselling.officer@example.com` |
| Support Officer - Legal Support | `legal.officer@example.com` |
| Registered User | `user@example.com` |
| Registered User | `amina.bello@example.test` |
| Registered User | `grace.mensah@example.test` |
| Registered User | `nadia.cole@example.test` |
| Registered User | `maya.rivers@example.test` |
| Registered User | `sam.taylor@example.test` |
| Registered User | `leah.brooks@example.test` |
| Registered User | `jonah.reed@example.test` |
| Registered User | `priya.shah@example.test` |
| Registered User | `noah.clarke@example.test` |
| Disabled Registered User | `disabled.user@example.test` |

Anonymous demo cases are seeded with hashed access codes. In a fresh local seed, open the admin case queue to see the generated case references. Demo access codes include `DEMOA123`, `DEMOA456`, and `DEMOA789`.

Do not seed predictable demo accounts automatically in production.

## Running The Project

Start the Laravel development server:

```bash
php artisan serve
```

Compile frontend assets for development:

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

Optional combined development command from `composer.json`:

```bash
composer run dev
```

Common entry points:

- Public website: `/`
- Login: `/login`
- Register: `/register`
- Registered user dashboard: `/dashboard`
- Support officer dashboard: `/officer/dashboard`
- Admin dashboard: `/admin/dashboard`
- Anonymous support: `/anonymous-support`
- Anonymous tracking: `/anonymous-support/track`

For a role-by-role operating guide, see `PROJECT_WORKFLOW_MANUAL.md`.

## Testing

Run the full test suite:

```bash
php artisan test
```

The test environment uses SQLite in memory through `phpunit.xml`, so tests do not touch the local MySQL database.

Current feature coverage includes:

- Registration, login, logout, password reset, password update, and profile management
- Role protection and account status enforcement
- Public website rendering and article publishing visibility
- Registered support case submission and ownership protection
- Anonymous support request submission, hashed access code storage, limited tracking, and rate limiting
- Admin and officer case management authorization
- Private case messaging and read status
- Internal note privacy
- Case activity history
- Quick Exit configuration and privacy wording
- Dashboard privacy
- Notifications
- Reports and CSV export
- Audit logging
- System settings
- Secure case attachments
- Search and filters
- Seeder repeatability

## Database Overview

Important tables include:

- `users`
- `awareness_categories`
- `awareness_articles`
- `abuse_types`
- `support_cases`
- `anonymous_case_accesses`
- `case_messages`
- `case_notes`
- `case_activities`
- `case_attachments`
- `support_resources`
- `faq_categories`
- `faqs`
- `contact_messages`
- `notifications`
- `audit_logs`
- `system_settings`

Key relationships:

- A user can have many support cases, sent messages, received messages, notifications, audit logs, and case activity records.
- A support case belongs to an abuse type, optionally belongs to a registered user, optionally belongs to an assigned support officer, and has messages, notes, attachments, activities, and optional anonymous access.
- An awareness article belongs to an awareness category and an author.
- FAQs belong to FAQ categories.
- Audit logs can link to an actor and an auditable record.

## Case Workflow

Support cases use human-readable references such as `DV-2026-000001`. The prefix is configurable in system settings.

Supported urgency levels:

- `low`
- `medium`
- `high`
- `critical`

Supported statuses:

- `submitted`
- `under_review`
- `assigned`
- `in_progress`
- `awaiting_user`
- `referred`
- `resolved`
- `closed`

Major workflow actions are recorded in case activity history, including submission, assignment, reassignment, status changes, urgency changes, replies, notes, referrals, resolution, closure, and reopening.

## Security Notes

- Passwords are hashed with Laravel hashing.
- CSRF protection is enabled on form routes.
- Authentication middleware protects private areas.
- Role middleware protects admin and support officer routes.
- Disabled accounts are blocked from login and removed from active protected sessions.
- Users can only access their own cases and case messages.
- Support officers can only access assigned cases or open unassigned cases where the workflow allows it.
- Anonymous case access codes are stored as hashes.
- Anonymous lookup is rate-limited.
- File uploads are validated by type, size, and count.
- Attachments are renamed safely and served only through authorized Laravel routes.
- Dashboards, reports, notifications, audit logs, and activity records avoid exposing sensitive private descriptions where possible.
- Audit log records are not editable through the normal interface.
- Mass assignment is restricted on sensitive model fields.
- Raw SQL is avoided in favor of Laravel query builder and Eloquent.

## Privacy Notes

- The platform collects only the information needed for support workflows.
- Anonymous support requests are available for people who do not want an account.
- Internal case notes are never visible to registered users or anonymous visitors.
- Anonymous tracking pages show only limited case information intended for the anonymous requester.
- Notification text is neutral and avoids case details.
- Dashboard widgets use counts and summaries rather than full case descriptions.
- Public pages do not display support case data.
- Page titles avoid sensitive case descriptions.
- Quick Exit immediately redirects to a configurable neutral destination.
- Quick Exit does not erase browser history, network logs, saved passwords, downloads, or device activity.
- Emergency contacts and support resources should be configured and verified by administrators rather than hard-coded.

## Project Modules

The application has been built module by module according to `master_prompt.txt`.

| Module | Status | Summary |
| --- | --- | --- |
| 1 | Complete | Project foundation, authentication, roles, account status, dashboards |
| 2 | Complete | Public website and awareness pages |
| 3 | Complete | Awareness categories and articles |
| 4 | Complete | Support resources and organizations |
| 5 | Complete | Registered user support requests |
| 6 | Complete | Anonymous support requests and secure tracking |
| 7 | Complete | Admin and support officer case management |
| 8 | Complete | Private case messaging |
| 9 | Complete | Internal case notes |
| 10 | Complete | Case activity history |
| 11 | Complete | Quick Exit and privacy protections |
| 12 | Complete | Admin dashboard |
| 13 | Complete | Support officer dashboard |
| 14 | Complete | Registered user dashboard |
| 15 | Complete | FAQ management |
| 16 | Complete | Contact and general enquiries |
| 17 | Complete | User management |
| 18 | Complete | Support officer management |
| 19 | Complete | In-system notifications |
| 20 | Complete | Reports and statistics |
| 21 | Complete | Audit logging |
| 22 | Complete | System settings |
| 23 | Complete | File attachments |
| 24 | Complete | Search and filters |
| 25 | Complete | Seeders and demo data |
| 26 | Complete | Security review |
| 27 | Complete | Feature testing |
| 28 | Complete | UI and responsive review |
| 29 | Complete | README and project documentation |
| 30 | Complete | Final system review and missing requirement fixes |

Do not continue to another module unless it is explicitly requested.

## Screenshots

Screenshots can be added here for the final academic submission:

- Public homepage
- Awareness article list and detail
- Anonymous support submission
- Anonymous tracking
- Registered user dashboard
- Support officer dashboard
- Admin dashboard
- Case management detail
- Reports
- System settings

## Academic Scope

The system is limited to awareness content, support request submission, anonymous reporting, case management, private support communication, support resources, administrative reports, user management, support officer management, audit logging, and configurable settings.

The system does not:

- Automatically contact police or emergency services
- Diagnose medical conditions
- Provide automated legal advice
- Replace professional counselling
- Track victims using GPS
- Perform surveillance
- Automatically determine whether an allegation is true
- Provide public discussions of individual cases

## Maintenance Notes

- Follow Laravel conventions for controllers, models, requests, middleware, services, named routes, and Eloquent relationships.
- Keep authorization on the backend, not only in Blade views.
- Prefer new migrations for structural changes instead of editing old migrations that may already have run.
- Keep seeded data fictional.
- Keep support resources and emergency-related information configurable and verified.
- Run `php artisan test` after functional changes.
- Run `npm run build` after frontend changes.
