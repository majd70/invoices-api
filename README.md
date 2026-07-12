# Laravel Invoices API

A simple REST API to manage **Customers** and **Invoices**, built with **Laravel 12** and secured with **Laravel Sanctum** token authentication.

This project was built as a practical engineering test task. It focuses on clean, layered architecture, solid validation, correct database relationships, consistent JSON responses, and real automated tests.

---

## Features

- **Token authentication** with Laravel Sanctum (login / me / logout).
- **Customers CRUD** — list (with search + pagination), show, create, update, delete.
- **Invoices CRUD** — list, show, create, update, delete, plus "invoices of a specific customer".
- **Automatic total** on invoices: `total = subtotal + tax - discount`.
- **Form Request validation** with clear JSON error messages.
- **Consistent JSON envelope** for every response: `{ status, message, data }`.
- **Layered architecture**: Controller → Service → Repository (interface-bound).
- **API Resources** for output shaping.
- **Seeders** for demo data + a ready-to-use login account.
- **Feature tests** (18 tests) covering auth, customers, invoices, and validation.
- **Docker** setup and a **Postman collection** included.

---

## Tech Stack

| Component        | Version            |
| ---------------- | ------------------ |
| PHP              | 8.2+               |
| Laravel          | 12.x               |
| Auth             | Laravel Sanctum    |
| Database         | SQLite (default) / MySQL |
| Tests            | PHPUnit            |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/Api/    # Thin controllers (Auth, Customer, Invoice)
│   ├── Requests/           # Form Request validation
│   └── Resources/          # API Resources (output shaping)
├── Models/                 # Customer, Invoice (relationships + total logic)
├── Repositories/           # Repository pattern (contracts + implementations)
├── Services/               # Business logic layer
├── Support/                # ApiResponse helper (JSON envelope)
└── Providers/              # RepositoryServiceProvider (interface bindings)
```

**Request flow:** `Route → FormRequest (validation) → Controller → Service (business logic) → Repository (data access) → Model` and back through an **API Resource** wrapped in the **ApiResponse** envelope.

---

## Getting Started

### Requirements

- PHP >= 8.2 with `pdo_sqlite` (default) or `pdo_mysql`
- Composer 2.x

### 1. Install dependencies

```bash
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

The project uses **SQLite by default** (zero configuration). The `composer install`
step already creates `database/database.sqlite`. If it's missing, create it:

```bash
# macOS / Linux
touch database/database.sqlite
# Windows (PowerShell)
New-Item database/database.sqlite -ItemType File
```

> **Prefer MySQL?** See [Using MySQL](#using-mysql-optional) below.

### 3. Run migrations and seeders

```bash
php artisan migrate --seed
```

This creates the tables and seeds:

- A login account → **email:** `admin@example.com`, **password:** `password`
- 10 sample customers, each with a few invoices.

### 4. Serve the app

```bash
php artisan serve
```

The API is now available at `http://localhost:8000/api`.

---

## Running with Docker

A `Dockerfile` and `docker-compose.yml` (app + MySQL) are included:

```bash
docker compose up --build
```

The container automatically runs migrations + seeders and serves the API at
`http://localhost:8000/api`.

---

## Running Tests

Tests run against an in-memory SQLite database (no setup required):

```bash
php artisan test
```

Expected: **18 passing tests** covering:

- Login success / invalid credentials
- Access to protected routes **without a token** (401)
- Creating a customer + validation errors (missing name, duplicate phone, invalid email)
- Creating an invoice + total calculation
- Rejecting invoices for a non-existent customer
- Rejecting negative amounts
- Listing a customer's invoices, pagination & search

---

## Authentication

1. **Login** to obtain a token:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

Response:

```json
{
  "status": true,
  "message": "Logged in successfully",
  "data": {
    "user": { "id": 1, "name": "Admin User", "email": "admin@example.com" },
    "token": "1|xxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

2. Send the token on every protected request:

```
Authorization: Bearer {token}
```

---

## API Endpoints

Base URL: `http://localhost:8000/api`

| Method | Endpoint                          | Description                              | Auth |
| ------ | --------------------------------- | ---------------------------------------- | ---- |
| POST   | `/login`                          | Login, returns a Sanctum token           | ❌   |
| GET    | `/me`                             | Current authenticated user               | ✅   |
| POST   | `/logout`                         | Revoke current token                     | ✅   |
| GET    | `/customers`                      | List customers (`?search=`, `?per_page=`, `?page=`) | ✅ |
| POST   | `/customers`                      | Create a customer                        | ✅   |
| GET    | `/customers/{id}`                 | Show a customer                          | ✅   |
| PUT    | `/customers/{id}`                 | Update a customer                        | ✅   |
| DELETE | `/customers/{id}`                 | Delete a customer                        | ✅   |
| GET    | `/customers/{id}/invoices`        | List a customer's invoices               | ✅   |
| GET    | `/invoices`                       | List invoices (`?customer_id=`, `?per_page=`) | ✅ |
| POST   | `/invoices`                       | Create an invoice                        | ✅   |
| GET    | `/invoices/{id}`                  | Show an invoice                          | ✅   |
| PUT    | `/invoices/{id}`                  | Update an invoice                        | ✅   |
| DELETE | `/invoices/{id}`                  | Delete an invoice                        | ✅   |

### Example — Create a customer

```bash
curl -X POST http://localhost:8000/api/customers \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"name":"Acme Corp","phone":"0599123456","email":"acme@example.com","address":"Gaza"}'
```

```json
{
  "status": true,
  "message": "Customer created successfully",
  "data": {
    "id": 11,
    "name": "Acme Corp",
    "phone": "0599123456",
    "email": "acme@example.com",
    "address": "Gaza",
    "invoices_count": 0
  }
}
```

### Example — Create an invoice

```bash
curl -X POST http://localhost:8000/api/invoices \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"customer_id":11,"invoice_number":"INV-1001","invoice_date":"2026-01-15","subtotal":1000,"tax":150,"discount":50}'
```

```json
{
  "status": true,
  "message": "Invoice created successfully",
  "data": {
    "id": 31,
    "customer_id": 11,
    "invoice_number": "INV-1001",
    "invoice_date": "2026-01-15",
    "subtotal": 1000,
    "tax": 150,
    "discount": 50,
    "total": 1100
  }
}
```

### Example — Validation error

```json
{
  "status": false,
  "message": "The given data was invalid.",
  "errors": {
    "phone": ["The phone field is required."],
    "subtotal": ["The subtotal field must be at least 0."]
  }
}
```

---

## Validation Rules

**Customer**

| Field   | Rules                          |
| ------- | ------------------------------ |
| name    | required, string, max:255      |
| phone   | required, unique, max:30       |
| email   | optional, valid email          |
| address | optional, string, max:500      |

**Invoice**

| Field          | Rules                                         |
| -------------- | --------------------------------------------- |
| customer_id    | required, must exist in `customers`           |
| invoice_number | required, unique, max:50                       |
| invoice_date   | required, valid date                          |
| subtotal       | required, numeric, **min:0**                  |
| tax            | required, numeric, **min:0**                  |
| discount       | optional, numeric, **min:0**                  |
| total          | derived automatically (`subtotal + tax - discount`) |

---

## Postman

Import `postman/Invoices-API.postman_collection.json`.

Run **Auth → Login** first; the returned token is stored automatically into the
`token` collection variable and reused by every other request.

---

## Using MySQL (optional)

1. Create a database (e.g. `invoices`).
2. Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoices
DB_USERNAME=root
DB_PASSWORD=
```

3. Run `php artisan migrate --seed`.

---

## Notes

- Every API failure (validation, unauthenticated, not found, server error) returns
  the same JSON envelope with `status: false`, so clients can handle errors uniformly.
- Repository interfaces are bound to their implementations in
  `App\Providers\RepositoryServiceProvider`, making the data layer easy to swap or mock.
