# Task Management Laravel API

Primary REST API for the **Task Management & Analytics Platform**. Built with **Laravel 11**, this service owns authentication, authorization, business logic, and the database layer. It integrates with the companion [Node.js services](https://github.com/your-username/task-management-node-services) for notifications, analytics, exports, and scheduled jobs.

---

## Table of Contents

- [Live URLs](#live-urls)
- [Production note (Render)](#production-note-render)
- [Features](#features)
- [Prerequisites](#prerequisites)
- [Quick Start (Local)](#quick-start-local)
- [Environment Variables](#environment-variables)
- [Test Credentials](#test-credentials)
- [Database](#database)
- [Authentication](#authentication)
- [Authorization (RBAC)](#authorization-rbac)
- [API Reference](#api-reference)
- [Status Transition Rules](#status-transition-rules)
- [Inter-Service Communication](#inter-service-communication)
- [Response Format](#response-format)
- [Running Tests](#running-tests)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)

---

## Live URLs

| Service | URL |
|---------|-----|
| **Laravel API (this repo)** | [https://task-management-laravel-api-u2v9.onrender.com/api](https://task-management-laravel-api-u2v9.onrender.com/api) |
| **Node.js Services** | [https://task-management-node-services-g5ie.onrender.com](https://task-management-node-services-g5ie.onrender.com) |
| **Health check** | [GET /up](https://task-management-laravel-api-u2v9.onrender.com/up) |

---

## Production note (Render)

> **Important:** On the current Render deployment, **SMTP mail is disabled** on both services, and **cron jobs are disabled** on the Node.js service.

| Feature | Render status | Local dev |
|---------|---------------|-----------|
| Laravel mail (`MAIL_MAILER`) | **`log`** — emails written to logs, not sent | `log` by default; set `smtp` to send via Laravel |
| Node.js SMTP (Nodemailer) | **Disabled** — no Gmail credentials configured | Set `EMAIL_USER` / `EMAIL_PASS` in Node `.env` |
| Node.js cron jobs | **Disabled** (`CRON_ENABLED=false`) | Enabled with `CRON_ENABLED=true` |

**Impact on Render**

- Laravel still calls the Node.js notification endpoint when tasks are assigned or updated; Node returns `202 Accepted` and queues the job.
- No emails are actually delivered because SMTP is not configured on Node.js.
- Scheduled jobs (daily digest, deadline reminders, task cleanup) do not run.

All notification and scheduler **logic is implemented and testable locally**. See the [Node.js README](https://github.com/your-username/task-management-node-services#production-note-render) for re-enable steps.

---

## Features

- JWT authentication (`php-open-source-saver/jwt-auth`)
- Role-based access control (Admin, Manager, Team Member)
- RESTful endpoints with Form Request validation
- Eloquent relationships with eager loading
- Database transactions for critical operations
- Soft-delete support for archived tasks
- Async notification dispatch to Node.js on task assign / status change
- Internal service endpoints for Node.js scheduler and notification workers

---

## Prerequisites

| Tool | Version |
|------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL or PostgreSQL | 8.x / 14+ |
| Node.js *(for companion service)* | 20+ |

Optional: [Node.js services repo](https://github.com/your-username/task-management-node-services) running locally on port `3000`.

---

## Quick Start (Local)

### 1. Clone and install

```bash
git clone https://github.com/your-username/task-management-laravel-api.git
cd task-management-laravel-api
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Edit `.env` with your database credentials and shared secrets (see [Environment Variables](#environment-variables)).

### 3. Create the database

Create an empty database (example for MySQL):

```sql
CREATE DATABASE task_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

This creates sample users, three teams (Engineering, Marketing, Sales), and demo tasks.

### 5. Start the API server

```bash
php artisan serve
```

The API is available at **http://localhost:8000/api**.

### 6. Start the Node.js companion service

In a separate terminal, from the Node.js repository:

```bash
npm install
cp .env.example .env   # set JWT_SECRET and INTERNAL_SERVICE_KEY to match Laravel
npm run dev
```

Both services must share the same `JWT_SECRET` and `INTERNAL_SERVICE_KEY`.

### 7. Verify

```bash
# Health check
curl http://localhost:8000/up

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password123"}'
```

---

## Environment Variables

| Variable | Required | Description |
|----------|----------|-------------|
| `APP_KEY` | Yes | Laravel application key (`php artisan key:generate`) |
| `APP_URL` | Yes | Public URL of this API (e.g. `http://localhost:8000`) |
| `DB_CONNECTION` | Yes | `mysql` or `pgsql` |
| `DB_HOST` | Yes | Database host |
| `DB_PORT` | Yes | Database port |
| `DB_DATABASE` | Yes | Database name |
| `DB_USERNAME` | Yes | Database user |
| `DB_PASSWORD` | Yes | Database password |
| `DATABASE_URL` | No | Full connection URL (Render/Heroku style) |
| `JWT_SECRET` | Yes | Shared JWT signing secret (`php artisan jwt:secret`) |
| `NODE_SERVICE_URL` | Yes | Base URL of Node.js service (e.g. `http://localhost:3000`) |
| `INTERNAL_SERVICE_KEY` | Yes | Shared secret for Laravel ↔ Node internal calls |

See [`.env.example`](.env.example) for the full list including mail, cache, and queue settings.

---

## Test Credentials

Seeded by `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@test.com` | `password123` |
| Manager | `manager@test.com` | `password123` |
| Team Member | `member@test.com` | `password123` |

Additional seeded team members use `@mailinator.com` addresses with the same password.

---

## Database

### Supabase PostgreSQL (production)

Use these variables in `.env` when connecting to a hosted Supabase database. Copy the values from your Supabase project dashboard (Settings → Database) — do not commit real credentials to the repo.

```env
DB_CONNECTION=pgsql
DB_URL="postgresql://<username>:<password>@<host>:5432/<database>?sslmode=require"
DB_HOST=<your-supabase-host>
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=<your-supabase-username>
DB_PASSWORD=<your-supabase-password>
DB_SSLMODE=require
```

For local development, use MySQL (or another local database) instead — see [Quick Start (Local)](#quick-start-local).

### Schema overview

| Table | Purpose |
|-------|---------|
| `users` | Accounts with `role` and `is_active` flag |
| `teams` | Team records with `created_by` owner |
| `team_members` | Pivot: user ↔ team with `member` / `lead` role |
| `tasks` | Tasks linked to team, assignee, and creator |
| `cache`, `jobs` | Laravel framework tables |

### Migrations

```bash
# Run all pending migrations
php artisan migrate

# Fresh install (drops all tables)
php artisan migrate:fresh --seed
```

### Seeders

| Seeder | Contents |
|--------|----------|
| `UserSeeder` | Admin, Manager, Team Member + additional team members |
| `TeamSeeder` | Engineering (4), Marketing (3), Sales (2) |
| `TaskSeeder` | Sample tasks on the Engineering team |

---

## Authentication

All protected routes require a JWT in the `Authorization` header:

```
Authorization: Bearer <access_token>
```

### Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/auth/register` | Public | Register a new user |
| `POST` | `/api/auth/login` | Public | Login and receive JWT |
| `GET` | `/api/auth/me` | JWT | Current authenticated user |
| `POST` | `/api/auth/logout` | JWT | Invalidate current token |

### Login example

**Request**

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@test.com",
  "password": "password123"
}
```

**Response `200`**

```json
{
  "status": "ok",
  "message": "Login successful.",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@test.com",
      "role": "admin",
      "is_active": true
    }
  }
}
```

Inactive users receive `403`. Invalid credentials receive `401`.

---

## Authorization (RBAC)

| Permission | Admin | Manager | Team Member |
|------------|:-----:|:-------:|:-----------:|
| Manage users (CRUD, toggle status) | ✅ | ✅ * | ❌ |
| Create users with any role | ✅ | ❌ | ❌ |
| Create team members only | — | ✅ | ❌ |
| List / create teams | ✅ | ✅ | ❌ |
| Manage team members | ✅ | ✅ ** | ❌ |
| Create / edit / delete team tasks | ✅ | ✅ ** | ❌ |
| View team tasks | ✅ | ✅ ** | Own only |
| Edit / complete own assigned tasks | ✅ | ✅ | ✅ |
| Delete tasks | Creator or Admin | Creator or Admin | ❌ |
| View analytics (via Node.js) | ✅ | ✅ ** | ❌ |

\* Managers can create users but only with the `team_member` role.  
\*\* Scoped to teams the manager belongs to.

Middleware stack on protected routes: `auth:api` → `active` → `role:...` (where applicable).

---

## API Reference

Base URL: `/api`

### Users *(Admin & Manager)*

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/users` | List users (paginated) |
| `POST` | `/users` | Create user |
| `GET` | `/users/{id}` | Get user details |
| `PATCH` | `/users/{id}` | Update name, email, role |
| `PATCH` | `/users/{id}/status` | Toggle active / inactive |

**Query params for `GET /users`:** `role`, `status` (`active` \| `inactive`), `per_page` (1–100, default 15)

**Create user body:**

```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "role": "team_member"
}
```

Users are never hard-deleted; use `PATCH /users/{id}/status` to deactivate.

---

### Teams

| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| `GET` | `/teams` | Admin, Manager | List teams (paginated) |
| `POST` | `/teams` | Admin, Manager | Create team |
| `GET` | `/teams/{id}` | Member+ | Team details with members |
| `POST` | `/teams/{id}/members` | Admin, Manager (team) | Add member |
| `DELETE` | `/teams/{id}/members/{user_id}` | Admin, Manager (team) | Remove member |

**Create team body:**

```json
{
  "name": "Product"
}
```

**Add member body:**

```json
{
  "user_id": 5,
  "role": "member"
}
```

Team member roles: `member`, `lead`.

---

### Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/teams/{team_id}/tasks` | List team tasks (filtered, paginated) |
| `POST` | `/teams/{team_id}/tasks` | Create task |
| `GET` | `/tasks/mine` | List tasks assigned to current user |
| `GET` | `/tasks/{id}` | Task details |
| `PATCH` | `/tasks/{id}` | Update task fields |
| `DELETE` | `/tasks/{id}` | Delete task (creator or admin) |
| `PATCH` | `/tasks/{id}/status` | Update status with transition validation |

**Query params for `GET /teams/{team_id}/tasks`:** `status`, `priority`, `assigned_to`, `per_page`

**Create task body:**

```json
{
  "title": "Implement feature X",
  "description": "Optional details",
  "priority": "high",
  "assigned_to": 3,
  "due_date": "2026-07-01T00:00:00.000Z"
}
```

**Status values:** `pending`, `in_progress`, `completed`, `cancelled`  
**Priority values:** `low`, `medium`, `high`

**Update status body:**

```json
{
  "status": "in_progress"
}
```

Invalid transitions return `422`.

---

### Internal endpoints *(Node.js only)*

Protected by `X-Service-Key` header matching `INTERNAL_SERVICE_KEY`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/internal/notifications/{task}/{user}` | Notification payload for email worker |
| `GET` | `/api/internal/scheduler/daily-digest` | Incomplete tasks grouped by user |
| `GET` | `/api/internal/scheduler/deadline-reminders` | Tasks due within 24 hours |
| `GET` | `/api/internal/scheduler/stale-cancelled-tasks` | Cancelled tasks older than 30 days |
| `DELETE` | `/api/tasks/{id}/archive` | Soft-delete stale cancelled task |

---

## Status Transition Rules

| Current status | Allowed next states |
|----------------|---------------------|
| `pending` | `in_progress`, `cancelled` |
| `in_progress` | `completed`, `pending` |
| `completed` | *(final — no transitions)* |
| `cancelled` | *(final — no transitions)* |

Enforced in the API; invalid transitions return `422 Unprocessable Entity`.

---

## Inter-Service Communication

When a task is **assigned** or its **status changes**, Laravel calls the Node.js notification service:

```http
POST {NODE_SERVICE_URL}/api/notifications/send
X-Service-Key: {INTERNAL_SERVICE_KEY}
Content-Type: application/json

{
  "task_id": 1,
  "user_id": 3,
  "event_type": "assigned",
  "details": {}
}
```

Node.js responds immediately (`202 Accepted`) and processes the email asynchronously. Failures are logged in Laravel but do not block the API response.

If `NODE_SERVICE_URL` or `INTERNAL_SERVICE_KEY` is missing, notifications are skipped with a warning log.

---

## Response Format

### Success

```json
{
  "status": "ok",
  "message": "Human-readable message.",
  "data": { }
}
```

### Paginated list

```json
{
  "status": "ok",
  "message": "Tasks retrieved successfully.",
  "data": {
    "tasks": [ ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42
    }
  }
}
```

### Error

```json
{
  "status": "error",
  "message": "Description of the problem.",
  "errors": { "field": ["Validation message"] }
}
```

| Code | Meaning |
|------|---------|
| `401` | Missing or invalid JWT |
| `403` | Authenticated but not authorized |
| `404` | Resource not found |
| `422` | Validation or business rule failure |
| `500` | Unexpected server error |

---

## Running Tests

```bash
# Run PHPUnit test suite
php artisan test

# Or directly
./vendor/bin/phpunit
```

---

## Deployment

**Recommended platform:** [Render](https://render.com) — free tier supports Docker-based PHP deployments with managed PostgreSQL, automatic HTTPS, and straightforward environment variable management.

### Why Render

- Native Docker support (this project includes a `Dockerfile`)
- Managed PostgreSQL with internal networking
- Automatic deploys from GitHub
- Built-in health checks at `/up`

### Deploy steps (Render)

1. Create a **PostgreSQL** database on Render.
2. Create a **Web Service** connected to this GitHub repo.
3. Set environment variables from [`.env.example`](.env.example):
   - `DB_CONNECTION=pgsql`
   - `DATABASE_URL=<internal database URL>`
   - `APP_KEY`, `JWT_SECRET`, `INTERNAL_SERVICE_KEY`
   - `NODE_SERVICE_URL=https://task-management-node-services-g5ie.onrender.com`
   - `APP_ENV=production`, `APP_DEBUG=false`
   - `MAIL_MAILER=log` *(current Render config — SMTP not used)*
4. Deploy — the startup script runs `composer install`, caches config/routes, and executes `php artisan migrate --force`.

> **Note:** The companion Node.js service on Render has cron and SMTP disabled. Task notification HTTP calls succeed, but emails and scheduled jobs do not run until those features are re-enabled on Node.js.
5. Seed production data (one-time):

   ```bash
   php artisan db:seed --force
   ```

6. Confirm [Live URLs](#live-urls) match your deployed endpoints.

### Production checklist

- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_KEY`, `JWT_SECRET`, and `INTERNAL_SERVICE_KEY`
- [ ] Migrations run successfully
- [ ] Seed data loaded
- [ ] Node.js service deployed and reachable
- [ ] CORS / frontend URL configured on Node.js side

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `401 Unauthorized` on all routes | Verify JWT is sent as `Bearer <token>`; check `JWT_SECRET` matches Node.js |
| Notifications not sent (local) | Confirm Node.js is running; check `NODE_SERVICE_URL`, `INTERNAL_SERVICE_KEY`, and Node `EMAIL_*` vars |
| Notifications not sent (Render) | Expected if Node SMTP is disabled; Laravel still queues the HTTP call — see [Production note (Render)](#production-note-render) |
| `SQLSTATE` connection errors | Verify `DB_*` vars; ensure database exists and is reachable |
| `500` after deploy | Check `storage/` and `bootstrap/cache/` are writable; run `php artisan config:clear` |
| Migration failures | Run `php artisan migrate:fresh --seed` on a fresh database (dev only) |

Logs: `storage/logs/laravel.log` (local) or platform log stream (production).

---

## License

MIT
