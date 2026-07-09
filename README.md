# 7NVENT — Hotel Inventory Management System
**CSC2854 Final Year Project | Session 1 2026/2027**

| | |
|---|---|
| **Developer** | Muhammad Syukri Bin Zainal Abidin |
| **Student ID** | BCS2402-042 |
| **Class** | DCS 6B |
| **Supervisor** | Pn. Nini Aniza Binti Zakaria |
| **Institution** | Kolej Profesional Mara Beranang |

---

## 📋 System Requirements

| Component | Requirement |
|---|---|
| PHP | 8.1 or higher |
| MySQL | 8.0 or higher |
| Web Server | Apache (XAMPP recommended) |
| Browser | Chrome, Firefox, Edge (latest) |
| RAM | Minimum 4GB |

---

## 🚀 Quick Installation (XAMPP)

### Step 1 — Install XAMPP
Download from https://www.apachefriends.org and install.
Start **Apache** and **MySQL** modules.

### Step 2 — Copy Project Files
```
Copy the entire `7nvent/` folder into:
C:\xampp\htdocs\7nvent\
```

### Step 3 — Create Database
1. Open browser → go to `http://localhost/phpmyadmin`
2. Click **New** → create database named `7nvent`
3. Select the `7nvent` database
4. Click **Import** tab → choose file `database/7nvent_schema.sql`
5. Click **Go** — this creates all tables and demo data

### Step 4 — Configure Database Connection
Open `config/config.php` and update if needed:
```php
define('DB_HOST', 'localhost');   // usually localhost
define('DB_NAME', '7nvent');      // database name
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password (empty for XAMPP default)
```

### Step 5 — Configure Apache URL Rewrite
Make sure `mod_rewrite` is enabled in XAMPP.
The `.htaccess` file in `public/` handles clean URLs automatically.

### Step 6 — Access the System
```
Landing Page : http://localhost/7nvent/public/
Dashboard    : http://localhost/7nvent/public/dashboard
Login        : http://localhost/7nvent/public/login
```

---

## 🔑 Demo Login Credentials

| Name | Username | Password | Role | Access |
|---|---|---|---|---|
| Elizabeth Lee | elizabeth.lee | password | Inventory Manager | Full Admin |
| Alvin Yuan | alvin.yuan | password | Housekeeping Manager | Update |
| Sarah Qinn | sarah.qinn | password | Procurement Officer | PO Manager |
| Abdul Hakim | abdul.hakim | password | IT Administrator | System Admin |
| Farah Nabilah | farah.nabilah | password | Hotel GM | Approval Only |
| Melissa Yee | melissa.yee | password | Supervisor | Spectator |

---

## 📁 Project Structure

```
7nvent/
├── config/
│   └── config.php              ← Database & app settings
├── app/
│   ├── Database.php            ← PDO database connection
│   ├── Auth.php                ← Authentication & session management
│   └── Http/Controllers/
│       ├── AuthController.php
│       ├── DashboardController.php
│       ├── InventoryController.php
│       ├── PurchaseOrderController.php
│       ├── AlertController.php
│       ├── SupplierController.php
│       ├── LocationController.php
│       ├── ReportController.php
│       ├── UserController.php
│       ├── SettingsController.php
│       └── LandingController.php
├── database/
│   └── 7nvent_schema.sql       ← Full DB schema + seed data
├── resources/views/
│   ├── layouts/app.php         ← Main layout (sidebar + header)
│   ├── auth/login.php
│   ├── dashboard/index.php
│   ├── inventory/
│   ├── purchase-orders/
│   ├── alerts/
│   ├── suppliers/
│   ├── locations/
│   ├── reports/
│   ├── users/
│   ├── settings/
│   └── landing/index.php
└── public/
    ├── index.php               ← Front controller / router
    └── .htaccess               ← URL rewrite rules
```

---

## ⚙️ System Features

| Module | Features | FR Reference |
|---|---|---|
| Authentication | Login, logout, session timeout, lockout after 3 fails | FR-01 |
| Dashboard | KPI stats, bar chart, stock levels, alerts, activity feed | FR-02 |
| Inventory | CRUD items, category filter, search, status auto-update | FR-03 |
| Alerts | Par-level breach detection, expiry warning, approve/dismiss | FR-04 |
| Purchase Orders | Create PO, track status, supplier linkage, auto-numbering | FR-05 |
| FIFO / Expiry | Expiry date tracking, FIFO flag, waste/expiry reports | FR-06 |
| Reports | 6 report types, stock summary, PO history, waste & expiry | FR-07 |
| Suppliers | Directory, rating, lead time, YTD orders | — |
| Locations | Multi-location tracking, capacity monitoring | — |
| Users & Roles | RBAC — 6 roles, create/edit users, access level | FR-01 |
| Settings | Toggle notifications, FIFO, backup, PDPA compliance mode | NFR-01~06 |

---

## 🔐 Role-Based Access Control (RBAC)

| Role | Inventory | Alerts | PO | Reports | Users | Settings |
|---|---|---|---|---|---|---|
| Inventory Manager | Full | Full | Full | Full | Full | Full |
| Housekeeping Manager | View + Update | View | View | View | ✗ | ✗ |
| Procurement Officer | View | Approve | Full | View | ✗ | ✗ |
| IT Administrator | View | View | View | View | Full | Full |
| Hotel GM | View | View | View | Full | ✗ | ✗ |
| Supervisor | View | View | View | View | ✗ | ✗ |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 (MVC pattern) |
| Database | MySQL 8.0 |
| Frontend | Bootstrap 5.3 + Bootstrap Icons |
| Charts | Chart.js 4.4 |
| Local Server | XAMPP (Apache) |

---

## 📊 Database Tables

1. `roles` — System roles (6 roles)
2. `users` — User accounts with RBAC
3. `suppliers` — Supplier directory
4. `locations` — Storage locations
5. `inventory_items` — Main inventory table
6. `purchase_orders` — PO header records
7. `purchase_order_items` — PO line items
8. `alerts` — System alerts & notifications
9. `audit_logs` — Complete audit trail
10. `settings` — System configuration

---

## 📝 Notes for Submission

- All passwords are hashed using **bcrypt** (PHP `password_hash()`)
- Session timeout: **30 minutes** of inactivity
- Auto-lockout: **15 minutes** after 3 failed login attempts
- Audit trail logs **every action** with timestamp, user ID, and IP address
- PDPA Compliance Mode can be toggled from Settings page

---

*Generated for CSC2854 FYP — 7NVENT v1.0 | June 2026*
