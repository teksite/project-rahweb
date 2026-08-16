# RAHWEB

RAHWEB is a modular Laravel-based project developed for testing and demonstrating a ticketing workflow, user management, administration panel, notifications, and integration with external APIs.

This project is currently intended for local development and testing purposes and should not be considered production-ready.

---

# Requirements

The following software and versions are recommended for running the project:

- PHP 8.4
- Composer 2.8.12
- Node.js v24.15.0
- pnpm 10.12.4
- MariaDB 8.4
- MySQL or PostgreSQL can also be used
- Laragon is recommended for local development

Laragon is recommended because it provides an easy way to create a local web server, virtual hosts/domains, PHP environments, databases, and development tools.

---

# Installation

## 1. Clone or Download the Project

Clone the project from the GitHub repository using the `main` branch:

    git clone -b main https://github.com/teksite/project-rahweb.git

Then enter the project directory:

    cd project-rahweb

Alternatively, you can download the ZIP file of the `main` branch from the GitHub repository and extract it.

---

## 2. Install PHP Dependencies

Run the following command in the root directory of the project:

    composer install

This command installs all PHP and Laravel dependencies required by the project.

---

## 3. Install Node.js Dependencies

Run:

    pnpm install

This installs the frontend dependencies used by Vite, Alpine.js, Tailwind CSS, and other frontend packages.

---

# Environment Configuration

## 4. Create the .env File

Copy the `.env.example` file and rename the copy to `.env`.

The `.env` file must be located in the root directory of the project.

For example:

    cp .env.example .env

On Windows, you can simply copy the `.env.example` file manually and rename it to:

    .env

---

## 5. Generate the Application Key

Run:

    php artisan key:generate

This generates the Laravel application encryption key required by the application.

---

# Database Configuration

## 6. Create a Database

Create a new database using one of the supported database systems:

- MariaDB
- MySQL
- PostgreSQL

For example, you can create a database named:

    rahweb

After creating the database, open the `.env` file and configure the database connection.

Example:

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=rahweb
    DB_USERNAME=root
    DB_PASSWORD=

If you are using PostgreSQL, configure the corresponding PostgreSQL connection values.

Make sure that the database name in `.env` matches the database you created.

---

# Email Configuration

The project uses email notifications in several parts of the ticketing workflow.

By default, you can use the Laravel `log` mail driver if you only want to inspect generated emails inside the application logs.

For example:

    MAIL_MAILER=log

If you want to actually receive emails, change the mail driver to SMTP and configure your SMTP server.

Example:

    MAIL_MAILER=smtp
    MAIL_HOST=your-smtp-host
    MAIL_PORT=587
    MAIL_USERNAME=your-username
    MAIL_PASSWORD=your-password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="example@example.com"
    MAIL_FROM_NAME="${APP_NAME}"

For local development, Mailpit is recommended.

Laragon already includes Mailpit in many installations, making it convenient for local email testing.

You can also use a sandbox email service such as Mailtrap.

Mailpit is particularly useful for local development because emails are captured locally instead of being delivered to real recipients.

Mailpit does not impose a sending limit for local development in the same way that hosted email sandbox services may impose plan-based limits.

---

# Reset and Seed the Application

## 7. Initialize the Database

After configuring the `.env` file and database, run:

    php artisan app:reset --seed

This command resets the application's database and executes the migrations belonging to the project modules in the required order.

The `--seed` option also runs the seeders and inserts the initial and required data, as well as development/test data provided by the project.

This command should be used when you want to start with a clean database.

WARNING:

This command resets the database data. Do not use it on a database containing data that you need to keep.

---

# Build Frontend Assets

## 8. Build the Frontend

Run:

    pnpm build

This command runs the Vite production build and generates the frontend assets required by the Laravel application.

The generated assets are then used by the application when running the project.

For development, you can instead use:

    pnpm dev

The development command starts Vite in development mode and watches frontend files for changes.

---

# Storage Link

## 9. Create the Storage Symbolic Link

Run:

    php artisan storage:link

This creates the symbolic link required by Laravel to make files stored in the `storage/app/public` directory accessible through the public web directory.

The command is required for displaying and accessing uploaded or predefined public files.

---

# Running the Project

After completing the installation steps, configure your local web server and point the domain/document root to the project.

Laragon is recommended for local development because it can automatically create a local domain for the project.

If you are not using Laragon, you can also use the Laravel development server:

    php artisan serve

The application will normally be available through:

    http://127.0.0.1:8000

If you are using Laragon, you can configure a local domain such as:

    rahweb.test

The exact domain depends on your local Laragon configuration.


---

# Running QUEUE

    php artisan queue:work

---
# Important Notes

## Development and Local Usage Only

This project is currently intended for local development, testing, and demonstration purposes.

It has not been fully hardened for production usage.

There may be security issues, incomplete workflows, missing validations, or other implementation problems that should be addressed before using the project in a production environment.

---

## CORS

CORS restrictions have intentionally been relaxed/disabled in the current version of the project to simplify local development and testing.

As a result, requests from different domains may be accepted and processed by the application.

This configuration should be reviewed and properly restricted before using the project in production.

---

# LAREON Architecture

This project is based on an early version of the LAREON CMS architecture.

LAREON is being developed as a modular Laravel-based CMS architecture.

The project uses modules to separate different parts of the application.

For working with modules, refer to:

    https://github.com/teksite/module

The module system is responsible for organizing application functionality into independent modules.

---

# Main Packages

The project uses several Teksite packages.

## Teksite Module

    https://github.com/teksite/module

Provides the modular architecture used by the project.

---

## Teksite ExtraLaravel

    https://github.com/teksite/extralaravel

Provides additional Laravel functionality used throughout the project.

---

## Teksite Authorize

    https://github.com/teksite/authorize

Provides authorization, roles, permissions, and related access-control functionality.

---

## Teksite Laravel Icon

    https://github.com/teksite/laravel-icon

Provides the icon functionality used by the application.

---

## Teksite Laravel File Manager

    https://github.com/teksite/laravel-filemanager

Provides file-manager functionality used by the project.

The file manager is currently not fully integrated with the Ticketing module.

Refer to the package repository for installation instructions and usage documentation.

---

# Project Usage

After running the application, two main areas are available:

- User panel
- Admin panel

Both areas require authentication.

The other sections of the application are outside the scope of this project and therefore are not described in detail here.

---

# User Panel

A default regular user is available for testing.

Email:

    user3@example.com

Password:

    user3@example.com

Regular users can access their dashboard through:

    /panel/dashboard

From the right-side menu, select:

    Tickets

The user can then create and manage their tickets.

---

# Admin Panel

The admin panel is available at:

    /tkadmin/dashboard

Administrators can log in using the predefined users described below.

---

# Level 1 Administrator

The first-level administrator can be accessed using:

Email:

    user1@example.com

Password:

    user1@example.com

This user represents the first administrator responsible for the initial ticket approval.

The first-level administrator reviews submitted tickets and can either approve or reject them.

If a ticket is rejected, the administrator can provide a review explaining the reason for rejection.

---

# Level 2 Administrator

The second-level administrator can be accessed using:

Email:

    user2@example.com

Password:

    user2@example.com

This user represents the final administrator responsible for the second stage of ticket approval.

The second-level administrator does not have access to tickets before they are approved by the first-level administrator.

Once the first-level administrator approves a ticket, it becomes available to the second-level administrator for final approval.

---

# Ticket Approval Workflow

The ticket workflow is implemented as a two-stage approval process.

The general workflow is:

    User
        |
        v
    Create Ticket
        |
        v
    Level 1 Administrator
        |
        +---- Reject ----> Ticket Rejected
        |
        v
      Approve
        |
        v
    Level 2 Administrator
        |
        +---- Reject ----> Ticket Rejected
        |
        v
      Approve
        |
        v
    External API Endpoint

Each administrator's approval or rejection is stored separately.

When an administrator rejects a ticket, a review can be stored with the approval record.

---

# API Integration

After both administrators approve the ticket, the ticket is sent to:

    /api/endpoint

This endpoint is currently a fake/test endpoint.

It randomly returns either:

    HTTP 200

or:

    HTTP 500

This is intentionally implemented to simulate successful and failed API requests.

This allows the project to demonstrate how the application handles external API responses and failures.

---

# API Request Logging

The result of sending the ticket to the external endpoint is stored in two places:

1. Application logs
2. Database

The database stores information related to the API request and its response.

This makes it possible to inspect the history and result of API communication.

The application logs can be accessed through the Settings section of the admin panel.

The stored ticket/API request information can also be viewed through the ticket-related sections of the admin panel.

---

# Email Notifications

Email notifications are sent to the ticket owner during the approval workflow.

When the ticket is approved or rejected by an administrator, the user can receive an email notification describing the result of the operation.

For local development, Mailpit is recommended so that these emails can be inspected without sending them to real email addresses.

---

---

# SUPERUSER

The CMS includes several administrator roles with access to different parts of the application.

The `administrator` role has full access to all parts of the application, including administrative features, logs, roles, and permissions.

A default user with the `administrator` role is already available in the seeded database.

## Administrator Account

Email:

    zb.sina@teksite.net

Password:

    zb.sina@teksite.net

You can use this account to access the administrator features of the CMS.

With this account, you can:

- Access application error logs.
- View other application log files.
- Manage roles.
- Manage permissions.
- Access administrative sections of the CMS.
- Access all parts of the application that are protected by administrator-level permissions.

This account is intended for development and testing purposes.

WARNING:

Do not use this default email/password combination in a production environment. The password should be changed immediately before using the application in any environment where the account could be accessed by unauthorized users.

---
# Development Notes

The project is currently a test and demonstration implementation.

The ticketing workflow is implemented to demonstrate:

- Ticket creation
- File attachment
- Administrator approval
- Administrator rejection
- Multi-level approval
- Review/comment on rejection
- Email notification
- External API communication
- API failure handling
- API response logging
- Database logging
- Role-based access

The workflow should be reviewed and redesigned before being used in a real production environment.

---

# Coding Architecture

The project uses several software design patterns and architectural approaches.

The main approaches used in this project include:

- DTO
- Result / Service Result
- Repository Pattern
- Business Logic Layer

Some of the packages and internal components also use additional patterns such as:

- Facade
- Strategy
- DTO
- Repository
- Service Layer
- Business Logic Layer

The exact implementation can differ between modules and packages.

---

# DTO and Result

The project uses DTOs to transfer structured data between different layers of the application.

Result objects are also used to provide a consistent way of returning operation results.

The general idea is to avoid returning different structures from different business operations and instead provide a predictable result structure.

---

# Repository Pattern

The Repository Pattern is used to separate data-access operations from other parts of the application.

This helps prevent controllers and other application layers from becoming tightly coupled to database queries.

Database-related operations can therefore be centralized and reused where appropriate.

---

# Business Logic Layer

Business logic is separated from controllers as much as possible.

Controllers are primarily responsible for handling HTTP requests and responses, while the business logic is placed in dedicated classes.

In the modular architecture, logic related to a specific model or module can be placed in dedicated Logic classes.

This makes controllers smaller and makes business operations easier to maintain and test.

---

# Known Limitations and Required Improvements

The following items are known limitations of the current version of the project.

## Role Labels

Role names/labels are currently used directly in parts of the application logic.

Changing the role names used by the project may therefore cause operational problems or application errors.

A future version should use immutable internal identifiers or stable role keys instead of relying directly on display labels.

---

## File Manager Integration

The File Manager is not currently fully integrated with the Ticketing module.

As a result, the file manager cannot currently be used as a complete attachment-management solution for ticket files.

A future implementation should connect the File Manager directly to the Ticketing module.

---

## WYSIWYG Editor

A WYSIWYG editor has not been implemented in the current version.

The architecture can support integrating a WYSIWYG editor and connecting it to the File Manager.

However, using a WYSIWYG editor is not recommended for the current version because the project is primarily intended for testing the ticketing workflow.

---

## File Manager Installation

The File Manager package requires its own installation and configuration.

If you need to use the File Manager, refer to its GitHub repository and installation documentation:

    https://github.com/teksite/laravel-filemanager

---

## Production Readiness

The project requires significant changes before it can be used in production.

The current ticketing scenario and business workflow are primarily implemented for testing and demonstration purposes.

A production implementation should define the following areas more precisely:

- Business rules
- Authorization rules
- Validation rules
- Error handling
- API retry strategy
- API idempotency
- Queue configuration
- Notification strategy
- File security
- File validation
- Authentication
- Email verification
- Phone verification
- Audit logging
- Rate limiting
- CORS policy
- CSRF policy
- Database indexing
- Database transactions
- Concurrency handling
- Monitoring
- Production logging
- Security hardening

---

# Email and Phone Verification

For development convenience, email and phone information is considered verified by default during registration.

There is currently no complete real-world verification workflow for:

- Email verification
- Phone number verification

A production implementation should add appropriate verification processes before allowing users to rely on these values as verified contact information.

---

# Login Pages

The login pages are currently manually implemented for the different user levels and roles.

A future production implementation should consider a more centralized authentication and authorization system where authentication is separated from role-specific access control.

---

# Vue.js

Vue.js is not currently used in this project.

The current frontend relies primarily on Laravel Blade, Alpine.js, Tailwind CSS, and Vite.

Vue.js can be introduced in the future if the application requires more complex client-side state management or highly interactive interfaces.

---

# Recommended Future Improvements

The following improvements are recommended for future versions:

- Replace role labels with immutable role keys/identifiers.
- Fully integrate the File Manager with the Ticketing module.
- Add a proper WYSIWYG editor.
- Implement real email verification.
- Implement real phone verification.
- Improve authentication architecture.
- Improve authorization policies.
- Improve API security.
- Restrict CORS to trusted origins.
- Add proper API authentication.
- Add API retry and backoff mechanisms.
- Add idempotency handling for external API requests.
- Improve database indexing.
- Add comprehensive automated tests.
- Add feature tests for the complete ticket workflow.
- Add unit tests for business logic.
- Improve validation and error handling.
- Improve audit logging.
- Add production queue configuration.
- Add monitoring and alerting.
- Improve file upload security.
- Add file type and file size validation.
- Add virus/malware scanning for uploaded files.
- Improve frontend architecture.
- Consider Vue.js for complex interactive components.
- Harden the application for production deployment.

---

# Package Documentation

The following repositories contain additional documentation and implementation details.

Teksite Module:

    https://github.com/teksite/module

Teksite ExtraLaravel:

    https://github.com/teksite/extralaravel

Teksite Authorize:

    https://github.com/teksite/authorize

Teksite Laravel Icon:

    https://github.com/teksite/laravel-icon

Teksite Laravel File Manager:

    https://github.com/teksite/laravel-filemanager

---

# Final Note

RAHWEB is currently a development and demonstration project.

The main purpose of the current version is to demonstrate a modular Laravel application and a multi-level ticket approval workflow.

The application should not be deployed directly to a production environment without reviewing and improving its security, authentication, authorization, validation, API integration, file management, email verification, logging, testing, and business rules.
