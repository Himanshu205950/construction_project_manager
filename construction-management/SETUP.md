# Construction Project Management System — Setup Guide

A PHP + MySQL + HTML/CSS web app for managing construction projects, workers,
materials, expenses, and progress. (Plain HTML/CSS can't talk to a SQL
database on its own, so PHP is the bridge between them — that's why this
package includes a light PHP backend, per your original requirement note.)

## 1. Requirements
- XAMPP / WAMP / MAMP (or any Apache + PHP 8+ + MySQL stack)
- A browser

## 2. Installation

1. **Copy the project folder**
   Copy the whole `construction-management` folder into your server's web root:
   - XAMPP (Windows): `C:\xampp\htdocs\construction-management`
   - XAMPP (Mac/Linux): `/Applications/XAMPP/htdocs/construction-management` or `/opt/lampp/htdocs/...`

2. **Start Apache and MySQL** from the XAMPP control panel.

3. **Create the database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Click "Import" (or "SQL" tab) and run the file `database.sql`
     included in this project. This creates the `construction_db`
     database, all tables, and sample demo data.

4. **Check your DB credentials**
   Open `config/db.php` and confirm the values match your MySQL setup
   (defaults are `root` / no password, which is standard for XAMPP):
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'construction_db';
   $DB_USER = 'root';
   $DB_PASS = '';
   ```

5. **Open the app in your browser**
   ```
   http://localhost/construction-management/
   ```

## 3. Default login
```
Username: admin
Password: admin123
```
Change this password after your first login by updating the `users` table
(store a new hash with PHP's `password_hash()`), or add a "change password"
page as an extra feature.

## 4. Project Structure
```
construction-management/
├── config/
│   └── db.php                  → database connection
├── includes/
│   ├── auth_check.php          → login guard for protected pages
│   ├── header.php               → shared layout + sidebar
│   └── footer.php
├── css/
│   └── style.css                → all styling
├── database.sql                 → full schema + sample data
├── index.php                    → redirects to login/dashboard
├── login.php / logout.php
├── dashboard.php                 → KPIs + Cost Control module
├── projects.php / project_form.php
├── materials.php / material_form.php / material_usage_save.php
├── workers.php / worker_form.php
├── attendance.php
├── expenses.php / expense_save.php
├── progress.php
└── reports.php
```

## 5. How the modules map to your spec

| Feature (your spec)     | File(s)                                             |
|--------------------------|------------------------------------------------------|
| Dashboard                | `dashboard.php`                                       |
| Project Management        | `projects.php`, `project_form.php`                    |
| Material Management        | `materials.php`, `material_form.php`, `material_usage_save.php` |
| Worker Management          | `workers.php`, `worker_form.php`                      |
| Attendance                 | `attendance.php`                                       |
| Expense Management         | `expenses.php`, `expense_save.php`                    |
| Progress Tracking          | `progress.php`                                          |
| Reports                    | `reports.php`                                           |
| Project Cost Control       | Built into `dashboard.php` (the budget flow diagram)   |

## 6. Notes / possible next steps
- All forms use PHP `PDO` prepared statements (protects against SQL injection).
- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- Material stock auto-decrements when usage is logged (`material_usage_save.php`).
- Worker "Total Wages" is auto-calculated from attendance (Present = 1 day,
  Half Day = 0.5 day) × daily wage.
- To make this resume-ready, consider adding: user roles (site engineer vs
  admin), file uploads for site photos, charts (Chart.js) on the dashboard,
  and CSV/PDF export on the Reports page.
