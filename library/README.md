# LibraFlow — Book Borrowing System
### PHP + MySQL Library Management System

---

## Overview

LibraFlow is a complete, OOP-based book borrowing system built with PHP and MySQL.
It provides separate interfaces for **admins/librarians** and **borrowers**, with secure
authentication, borrow request workflows, automatic overdue detection, and penalty tracking.

> **Penalty Policy:** ₱5.00 per day is charged **only** when a book is returned past its
> due date. No fee is charged for on-time returns.

---

## Tech Stack

| Layer    | Technology                         |
|----------|------------------------------------|
| Backend  | PHP 8.1+ (OOP, PDO, bcrypt)        |
| Database | MySQL 8.0+ / MariaDB 10.6+         |
| Frontend | Bootstrap 5.3, Bootstrap Icons     |
| Charts   | Chart.js 4                         |
| Fonts    | Playfair Display, DM Sans (Google) |

---

## Directory Structure

```
library/
├── index.php                  ← Root redirect
├── database.sql               ← Full DB creation + sample data
│
├── config/
│   └── database.php           ← DB credentials & constants
│
├── classes/
│   ├── User.php               ← User auth, CRUD, notifications
│   ├── Book.php               ← Book catalog, categories, search
│   ├── BookRequest.php        ← Borrow request flow
│   └── BorrowedBook.php       ← Borrow records, penalties, returns
│
├── includes/
│   ├── auth.php               ← Session helpers, flash messages
│   ├── header.php             ← Shared HTML head + sidebar
│   └── footer.php             ← Shared JS + closing tags
│
├── auth/
│   ├── login.php              ← Login page
│   ├── register.php           ← Self-registration (borrowers)
│   └── logout.php             ← Session destroy
│
├── admin/
│   ├── index.php              ← Dashboard with stats & charts
│   ├── books.php              ← Book catalog management (CRUD)
│   ├── categories.php         ← Category management
│   ├── requests.php           ← Approve / reject borrow requests
│   ├── borrowed.php           ← Borrowed book records & returns
│   ├── users.php              ← User management
│   ├── reports.php            ← Analytics + CSV export
│   ├── settings.php           ← Loan period & penalty settings
│   └── notifications.php      ← Admin notification center
│
├── user/
│   ├── index.php              ← Browse & search books
│   ├── requests.php           ← My borrow requests
│   ├── borrowed.php           ← My active borrows & history
│   ├── profile.php            ← Edit profile & change password
│   └── notifications.php      ← User notification center
│
└── uploads/                   ← Book cover images (auto-created)
```

---

## Installation

### 1. Requirements

- PHP 8.1 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Apache / Nginx with `mod_rewrite` (optional)
- A local server like XAMPP, Laragon, or WAMP

### 2. Setup Steps

**Step 1 — Copy files**
```
Copy the `library/` folder to your web server root:
  - XAMPP: C:/xampp/htdocs/library
  - Laragon: C:/laragon/www/library
```

**Step 2 — Create the database**
```sql
-- Open phpMyAdmin or run in MySQL shell:
SOURCE /path/to/library/database.sql;
```
This creates `library_db` with all tables and sample data.

**Step 3 — Configure database connection**
```php
// Edit config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // your MySQL username
define('DB_PASS', '');             // your MySQL password
define('DB_NAME', 'library_db');
define('BASE_URL', 'http://localhost/library');  // adjust to your setup
```

**Step 4 — Set folder permissions**
```
Ensure the uploads/ folder is writable:
  chmod 755 library/uploads/    (Linux/Mac)
```

**Step 5 — Open in browser**
```
http://localhost/library
```

---

## Default Login Credentials

| Role      | Email                    | Password     |
|-----------|--------------------------|--------------|
| Admin     | admin@library.com        | password     |
| Librarian | librarian@library.com    | password     |
| Borrower  | juan@mail.com            | password     |
| Borrower  | ana@mail.com             | password     |

> **Change these passwords immediately after first login!**

---

## Database Tables

### `users`
| Column     | Type        | Notes                            |
|------------|-------------|----------------------------------|
| id         | INT PK AI   |                                  |
| name       | VARCHAR(150)|                                  |
| email      | VARCHAR(150)| UNIQUE                           |
| password   | VARCHAR(255)| bcrypt hashed                    |
| role       | ENUM        | admin, librarian, borrower       |
| phone      | VARCHAR(20) |                                  |
| address    | TEXT        |                                  |
| status     | ENUM        | active, inactive, suspended      |
| created_at | TIMESTAMP   |                                  |

### `categories`
| Column        | Type         |
|---------------|--------------|
| category_id   | INT PK AI    |
| category_name | VARCHAR(100) |
| description   | TEXT         |

### `books`
| Column             | Type         | Notes                     |
|--------------------|--------------|---------------------------|
| book_id            | INT PK AI    |                           |
| title              | VARCHAR(255) |                           |
| author             | VARCHAR(150) |                           |
| publication        | VARCHAR(150) |                           |
| category_id        | INT FK       | → categories              |
| isbn               | VARCHAR(20)  | UNIQUE                    |
| description        | TEXT         |                           |
| quantity           | INT          | Total physical copies     |
| available_quantity | INT          | Copies not currently out  |
| image              | VARCHAR(255) | Filename in uploads/      |
| year_published     | YEAR         |                           |

### `book_requests`
| Column       | Type      | Notes                        |
|--------------|-----------|------------------------------|
| id           | INT PK AI |                              |
| book_id      | INT FK    | → books                      |
| user_id      | INT FK    | → users (borrower)           |
| request_date | TIMESTAMP |                              |
| status       | ENUM      | pending, approved, rejected  |
| admin_note   | TEXT      | Librarian's message to user  |
| processed_by | INT FK    | → users (admin/librarian)    |
| processed_at | TIMESTAMP |                              |

### `borrowed_books`
| Column          | Type          | Notes                              |
|-----------------|---------------|------------------------------------|
| id              | INT PK AI     |                                    |
| book_id         | INT FK        | → books                            |
| user_id         | INT FK        | → users                            |
| request_id      | INT FK NULL   | → book_requests                    |
| borrow_date     | DATE          |                                    |
| due_date        | DATE          | borrow_date + loan_period_days     |
| return_date     | DATE NULL     | NULL until returned                |
| penalty         | DECIMAL(10,2) | ₱5/day × overdue days; 0 if on-time|
| penalty_per_day | DECIMAL(10,2) | Stored rate at time of borrow      |
| status          | ENUM          | borrowed, returned, overdue        |
| issued_by       | INT FK        | → users (admin/librarian)          |

### `notifications`
| Column     | Type      | Notes                           |
|------------|-----------|---------------------------------|
| id         | INT PK AI |                                 |
| user_id    | INT FK    | → users                         |
| title      | VARCHAR   |                                 |
| message    | TEXT      |                                 |
| type       | ENUM      | info, success, warning, danger  |
| is_read    | TINYINT   | 0 = unread, 1 = read            |

### `system_settings`
| Column        | Type         | Notes                       |
|---------------|--------------|-----------------------------|
| setting_key   | VARCHAR(100) | PK                          |
| setting_value | VARCHAR(255) |                             |

**Default Settings:**
- `loan_period_days` = 14
- `penalty_per_day` = 5 (₱5.00)
- `penalty_grace` = 0
- `max_borrows` = 3

---

## Table Relationships

```
categories ──< books ──< book_requests >── users
                    \──< borrowed_books >── users
                                            └──> notifications
```

- `books.category_id` → `categories.category_id` (SET NULL on delete)
- `book_requests.book_id` → `books.book_id` (CASCADE)
- `book_requests.user_id` → `users.id` (CASCADE)
- `borrowed_books.book_id` → `books.book_id` (CASCADE)
- `borrowed_books.user_id` → `users.id` (CASCADE)
- `borrowed_books.request_id` → `book_requests.id` (SET NULL)
- `notifications.user_id` → `users.id` (CASCADE)

---

## How Penalties Work

```
Borrow Date ──────── Due Date ──────── Overdue Zone
                         │
                         └── penalty starts HERE (next day after due date)
                             ₱5.00 × days_overdue
```

- **No fee** while the book is within the loan period.
- **₱5.00/day** starts the day after the due date.
- Penalty is calculated automatically when:
  - The admin marks a book as returned (`admin/borrowed.php`)
  - The daily sync runs (`syncOverdueStatuses()` called on dashboard/user load)
- The penalty rate is configurable in **Admin → Borrow Settings**.

---

## Admin Module Guide

| Page                   | Path                     | What you can do                              |
|------------------------|--------------------------|----------------------------------------------|
| Dashboard              | admin/index.php          | Stats, chart, quick actions, overdue alerts  |
| Book Catalog           | admin/books.php          | Add, edit, delete books; upload covers       |
| Categories             | admin/categories.php     | Manage book categories                       |
| Borrow Requests        | admin/requests.php       | Approve or reject user requests              |
| Borrowed Books         | admin/borrowed.php       | View all loans; mark returns; see penalties  |
| Users                  | admin/users.php          | Manage all user accounts & roles             |
| Reports                | admin/reports.php        | Inventory + history + overdue CSV export     |
| Borrow Settings        | admin/settings.php       | Set loan period, grace days, penalty/day     |

---

## User Module Guide

| Page            | Path                    | What you can do                              |
|-----------------|-------------------------|----------------------------------------------|
| Browse Books    | user/index.php          | Search, filter, and request books            |
| My Requests     | user/requests.php       | Track pending/approved/rejected requests     |
| My Borrowed     | user/borrowed.php       | See active borrows, due dates, and penalties |
| My Profile      | user/profile.php        | Edit info, change password, full history     |
| Notifications   | user/notifications.php  | Request approvals, overdue alerts            |

---

## Security Notes

- All SQL queries use **PDO prepared statements** (no raw interpolation).
- Passwords are hashed with **bcrypt** (cost 12).
- Sessions are used for authentication; role checks on every protected page.
- File uploads validate extension whitelist (`jpg, jpeg, png, gif, webp`).
- All user input rendered via `htmlspecialchars()` (`sanitize()` helper).

---

## Customization

**Change loan period:** Admin → Borrow Settings → Default Loan Period

**Change penalty rate:** Admin → Borrow Settings → Penalty per Overdue Day

**Add a category:** Admin → Categories → Add Category

**Change base URL:**
```php
// config/database.php
define('BASE_URL', 'http://your-domain.com/library');
```
