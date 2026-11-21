# Event RSVP API

A RESTful API built with **Laravel 12** and **PHP 8.4** for managing events, attendees, and RSVPs.

The project focuses on backend engineering challenges, including many-to-many relationships with pivot state, database integrity, transactional business logic, event capacity management, and preventing race conditions under concurrent requests.

## Tech Stack

- **Language:** PHP 8.4
- **Framework:** Laravel 12
- **Database:** PostgreSQL 17 (via Docker)
- **Testing:** Pest 4 (built on PHPUnit)
- **Containerization:** Docker & Docker Compose
- **Architecture:** Layered Architecture / Action-Service pattern

## Key Features

- Event CRUD operations.
- Attendee creation and listing.
- RSVP registration and cancellation.
- Many-to-many relationships through the RSVP model.
- Event capacity enforcement with race-condition prevention.
- Duplicate RSVP prevention through database constraints.
- Re-registration after RSVP cancellation.
- Automated unit, feature, and RSVP lifecycle tests.

## Architecture & Design Decisions

The application follows a pragmatic layered architecture, emphasizing separation of concerns,
maintainability, and loose coupling.

Controllers are kept thin, with business logic delegated to single-purpose Action classes (app/Actions). Dependency injection is used where appropriate to reduce coupling between components.

### Database Design & Concurrency

The application uses three primary entities:

| Entity | Relationship |
|---|---|
| Event | Has many RSVPs |
| Attendee | Has many RSVPs |
| RSVP | Belongs to one Event and one Attendee |

Events and attendees form a many-to-many relationship through the RSVP model, which maintains additional state such as registration status and cancellation information.

Key engineering decisions:

- **Concurrency Control:** `CreateRsvpAction` uses PostgreSQL transactions and pessimistic row-level locking (`lockForUpdate()`) on the Event record to prevent overbooking.
- **Database Integrity:** A composite unique constraint on (`event_id`, `attendee_id`) prevents duplicate RSVP records. Foreign keys enforce referential integrity with `ON DELETE CASCADE`.
- **Idempotency:** Cancelling an RSVP returns `204 No Content`, even when the RSVP is already canceled.
- **Re-registration:** Previously canceled RSVPs are reactivated instead of creating duplicate records.
- **Capacity Management:** Only confirmed RSVPs count towards event capacity.

## Getting Started

### Prerequisites

- PHP 8.4
- Composer
- Docker & Docker Compose
- Git

### Installation

**1. Clone the repository and install dependencies:**

```bash
git clone <your-repo-url>
cd event-meetup-RSVP
composer install
```

**2. Configure the environment:**

```bash
cp .env.example .env
php artisan key:generate
```

Configure the PostgreSQL connection in `.env` to match the Docker Compose configuration:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your_local_password
```

**3. Start PostgreSQL:**

```bash
docker compose up -d db
```

**4. Run migrations and seeders:**

```bash
php artisan migrate --seed
```

The development seeder populates the database with 10 randomly generated attendees for testing RSVP workflows.

**5. Start the Laravel server:**

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## Testing

The project uses **Pest 4** and follows the testing pyramid.

- **Unit Tests:** Verify isolated business logic, enums, and domain exceptions without database access.
- **Feature Tests:** Verify API endpoints, validation, database constraints, relationships, and cascading deletes.
- **RSVP Lifecycle Test:** Simulates creating an event, filling its capacity, rejecting additional registrations, cancelling an RSVP, and successfully registering another attendee.

### Testing Database Setup

Tests use a separate PostgreSQL database named `laravel_testing`, configured in `phpunit.xml`, to ensure development data remains isolated.

Create the testing database once after initializing the Docker database volume:

```bash
docker compose exec db psql -U laravel -d postgres -c "CREATE DATABASE laravel_testing;"
```

**Run the complete test suite:**

```bash
php artisan test
```

Alternatively:

```bash
vendor/bin/pest
```

**Note:** Automated tests must always use `laravel_testing`, never the development database.

## API Documentation

All endpoints use the `/api` prefix.

### Events

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/events` | List events (paginated) |
| POST | `/api/events` | Create an event |
| GET | `/api/events/{id}` | Retrieve an event |
| PATCH | `/api/events/{id}` | Update an event |
| DELETE | `/api/events/{id}` | Delete an event |

### Attendees

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/attendees` | List attendees (paginated) |
| POST | `/api/attendees` | Create an attendee |
| GET | `/api/attendees/{id}` | Retrieve an attendee |

### RSVPs

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/events/{event}/rsvps` | List confirmed attendees |
| POST | `/api/events/{event}/rsvps` | Register an attendee |
| DELETE | `/api/events/{event}/rsvps/{rsvp}` | Cancel an RSVP |

### HTTP Status Codes

| Status | Meaning |
|---|---|
| `200` | Successful retrieval or update |
| `201` | Resource created |
| `204` | Successful deletion or cancellation |
| `404` | Resource not found |
| `409` | Business rule conflict |
| `422` | Validation error |
