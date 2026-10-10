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

Set the same PostgreSQL credentials in both files.

In `backend/.env`, configure:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=ticketflow
DB_USERNAME=ticketflow
DB_PASSWORD=your_local_password
```

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
docker compose run --rm frontend npm install
```

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

The frontend uses a Vite development proxy to forward `/api` requests to Laravel.

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

## License

This project is currently developed for portfolio and learning purposes.