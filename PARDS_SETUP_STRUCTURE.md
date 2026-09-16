# Property Accountability Records and Disposal System (PARDS)

This document defines the initial Laravel project setup and folder structure only.

## 1. Project Folder Structure

```text
RBLZCapstone/
|-- app/
|   |-- Http/
|   |   |-- Controllers/
|   |   |   |-- Admin/
|   |   |   |-- Staff/
|   |   |   |-- Teacher/
|   |   |   |-- DashboardController.php
|   |-- Models/
|   |   |-- User.php
|   |   |-- Concerns/
|-- bootstrap/
|-- config/
|-- database/
|   |-- factories/
|   |-- migrations/
|   |-- seeders/
|-- public/
|-- resources/
|   |-- css/
|   |-- js/
|   |-- views/
|   |   |-- admin/
|   |   |-- dashboard/
|   |   |   |-- index.blade.php
|   |   |-- layouts/
|   |   |   |-- app.blade.php
|   |   |   |-- partials/
|   |   |   |   |-- navbar.blade.php
|   |   |   |   |-- sidebar.blade.php
|   |   |-- staff/
|   |   |-- teacher/
|   |   |-- welcome.blade.php
|-- routes/
|   |-- web.php
|-- storage/
|-- tests/
|-- .env
|-- artisan
|-- composer.json
|-- package.json
```

## 2. Installation Commands Needed

### Create the Laravel project

```bash
composer create-project laravel/laravel pards
cd pards
```

### Configure MySQL in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pards_db
DB_USERNAME=root
DB_PASSWORD=
```

### Install frontend dependencies for Bootstrap 5

```bash
npm install
npm install bootstrap @popperjs/core
```

### Finalize the base app

```bash
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

## 3. Packages to Install

### QR code generation

Recommended package:

```bash
composer require simplesoftwareio/simple-qrcode
```

Alternative:

```bash
composer require bacon/bacon-qr-code
```

### PDF export

Recommended package:

```bash
composer require barryvdh/laravel-dompdf
```

### Optional Excel export

Recommended package:

```bash
composer require maatwebsite/excel
```

## 4. Initial Layout Plan

### Sidebar

- Show the system title or PARDS logo at the top.
- Group navigation by module, not by role logic yet.
- Suggested menu items:
  - Dashboard
  - Property Records
  - Accountability Forms
  - Disposal Requests
  - Reports
  - Users and Roles
  - Settings
- Reserve role filtering so Admin sees everything, while Staff and Teacher see only allowed modules later.

### Navbar

- Place a page title on the left.
- Add quick actions on the right:
  - Notifications
  - User name
  - Role badge
  - Profile dropdown
  - Logout
- Keep the navbar fixed or sticky for desktop dashboards.

### Dashboard Layout

- Use a two-column shell:
  - Left: fixed sidebar
  - Right: navbar plus page content
- Dashboard content sections:
  - Summary cards
  - Recent property activity
  - Pending disposal requests
  - Recent accountability updates
- Keep the main content in `@yield('content')` so every role dashboard can reuse the same layout.

## 5. Suggested Files to Create Step by Step

### Phase 1: Core layout

1. `resources/views/layouts/app.blade.php`
2. `resources/views/layouts/partials/navbar.blade.php`
3. `resources/views/layouts/partials/sidebar.blade.php`
4. `resources/views/dashboard/index.blade.php`

### Phase 2: Routing and controllers

5. `routes/web.php`
6. `app/Http/Controllers/DashboardController.php`
7. `app/Http/Controllers/Admin/`
8. `app/Http/Controllers/Staff/`
9. `app/Http/Controllers/Teacher/`

### Phase 3: Authentication and roles

10. `database/migrations/xxxx_xx_xx_add_role_to_users_table.php`
11. `app/Models/User.php`
12. `app/Http/Middleware/`
13. `database/seeders/RoleSeeder.php`

### Phase 4: Feature modules

14. `app/Models/Property.php`
15. `app/Models/AccountabilityRecord.php`
16. `app/Models/DisposalRecord.php`
17. `app/Models/Department.php`
18. `app/Models/Teacher.php`
19. `database/migrations/`
20. `resources/views/admin/`
21. `resources/views/staff/`
22. `resources/views/teacher/`

## Notes

- The current project is already a Laravel 12 application.
- MySQL should replace the default SQLite setup in `.env`.
- Bootstrap 5 is planned for the UI layer.
- Role-based access is prepared in the folder structure only and not fully implemented yet.
