# Domestic Support Platform Workflow Manual

This manual explains how to set up, run, and operate the Web-Based Domestic Violence Awareness and Support Platform.

The system is an academic domestic violence awareness and support platform. It helps visitors learn, submit support requests, and track cases. It does not replace emergency services, police, medical professionals, legal professionals, or professional counselling.

## 1. Local Setup With MySQL

### 1.1 Required Software

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- Node.js and npm
- A Laravel local environment such as Laragon, XAMPP, Herd, or `php artisan serve`

### 1.2 Create The MySQL Database

Open MySQL and create a database:

```sql
CREATE DATABASE domestic_support CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

If you use phpMyAdmin:

- Open phpMyAdmin.
- Click Databases.
- Create a database named `domestic_support`.
- Use collation `utf8mb4_unicode_ci` if available.

### 1.3 Configure `.env`

The project should use MySQL locally:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domestic_support
DB_USERNAME=root
DB_PASSWORD=
```

Change `DB_USERNAME` and `DB_PASSWORD` if your MySQL installation uses different credentials.

### 1.4 Install Dependencies

```bash
composer install
npm install
```

### 1.5 Generate App Key

```bash
php artisan key:generate
```

### 1.6 Clear Cached Config

Run this after changing `.env`:

```bash
php artisan config:clear
```

### 1.7 Run Migrations And Seeders

```bash
php artisan migrate --seed
```

For a fresh local reset:

```bash
php artisan migrate:fresh --seed
```

### 1.8 Link Storage

```bash
php artisan storage:link
```

### 1.9 Build Frontend Assets

```bash
npm run build
```

### 1.10 Run The Application

```bash
php artisan serve
```

Open the displayed URL, usually:

```text
http://127.0.0.1:8000
```

## 2. Demo Accounts

All seeded demo accounts use this password:

```text
password
```

| Role | Email | Dashboard |
| --- | --- | --- |
| Admin | `admin@example.com` | `/admin/dashboard` |
| Support Officer | `officer@example.com` | `/officer/dashboard` |
| Counselling Officer | `counselling.officer@example.com` | `/officer/dashboard` |
| Legal Support Officer | `legal.officer@example.com` | `/officer/dashboard` |
| Registered User | `user@example.com` | `/dashboard` |
| Disabled User | `disabled.user@example.test` | Cannot log in |

Other demo users are available in the README.

## 3. Main System Units

### 3.1 Public Website

Purpose:

- Educate visitors about domestic violence.
- Show awareness content.
- Show support resources and FAQs.
- Provide access to registered and anonymous support request workflows.

Main pages:

- Home
- About
- Types of Abuse
- Warning Signs
- Safety
- Awareness
- Awareness Article Detail
- Resources
- FAQs
- Get Support
- Contact
- Login
- Register

How to operate:

1. Open the homepage.
2. Use the public navigation to move between information pages.
3. Use Awareness to read published articles.
4. Use Resources to view active support organizations.
5. Use FAQs for common questions.
6. Use Get Support to choose registered or anonymous support.

Privacy note:

- Public pages must not display private support case details.
- Quick Exit is visible and redirects away from the site.
- Quick Exit does not erase browser history.

### 3.2 Authentication Unit

Purpose:

- Allow users to register, log in, log out, reset passwords, update passwords, and manage profile details.

Roles:

- `user`
- `support_officer`
- `admin`

How it works:

1. A visitor registers from `/register`.
2. A registered user logs in from `/login`.
3. The system redirects them by role:
   - User: `/dashboard`
   - Support officer: `/officer/dashboard`
   - Admin: `/admin/dashboard`
4. Disabled accounts are blocked.
5. Protected routes check authentication, active account status, and role.

### 3.3 System Settings Unit

Purpose:

- Keep configurable values out of code.

Admin can configure:

- Platform name
- Platform email
- Support phone
- Support email
- Address
- Contact page information
- Critical case guidance
- Quick Exit destination
- Case reference prefix
- Homepage text
- Footer information

How to operate:

1. Log in as admin.
2. Open Admin Dashboard.
3. Click Settings.
4. Edit required values.
5. Save settings.
6. Check the public pages and support forms to confirm settings are visible.

Important:

- Quick Exit destination must begin with `http://` or `https://`.
- Case reference prefix controls new case references, for example `DV-2026-000001`.
- Critical case guidance appears when someone selects Critical urgency.

### 3.4 Awareness Content Unit

Purpose:

- Manage educational articles shown to the public.

Includes:

- Awareness categories
- Awareness articles

Article statuses:

- Draft
- Published
- Archived

Admin workflow:

1. Log in as admin.
2. Go to Awareness Categories.
3. Create categories such as Physical Abuse, Digital Abuse, Safety Planning, or Recovery.
4. Go to Articles.
5. Create an article with title, category, short description, full content, optional image, and status.
6. Save as Draft while editing.
7. Publish when ready.
8. Archive old articles when they should no longer appear publicly.

Public workflow:

1. Visitor opens Awareness.
2. Visitor sees only published articles.
3. Visitor opens an article detail page.

Rules:

- Draft and archived articles are hidden publicly.
- Slugs are generated automatically if left blank.

### 3.5 Abuse Types Unit

Purpose:

- Manage the support case categories used by registered and anonymous request forms.

Admin workflow:

1. Log in as admin.
2. Go to Abuse Types.
3. Create or edit abuse types.
4. Set status to Active or Inactive.
5. Use search and filters to find categories.

Rules:

- Active abuse types appear on support request forms.
- Inactive abuse types are hidden from new requests.
- Abuse types used by existing cases cannot be deleted. Deactivate them instead.

### 3.6 Support Resources Unit

Purpose:

- Manage organizations and resources that may help users.

Resource fields:

- Organization name
- Resource type
- Description
- Phone
- Email
- Website
- Address
- Location
- Availability
- Status

Admin workflow:

1. Log in as admin.
2. Go to Resources.
3. Create or edit support resource records.
4. Verify contact information before saving phone numbers or emergency-related resources.
5. Set active resources to Active.
6. Set outdated resources to Inactive.

Public workflow:

1. Visitor opens Resources.
2. Visitor sees only active resources.
3. Visitor can search or filter resources.

Rules:

- Do not hard-code emergency contacts.
- Do not add unverified support phone numbers.

### 3.7 FAQ Unit

Purpose:

- Provide public answers to common questions.

Admin workflow:

1. Log in as admin.
2. Go to FAQs.
3. Manage FAQ categories.
4. Create FAQs under categories.
5. Activate or deactivate FAQs.
6. Reorder FAQs as needed.

Public workflow:

1. Visitor opens FAQs.
2. Visitor sees active FAQs in active categories only.

### 3.8 Contact Enquiries Unit

Purpose:

- Collect general platform enquiries.

Visitor workflow:

1. Open Contact.
2. Enter name, email, subject, and message.
3. Submit the form.

Admin workflow:

1. Log in as admin.
2. Go to Contact Messages.
3. View enquiries.
4. Update status.
5. Delete messages where appropriate.

Important:

- Contact messages are not support cases.
- Visitors should not submit sensitive case details through the general contact form.

## 4. Registered User Workflow

### 4.1 Register And Log In

1. Open `/register`.
2. Create an account.
3. Log in.
4. The system opens `/dashboard`.

### 4.2 User Dashboard

The user dashboard shows:

- Active cases
- Resolved cases
- Recent messages
- Recent case updates
- Awareness content
- Support resources

Privacy:

- Dashboard cards avoid full case descriptions, private message text, internal notes, and safe contact details.

### 4.3 Submit Registered Support Request

1. Log in as a registered user.
2. Click Request Support.
3. Choose Type of Concern.
4. Enter Subject.
5. Enter Description.
6. Select Urgency.
7. Choose Preferred Contact Method.
8. Add Safe Contact Time or Safe Contact Instructions if needed.
9. Optionally attach approved files.
10. Submit.

After submission:

- The system generates a case reference.
- Status starts as Submitted.
- Case activity records the submission.
- Admin receives a neutral notification.

### 4.4 View Own Cases

1. Click My Cases.
2. Use search, status filter, urgency filter, abuse type filter, date filters, and sorting.
3. Open a case by its case reference.

Rules:

- Users can only see their own cases.
- Users cannot access another user case.

### 4.5 Send Private Message

1. Open an assigned case.
2. Use the message form.
3. Send the message.

Rules:

- Messages must belong to a support case.
- A user can only message inside their own case.
- Anonymous cases do not use private account messaging.
- Closed cases do not accept new private messages.

### 4.6 Download Attachments

1. Open your case.
2. Use the attachment download link.

Rules:

- Users can download only attachments belonging to their own cases.
- File downloads go through Laravel authorization routes.

## 5. Anonymous Visitor Workflow

### 5.1 Submit Anonymous Request

1. Open Get Support.
2. Choose anonymous support.
3. Enter optional nickname if desired.
4. Choose Type of Concern.
5. Enter Subject and Description.
6. Select Urgency.
7. Choose preferred response method.
8. Provide optional email or phone only if safe.
9. Enter safe contact instructions if needed.
10. Optionally attach approved files.
11. Submit.

After submission:

- The system generates a case reference.
- The system generates a secure access code.
- The access code is shown once.
- The access code is stored only as a hash.

Important:

- The anonymous visitor must save both the case reference and access code.
- If the access code is lost, it cannot be shown again.

### 5.2 Track Anonymous Case

1. Open `/anonymous-support/track`.
2. Enter the case reference.
3. Enter the access code.
4. Submit.

The tracking result can show:

- Case reference
- Status
- Urgency
- Submission date
- Latest update
- Limited public-facing status information

The tracking result must not show:

- Internal notes
- Private staff records
- Other cases
- Full internal activity
- Private messages

Security:

- Lookup attempts are rate-limited.

## 6. Support Officer Workflow

### 6.1 Log In

1. Open `/login`.
2. Log in with a support officer account.
3. The system redirects to `/officer/dashboard`.

### 6.2 Officer Dashboard

The officer dashboard shows:

- Assigned cases
- Open assigned cases
- High-priority cases
- Unread messages
- Cases awaiting response
- Recent assignments
- Recently updated cases

Rules:

- Officers see only assigned cases and open unassigned cases where the workflow allows.
- Officers cannot access cases assigned to another officer.

### 6.3 Accept Unassigned Case

1. Open Officer Cases.
2. Find an open unassigned case.
3. Open the case.
4. Click Accept Case.

After acceptance:

- The officer becomes assigned to the case.
- Case activity records the assignment.
- Notifications are created where appropriate.

### 6.4 Manage Assigned Case

1. Open the case.
2. Review case details.
3. Update urgency if needed.
4. Update status.
5. Refer, resolve, close, or reopen according to workflow.
6. Add action note if needed.

Rules:

- Major actions are recorded in activity history.
- Resolution requires a resolution summary where required by validation.

### 6.5 Reply To Registered User

1. Open an assigned registered case.
2. Use the message form.
3. Submit reply.

After reply:

- Message is saved in the case thread.
- User gets a neutral notification.
- Read/unread status is tracked.
- Activity history records the reply without exposing private message text.

### 6.6 Add Internal Note

1. Open an assigned case.
2. Use Internal Notes.
3. Add note.

Rules:

- Internal notes are visible only to support officers and admins.
- Registered users and anonymous visitors never see internal notes.
- Activity history records that a note was added, but not the note content.

## 7. Admin Workflow

### 7.1 Log In

1. Open `/login`.
2. Log in with an admin account.
3. The system redirects to `/admin/dashboard`.

### 7.2 Admin Dashboard

The admin dashboard shows:

- Users
- Support officers
- Total cases
- Open cases
- Under review cases
- In-progress cases
- Resolved cases
- Anonymous cases
- Registered cases
- High-priority cases
- Published articles
- Support resources
- Recent cases
- Recent activities
- Charts and statistics

Privacy:

- Dashboard widgets do not show full case descriptions, private messages, internal notes, or anonymous contact details.

### 7.3 Manage Cases

1. Go to Cases.
2. Search by case reference.
3. Filter by status, urgency, abuse type, officer, or date.
4. Open a case.
5. Assign or reassign officer.
6. Change status.
7. Change urgency.
8. Add referral or resolution information.
9. Save.

Rules:

- Reassignment is recorded in case activity.
- Status changes are recorded.
- Urgency changes are recorded.
- Admins can see full case details.

### 7.4 Manage Users

1. Go to Users.
2. Search by name or email.
3. Filter by status.
4. Open a user profile.
5. Activate or deactivate the account.

Rules:

- Admins do not see plain-text passwords.
- Passwords remain hashed.
- Deactivated users cannot log in.

### 7.5 Manage Support Officers

1. Go to Support Officers.
2. Create officer account.
3. Enter staff details, specialization, status, and temporary password.
4. Edit officer profile where needed.
5. Activate or deactivate officers.

Rules:

- Officers with historical cases should be deactivated, not deleted.
- Existing case records remain linked for history.

### 7.6 Manage Reports

1. Go to Reports.
2. Select filters:
   - Date range
   - Status
   - Abuse type
   - Urgency
   - Officer
   - Anonymous or registered source
3. Review statistics.
4. Export CSV if needed.
5. Use print-friendly browser printing if needed.

Rules:

- Reports exclude private descriptions, messages, internal notes, anonymous contact details, and safe contact instructions.

### 7.7 Manage Audit Logs

1. Go to Audit Logs.
2. Search or filter logs.
3. Open log detail.

Audit logs track:

- Admin login
- Officer login
- User status changes
- Officer creation and updates
- Case assignment and status changes
- Article actions
- Resource actions
- Settings changes
- Abuse type actions

Rules:

- Audit logs are read-only in the normal interface.
- Audit logs should not store passwords, private message bodies, internal note content, anonymous access codes, or sensitive case descriptions.

## 8. Notifications Workflow

Purpose:

- Keep users, officers, and admins informed without exposing sensitive details.

Examples:

- Case submitted
- Case assigned
- Case status updated
- Support officer replied
- User replied
- Critical/high-priority case created

How to operate:

1. Log in.
2. Click Notifications.
3. Open a notification.
4. Use Mark All Read when needed.

Rules:

- Notification wording is neutral.
- Notifications use case references and general labels.
- Notifications must not reveal sensitive descriptions.

## 9. Case Status Workflow

Normal workflow:

1. Submitted
2. Under Review
3. Assigned
4. In Progress
5. Awaiting User
6. Referred
7. Resolved
8. Closed

Notes:

- A case can be reopened when allowed.
- Major changes are recorded in activity history.
- Critical urgency is highlighted for authorized staff.
- Critical submissions create internal notifications.

## 10. File Attachment Workflow

Allowed file types:

- JPG
- JPEG
- PNG
- PDF

Rules:

- Maximum file count and size are validated.
- Files are renamed safely.
- Files are stored privately.
- Downloads are served only through authorized Laravel routes.
- Users can only access attachments from their own cases.
- Officers can only access attachments for authorized cases.
- Anonymous tracking does not expose uploaded files.

## 11. Quick Exit Workflow

Purpose:

- Let users leave the platform quickly.

How it works:

1. User clicks Quick Exit.
2. The browser redirects to the configured neutral URL.

Admin setup:

1. Go to Settings.
2. Set Quick Exit Destination.
3. Save.

Important:

- Quick Exit does not erase browser history.
- Quick Exit does not erase network logs.
- Quick Exit does not erase saved passwords.
- Quick Exit does not erase downloads.
- Quick Exit does not erase device activity.

## 12. Testing Workflow

Run all automated tests:

```bash
php artisan test
```

Run frontend build:

```bash
npm run build
```

Useful manual checks:

1. Public pages load.
2. Registration works.
3. Login redirects by role.
4. Disabled user cannot log in.
5. Registered user can submit and view only their own case.
6. Anonymous user receives case reference and access code.
7. Anonymous tracking rejects invalid access code.
8. Officer cannot access another officer assigned case.
9. Admin can assign and update cases.
10. Internal notes are hidden from users and anonymous visitors.
11. Reports do not show private descriptions.
12. Notifications use neutral wording.
13. Quick Exit redirects correctly.
14. Mobile navigation works.

## 13. Troubleshooting

### 13.1 MySQL Connection Error

Check:

- MySQL server is running.
- Database `domestic_support` exists.
- `.env` database credentials are correct.
- `php artisan config:clear` has been run.

### 13.2 Tables Missing

Run:

```bash
php artisan migrate
```

Or for local reset:

```bash
php artisan migrate:fresh --seed
```

### 13.3 Demo Accounts Missing

Make sure `APP_ENV=local`, then run:

```bash
php artisan db:seed
```

### 13.4 CSS Or JavaScript Not Updating

Run:

```bash
npm run build
```

Or during development:

```bash
npm run dev
```

### 13.5 Storage Link Missing

Run:

```bash
php artisan storage:link
```

### 13.6 Config Changes Not Applying

Run:

```bash
php artisan config:clear
php artisan cache:clear
```

## 14. Privacy And Safety Rules For Operators

- Do not enter real survivor information in demo data.
- Do not publish unverified emergency contacts.
- Do not share anonymous access codes.
- Do not expose case descriptions in screenshots unless required for a private academic demonstration.
- Do not copy private messages or internal notes into reports.
- Use neutral language in notifications and administrative notes.
- Keep admin and officer accounts limited to trusted users.
- Deactivate accounts that should no longer have access.
- Keep production `.env` private.
- Keep `APP_DEBUG=false` in production.

## 15. Recommended Demonstration Flow

For project presentation:

1. Show public homepage.
2. Show awareness articles and resources.
3. Submit a registered support case as `user@example.com`.
4. Log in as admin and assign the case.
5. Log in as officer and accept or manage the assigned case.
6. Send a private reply.
7. Log back in as user and view the reply.
8. Add an internal note as officer and show that user cannot see it.
9. Submit an anonymous case.
10. Track it using case reference and access code.
11. Show admin dashboard statistics.
12. Show reports.
13. Show audit logs.
14. Show system settings, including Quick Exit and critical guidance.
15. Click Quick Exit and explain its limitation.
