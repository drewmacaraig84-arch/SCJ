# School of Criminal Justice Education (SCJE) Information System

A full-stack, blue-themed institutional website and information system for the **Divine Word College of Calapan – School of Criminal Justice Education (DWCC-SCJE)**.

Built with PHP 8.3, dual MySQL/SQLite engine, modular Middleware pipeline, and responsive modern frontend with zero build steps.

---

## Features & Structure

1. **Top Institutional Header**: Official DWCC SCJE emblem, Criminology scales & laurel badge, and clear institutional title.
2. **Sticky Navigation Bar**: Smooth navigation with dropdowns for Home, About, Research, Laboratories, Faculty, Resources, Contact, and Portal Login.
3. **Hero Section**: Tactical law enforcement backdrop, introductory text, and 4 quick access tiles (Criminological Research, Laboratory Equipment, Faculty, Research Resources).
4. **Institutional Pillars**: 3 cards for **VISION**, **MISSION**, and **GOALS**.
5. **About Section**: Comprehensive narrative of the **HISTORY OF SCJ**.
6. **16 Criminological Research Field Cards**: Interactive discipline cards with live filtering matching the reference sample.
7. **Research Data Table (from Sketch)**:
   - Live Search: `SEARCH: [_____]`
   - Columns: `NAME` | `RESEARCH TITLE` | `MONTH / YEAR` | `ACTION`
   - Abstract & Details modal viewer.
8. **5 Specialized Laboratory Facilities Cards**: Visual cards with preview graphics and bullet lists (Criminalistics, Crime Scene Investigation, Forensic Science, Forensic Ballistics, Other Specialized Areas).
9. **Laboratory Equipment Inventory Table (from Sketch)**:
   - Live Search: `SEARCH: [_____]`
   - Columns: `Equipment Code` | `Equipment Name` | `BRAND` | `MODEL` | `CURRENT LOCATION` | `STATUS`
10. **Faculty & Staff Hierarchy (from Sketch)**:
    - Top Level: **OIC - DEAN, SCJ** with gold emblem and credentials.
    - Subordinate Grid: Program Chairs, Coordinators, Custodians, and Professors.
11. **Research Resources Repository & Working Contact Form**:
    - Direct inquiry submission with CSRF and rate-limit protection.
12. **Information System Login Portal (from Sketch)**:
    - ID Number login modal and dedicated authentication portal (`USERNAME: ID NUMBER` and `PASSWORD:`).
13. **Administrator Dashboard (`/admin`)**:
    - Manage Research papers (Add, Delete).
    - Manage Laboratory Equipment (Add, Delete).
    - Manage Faculty members (Add, Delete).
    - Manage Contact Inquiries (View, Mark Read, Delete).

---

## Middleware Architecture

The application runs an enterprise-grade security middleware layer in `includes/middleware/`:
- **`SecurityHeadersMiddleware`**: Injects CSP, X-Frame-Options, and anti-sniffing headers.
- **`RateLimitMiddleware`**: Protects against brute-force login and spam submissions.
- **`CsrfMiddleware`**: Validates anti-CSRF security tokens on all POST/PUT/DELETE requests.
- **`AuthMiddleware`**: Verifies user session state and protects private routes.
- **`RoleMiddleware`**: Role-Based Access Control (`admin`, `faculty`, `student`).

---

## Local Testing Setup

### Option 1: Instant Local Testing via PHP CLI (Zero Configuration)
Open terminal / PowerShell in `c:\laragon\www\SCJ` and execute:
```bash
php setup.php
php -S localhost:8000
```
Open your browser and visit:
👉 **`http://localhost:8000`**

*(Note: If MySQL is not running, the system will automatically utilize SQLite `database/scj.sqlite` with zero errors).*

---

### Option 2: Running via Laragon (Apache + MySQL)
1. Open **Laragon**.
2. Click **Start All** (Starts Apache and MySQL).
3. If using MySQL, create the database or let `setup.php` auto-create it:
   ```bash
   php setup.php
   ```
4. Access via browser:
   👉 **`http://localhost/SCJ`** or **`http://scj.test`**

---

## Environment File (`.env`)

The project comes pre-configured with a `.env` file:
```env
APP_NAME="School of Criminal Justice Information System"
APP_ENV=local
APP_URL=http://localhost:8000

# Database Engine ('mysql' or 'sqlite')
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scj_db
DB_USERNAME=root
DB_PASSWORD=

# SQLite fallback path
DB_SQLITE_PATH=database/scj.sqlite

# Default Administrator Credentials
DEFAULT_ADMIN_ID=Drew
DEFAULT_ADMIN_PASSWORD=admin
```

---

## Default Login Credentials

| Role | Username / ID Number | Password | Portal Access |
| :--- | :--- | :--- | :--- |
| **Administrator** | `Drew` | `admin` | Full CRUD Admin Dashboard (`/admin`) |
| **Faculty Member** | `FAC-2024-001` | `password123` | Can Add Research Papers |
| **Student** | `2024-10045` | `password123` | Portal Student Account |
