# Retail CRM

A monolithic Laravel application designed for retail customer relationship management, sales tracking, and employee performance monitoring. Built with a backend-first approach and a clean, event-driven architecture.

## Features

- **Authentication & Authorization**: Secure login and registration using Laravel Fortify and Sanctum, with role-based access control (Admin vs. Employee).
- **Product Management**: Manage products, track inventory, and assign SKUs and pricing.
- **Customer Management**: Register customers, maintain profiles, and assign specific employees for personalized re-engagement.
- **Sales & Transaction Tracking**: Record sales, attach multiple products as sale items, and automatically calculate subtotals and grand totals.
- **Employee KPI Tracking**: Event-driven architecture that listens to sales and automates KPI updates for employees based on their performance.
- **Lost Customer Re-engagement**: A dedicated system (including artisan commands and scheduled tasks) to detect inactive customers and automatically assign them to employees for re-engagement.
- **Automated Invoicing**: Sends styled HTML email invoices automatically upon a successful purchase using SMTP and decoupled event listeners.

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL or PostgreSQL (or SQLite for local testing)

## Setup Instructions

1. **Clone the repository**

    ```bash
    git clone https://github.com/rajubeparybd/retail-crm
    cd retail-crm
    ```

2. **Automated Setup**
   The project includes a convenient composer script that handles dependencies, environment creation, application key generation, migrations, seeding, and frontend asset building.

    ```bash
    composer run setup
    ```

    _Note: The setup script copies `.env.example` to `.env`. Ensure you update the `.env` file with your database credentials and SMTP settings (e.g., Mailtrap) if needed._

## Running the Application

To start the Laravel development server, Vite frontend server, and queue worker concurrently, run:

```bash
composer run dev
```

_By default, the application will be available at `http://localhost:8000`._

You can log in using the administrator account seeded by default:

- **Email:** admin@gmail.com
- **Password:** password

## API Routes

The following API endpoints are available:

- `POST /api/login` — Authenticate and retrieve a token.
- `GET /api/products` — Retrieve a list of products.
- `GET /api/user` — Retrieve the currently authenticated user details.

## Code Quality & Testing

The application includes composer scripts for linting and testing (using Pest PHP).

To format and lint your code:

```bash
composer run lint
```

To run the comprehensive feature test suite:

```bash
composer run test
```
