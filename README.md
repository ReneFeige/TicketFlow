# TicketFlow

TicketFlow is a full-stack helpdesk ticket system developed as a portfolio project.

The goal is to build a maintainable web application with authentication, role-based access control, ticket management, and a REST API.

**Project status:** Initial development setup completed. Ticket management and authentication are not implemented yet.

## Tech Stack

- **Frontend:** React, TypeScript, Vite
- **Backend:** PHP 8.4, Laravel 13
- **Database:** PostgreSQL 17
- **Development environment:** Docker Compose
- **Version control:** Git and GitHub

## Getting Started

### Prerequisites

- Docker with Docker Compose
- Git

### 1. Clone the repository

```bash
git clone <repository-url>
cd TicketFlow
```

### 2. Configure environment variables

Create the Docker Compose environment file:

```bash
cp .env.example .env
```

Create the Laravel environment file:

```bash
cp backend/.env.example backend/.env
```

Both environment files must use the same PostgreSQL database name, username, and password.

In the root `.env` file, configure the Docker Compose database credentials:

```dotenv
DB_DATABASE=ticketflow
DB_USERNAME=ticketflow
DB_PASSWORD=your_local_password
```

In `backend/.env`, configure the Laravel database connection:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ticketflow
DB_USERNAME=ticketflow
DB_PASSWORD=your_local_password
```

The hostname `db` refers to the PostgreSQL service defined in `compose.yaml`. Docker Compose allows containers to communicate using their service names.

**Note:** The `.env` files contain local configuration and credentials and are excluded from Git. Do not commit real passwords or other secrets.

### 3. Start the database

```bash
docker compose up -d db
```

### 4. Install backend dependencies

```bash
docker compose run --rm backend composer install
```

Generate the Laravel application key:

```bash
docker compose run --rm backend php artisan key:generate
```

### 5. Install frontend dependencies

```bash
docker compose run --rm \
  -e npm_config_cache=/tmp/.npm \
  frontend npm ci
```

`npm ci` installs the exact dependency versions defined in package-lock.json, ensuring a reproducible frontend setup.

### 6. Start the application

```bash
docker compose up -d
```

### 7. Run database migrations

```bash
docker compose exec backend php artisan migrate
```

## Local URLs

| Service | URL |
|---|---|
| React frontend | http://localhost:5173 |
| Laravel backend | http://localhost:8000 |
| API health endpoint | http://localhost:8000/api/health |

The application uses ports `5173` (React/Vite) and `8000` (Laravel). Both ports must be available on your local machine.

The development servers are bound to `127.0.0.1` on the host, so they are accessible locally rather than being exposed to the network.

The frontend uses a Vite development proxy to forward `/api` requests to Laravel through Docker's internal network.

## Development

Stop the application:

```bash
docker compose down
```

Check running containers:

```bash
docker compose ps
```

View backend logs:

```bash
docker compose logs -f backend
```

### Backend tests

Backend tests use a dedicated PostgreSQL database (`ticketflow_test`)
to keep test data separate from development data.

Create the test database once after starting the Docker containers:

```bash
docker compose exec db psql -U ticketflow -d ticketflow -c "CREATE DATABASE ticketflow_test;"
```

Run the migrations for the test database:

```bash
docker compose exec \
  -e APP_ENV=testing \
  -e DB_CONNECTION=pgsql \
  -e DB_DATABASE=ticketflow_test \
  backend php artisan migrate --force
```

Run all backend tests:

```bash
docker compose exec backend php artisan test
```

The PHPUnit configuration in `backend/phpunit.xml` uses
`ticketflow_test` as the test database.
Never run tests against the development database.

## Planned MVP

- Authentication
- Customer, Support Agent, and Admin roles
- Ticket creation and management
- Ticket status and priority
- Categories and comments
- Role-based authorization
- Basic dashboard
- Demo accounts and sample data

## Roadmap

Future improvements may include file attachments, notifications, a knowledge base, and AI-assisted ticket processing.

## Project Purpose

This project is currently developed for portfolio and learning purposes.