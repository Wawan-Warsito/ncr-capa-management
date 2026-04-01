# Technical Documentation

## 1. System Overview
The NCR-CAPA Management System is a full-stack web application designed to manage Non-Conformance Reports (NCR) and Corrective and Preventive Actions (CAPA) within an organization. It facilitates tracking, approval workflows, and reporting.

### Tech Stack
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: React 19 (Vite)
- **Database**: MySQL 8.0 / MariaDB 10.4+
- **Styling**: Tailwind CSS 4.0
- **Testing**: PHPUnit (Backend), Vitest (Frontend), Cypress (E2E)

## 2. Database Schema

### Core Tables
- `users`: Stores user accounts, authentication data, and profile info.
- `roles`: Defines user roles (Admin, QC Manager, User, etc.) and permissions.
- `departments`: Organizational units (Quality Control, Production, etc.).
- `ncrs`: Main table for Non-Conformance Reports.
- `capas`: Corrective and Preventive Actions linked to NCRs.

### Helper Tables
- `defect_categories`: Categorization of defects (e.g., Welding, Material).
- `severity_levels`: Severity classification (Minor, Major, Critical).
- `disposition_methods`: Methods for resolving NCRs (Rework, Scrap).
- `activity_logs`: Audit trail for all system actions.
- `notifications`: In-app notifications for users.
- `settings`: System-wide configuration.

### Relationships (Key)
- `NCR` belongs to `User` (Creator), `Department` (Finder/Receiver).
- `CAPA` belongs to `NCR` (One-to-One or One-to-Many depending on implementation, currently 1:1 via `ncr_id`).
- `User` belongs to `Role` and `Department`.

## 3. API Reference (RESTful)

### Authentication
- `POST /api/auth/login`: Authenticate user and return token.
- `POST /api/auth/logout`: Invalidate current session.
- `GET /api/auth/me`: Get current user profile.

### NCR Management
- `GET /api/ncrs`: List all NCRs (paginated, filterable).
- `POST /api/ncrs`: Create a new NCR.
- `GET /api/ncrs/{id}`: Get NCR details.
- `PUT /api/ncrs/{id}`: Update NCR.
- `DELETE /api/ncrs/{id}`: Delete NCR (Soft delete if implemented).
- `POST /api/ncrs/{id}/submit`: Submit NCR for approval.
- `POST /api/ncrs/{id}/approve`: Approve NCR (various stages).

### CAPA Management
- `GET /api/capas`: List CAPAs.
- `POST /api/capas`: Create CAPA (usually from NCR).
- `PUT /api/capas/{id}/progress`: Update CAPA progress.
- `POST /api/capas/{id}/verify`: Verify CAPA effectiveness.

### Master Data (Admin)
- `GET /api/admin/users`: Manage users.
- `GET /api/admin/departments`: Manage departments.
- `GET /api/admin/settings`: System settings.

## 4. Security
- **Authentication**: Laravel Sanctum (SPA/Token based).
- **Authorization**: Role-Based Access Control (RBAC) via Middleware (`CheckRole`).
- **Data Protection**:
  - Passwords hashed via Bcrypt.
  - SQL Injection protection via Eloquent ORM.
  - XSS protection via React (automatic escaping) and backend validation.
  - CSRF protection for stateful requests.

## 5. Development Workflow
- **Backend**: `php artisan serve` (Port 8000)
- **Frontend**: `npm run dev` (Vite)
- **Testing**: `php artisan test`, `npm test`, `npx cypress run`
