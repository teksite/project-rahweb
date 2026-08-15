# RAHWEB Project Structure

This document describes the architecture and internal structure of the RAHWEB project, with a focus on the Ticketing module and its business workflow.

The project is based on a modular Laravel architecture and uses the LAREON modular structure.

---

# High-Level Architecture

The application is organized into modules.

The Ticketing module is responsible for:

- Ticket creation
- Ticket management
- Ticket approval workflow
- Multi-level administrator approval
- Ticket status management
- API submission
- API request tracking
- Email/event integration
- Ticket visibility rules
- File attachment handling

The main architecture can be summarized as:

    HTTP Request
         |
         v
    Controller
         |
         v
    Logic / Action
         |
         +--------------------+
         |                    |
         v                    v
    Query Layer          Model Layer
         |                    |
         +----------+---------+
                    |
                    v
                Database

For asynchronous API communication:

    Ticket Approved
          |
          v
    TicketApprovalObserver
          |
          v
    UpdateTicketStatusEvent
          |
          v
    TicketToApiJob
          |
          v
    External API
          |
          v
    tickets_api_requests

---

# Main Project Structure

The project uses a modular structure.

The important directories related to the Ticketing module are:

    lareon/
    ├── modules/
    │   ├── Ticketing/
    │   │   ├── App/
    │   │   │   ├── Action/
    │   │   │   ├── Enums/
    │   │   │   ├── Events/
    │   │   │   ├── Jobs/
    │   │   │   ├── Logics/
    │   │   │   ├── Models/
    │   │   │   ├── Observers/
    │   │   │   ├── queries/
    │   │   │   └── ...
    │   │   ├── Database/
    │   │   │   ├── migrations/
    │   │   │   └── seeders/
    │   │   ├── Resources/
    │   │   ├── Routes/
    │   │   └── ...
    │   └── ...
    │
    └── ...

The exact structure may contain additional files depending on the version of the module.

---

# Ticketing Module

The Ticketing module is located under:

    lareon/modules/Ticketing/

Its application code is located under:

    lareon/modules/Ticketing/App/

The module is divided into several layers.

    App/
    ├── Action/
    ├── Enums/
    ├── Events/
    ├── Jobs/
    ├── Logics/
    ├── Models/
    ├── Observers/
    ├── queries/
    └── Services/

Each directory has a specific responsibility.

---

# Action

Directory:

    App/Action/

Actions contain operations that represent a specific business action.

The main action currently implemented is:

    TicketBulkAction

This class handles bulk ticket operations for administrators.

Supported actions are:

    review
    approve
    reject

The action determines the current user's role and executes the appropriate workflow.

---

# TicketBulkAction

Class:

    Lareon\Modules\Ticketing\App\Action\TicketBulkAction

This class is responsible for bulk processing tickets.

It supports two administrator roles:

    ticket manager
    chief ticket manager

The workflow is:

    Ticket Manager
         |
         +---- review
         |
         +---- approve
         |
         +---- reject
         |
         v
    Chief Ticket Manager
         |
         +---- review
         |
         +---- approve
         |
         +---- reject

---

# Ticket Manager

The `ticket manager` role represents the first-level ticket administrator.

When this administrator performs `review`, tickets that:

- Have no previous approval
- Have not been sent to the API

can be assigned an approval record.

The created approval initially has:

    status = IN_REVIEW

The administrator can then approve or reject the approval.

---

# Chief Ticket Manager

The `chief ticket manager` role represents the second-level ticket administrator.

This administrator can only review tickets after the first-level administrator has approved them.

The required condition is:

    ticket manager approval = APPROVED

The ticket must also not already have an approval belonging to the chief ticket manager.

This creates the second stage of the approval workflow.

---

# Bulk Processing

Bulk operations are processed using:

    chunkById()

The chunk size is currently:

    500

This prevents the application from loading a very large number of tickets into memory at once.

For example:

    500 tickets
        |
        v
    Process
        |
        v
    Next 500
        |
        v
    Process
        |
        v
    ...

This approach is useful when a large number of tickets are processed simultaneously.

---

# Enums

Directory:

    App/Enums/

The module uses PHP Enums to represent fixed application states.

Currently there are two important enums:

    TicketStatusEnum
    ApiStatusEnum

---

# TicketStatusEnum

Class:

    Lareon\Modules\Ticketing\App\Enums\TicketStatusEnum

Available statuses:

    IN_REVIEW = 0
    APPROVED  = 1
    REJECTED  = 2
    PENDING   = 4

## PENDING

Value:

    4

The ticket has not entered the approval workflow yet.

In the current implementation, a ticket with no approval records is considered `PENDING`.

---

## IN_REVIEW

Value:

    0

The ticket is currently waiting for an administrator's decision.

---

## APPROVED

Value:

    1

The ticket has received the required approvals.

The current implementation considers a ticket approved when it has at least two approved approval records.

---

## REJECTED

Value:

    2

At least one approval associated with the ticket has been rejected.

A rejected approval takes priority over the approved state.

---

# Ticket Status Resolution

Ticket status is not stored directly in the `tickets` table.

Instead, it is calculated dynamically from the approval records.

The current logic is:

    No approvals
        |
        v
    PENDING

    Any rejected approval
        |
        v
    REJECTED

    At least two approved approvals
        |
        v
    APPROVED

    Otherwise
        |
        v
    IN_REVIEW

Therefore:

    REJECTED
        has higher priority than
    APPROVED

when determining the current ticket status.

---

# ApiStatusEnum

Class:

    Lareon\Modules\Ticketing\App\Enums\ApiStatusEnum

The enum represents API communication states.

Current values:

    NOT_SENT       = 0
    SENDING        = 1
    SENT           = 2
    Retry_SENDING  = 3
    API_FAILED     = 4

The current `TicketToApiJob` uses string values such as:

    pending
    processing
    success
    failed

for the `tickets_api_requests.status` column.

Therefore, the enum and the Job currently use different representations.

This should be unified in a future refactoring.

---

# Models

Directory:

    App/Models/

The main models involved in the Ticketing module are:

    Ticket
    TicketApproval
    TicketApi

---

# Ticket Model

Class:

    Lareon\Modules\Ticketing\App\Models\Ticket

The `Ticket` model represents a ticket submitted by a user.

Main fields:

    id
    user_id
    title
    body
    file
    created_at
    updated_at

---

# Ticket Relationships

The Ticket model has the following important relationships.

## Creator

    creator()

Relationship:

    belongsTo(User::class)

The creator is the user who submitted the ticket.

---

## Approvals

    approvals()

Relationship:

    hasMany(TicketApproval::class)

A ticket can have multiple approval records.

Each approval represents an administrator's decision for a particular role.

---

## API Requests

    apiRequests()

Relationship:

    hasMany(TicketApi::class)

A ticket can have one or more API request records.

These records contain the history and result of communication with the external API.

---

# Ticket Approval

The `TicketApproval` model represents an administrator's approval decision.

An approval contains information about:

    ticket
    administrator
    role
    round
    status
    review

The relationship can be understood as:

    Ticket
       |
       +---- Approval
       |       |
       |       +---- Admin
       |       |
       |       +---- Role
       |
       +---- Approval
               |
               +---- Admin
               |
               +---- Role

---

# Approval Status

Approval records use the same `TicketStatusEnum` values.

The important values are:

    IN_REVIEW
    APPROVED
    REJECTED

An approval starts with:

    IN_REVIEW

The administrator can then change it to:

    APPROVED

or:

    REJECTED

If rejected, the administrator can provide a review.

---

# Approval Uniqueness

The database contains the following unique constraint:

    ticket_id
    admin_id
    role_id
    round

This prevents the same administrator from creating duplicate approval records for the same ticket, role, and round.

---

# Round

The approval table contains:

    round

The default value is:

    1

The current workflow primarily uses round `1`.

The field exists to support future multi-round approval workflows.

---

# Ticket API Request

The `tickets_api_requests` table stores information about communication between the application and the external API.

Important fields include:

    ticket_id
    idempotency_key
    attempt
    status
    request_id
    response_code
    response_body
    error_message
    sent_at
    completed_at

---

# Idempotency

The API job generates an idempotency key using:

    ticket:{ticket_id}:approved

For example:

    ticket:15:approved

This key ensures that the same approved ticket can be identified consistently across retry attempts.

The database also enforces uniqueness on:

    idempotency_key

This helps prevent duplicate API request records for the same ticket approval operation.

---

# Database Structure

The Ticketing module creates three primary tables.

    tickets
        |
        +---- tickets_approvals
        |
        +---- tickets_api_requests

---

# tickets

The `tickets` table contains the original ticket information.

Columns:

    id
    user_id
    title
    body
    file
    created_at
    updated_at

## user_id

References:

    users.id

Represents the user who created the ticket.

The foreign key uses:

    restrictOnDelete()

Therefore, a user cannot be deleted if tickets still reference that user.

---

## title

Contains the ticket title.

Type:

    string

---

## body

Contains the main ticket content.

Type:

    text

---

## file

Contains the path or reference to the ticket attachment.

Type:

    string | nullable

---

# tickets_approvals

This table stores administrator approval records.

Columns:

    id
    ticket_id
    admin_id
    role_id
    round
    status
    review
    created_at
    updated_at

---

# tickets_approvals Foreign Keys

## ticket_id

References:

    tickets.id

Uses:

    cascadeOnDelete()

If a ticket is deleted, its approval records are automatically deleted.

---

## admin_id

References:

    users.id

Uses:

    restrictOnDelete()

An administrator cannot be deleted while their approval records reference them.

---

## role_id

References:

    auth_roles.id

Uses:

    restrictOnDelete()

The role used during the approval must remain available while approval records reference it.

---

# tickets_api_requests

This table stores API request information.

Columns:

    id
    ticket_id
    idempotency_key
    attempt
    status
    request_id
    response_code
    response_body
    error_message
    sent_at
    completed_at
    created_at
    updated_at

The table is designed to support:

- API request history
- Retry attempts
- API response tracking
- Error tracking
- Idempotency
- Request timing
- Debugging

---

# Logic Layer

Directory:

    App/Logics/

The Logic layer contains business operations used by the application.

The main classes are:

    TicketLogic
    ApprovalTicketLogic

---

# TicketLogic

Class:

    Lareon\Modules\Ticketing\App\Logics\TicketLogic

This class contains general ticket operations.

Main methods:

    all()
    allByUser()
    first()
    create()
    delete()

---

# TicketLogic::all()

This method retrieves the ticket list through:

    TicketListQuery

The query is then paginated.

The purpose of moving this operation to a dedicated query class is to keep complex filtering and authorization-related visibility logic outside the Logic class.

---

# TicketLogic::allByUser()

Returns tickets belonging to the authenticated user.

The relationship used is:

    auth()->user()->tickets()

The result is processed through:

    FetchDataService

---

# TicketLogic::create()

Creates a new Ticket model using the provided input.

The actual database operation is wrapped using:

    ServiceWrapper

---

# TicketLogic::delete()

Deletes a ticket.

The operation is also wrapped using:

    ServiceWrapper

---

# ApprovalTicketLogic

Class:

    Lareon\Modules\Ticketing\App\Logics\ApprovalTicketLogic

This class contains administrator approval-related business operations.

Main methods:

    prepareApproval()
    update()
    bulkAction()
    getUser()

---

# prepareApproval()

Creates or retrieves an approval record for the authenticated administrator.

The approval is created with:

    status = IN_REVIEW

The administrator's first role is currently used as the role associated with the approval.

---

# update()

Updates an existing approval.

The update contains:

    review
    status

The authenticated administrator and their role are used to identify the approval.

---

# bulkAction()

Delegates bulk operations to:

    TicketBulkAction

This keeps the bulk-processing logic outside of the general approval logic.

---

# getUser()

Only users having one of these roles are accepted:

    ticket manager
    chief ticket manager

If the authenticated user does not have either role, the method returns `null`.

---

# Query Layer

Directory:

    App/queries/

The main query class is:

    TicketListQuery

The query layer is responsible for retrieving tickets according to the current administrator's visibility rules.

---

# TicketListQuery

Class:

    Lareon\Modules\Ticketing\App\queries\TicketListQuery

Main responsibilities:

- Ticket pagination
- Administrator visibility
- Search
- Status search
- Creator search
- Title search
- Loading approval relationships

---

# Ticket Filtering

The query always excludes tickets that already have API requests:

    whereDoesntHave('apiRequests')

This means tickets already submitted to the API are not returned by the current administrator ticket list.

---

# Ticket Manager Visibility

When the authenticated user has:

    ticket manager

the query allows tickets that satisfy one of the following conditions.

## New Ticket

The ticket has no approval records:

    no approvals

These are tickets waiting to be reviewed by the first administrator.

---

## Previously Reviewed by Current Manager

The ticket has an approval belonging to the current administrator with:

    role = ticket manager

and there is no:

    chief ticket manager

approval.

This allows the same first-level administrator to continue working with tickets assigned to them.

---

# Chief Ticket Manager Visibility

When the authenticated user has:

    chief ticket manager

the ticket must first have an approved:

    ticket manager

approval.

The ticket must also not have an existing chief approval, or the existing chief approval must belong to the current authenticated administrator.

Therefore the second-level administrator can only work on tickets that passed the first approval stage.

---

# Search

Ticket search is handled by:

    TicketListQuery::applySearch()

The current search can match:

    title
    creator name
    creator lastname
    approval status

---

# Search Statuses

The following search values are supported:

    pending
    approved
    rejected
    in_review

These values are converted to the corresponding `TicketStatusEnum`.

---

# Events

Directory:

    App/Events/

The module contains:

    UpdateTicketStatusEvent

The event receives:

    Ticket
    TicketApproval

The event is dispatched when an approval status changes.

---

# Observers

Directory:

    App/Observers/

The main observer is:

    TicketApprovalObserver

---

# TicketApprovalObserver

The observer listens to:

    TicketApproval::updated

It checks whether the approval status has actually changed.

If the status has changed, it dispatches:

    UpdateTicketStatusEvent

The flow is:

    TicketApproval updated
            |
            v
    status changed?
        /       \
      No         Yes
      |           |
      v           v
    Stop    UpdateTicketStatusEvent

This provides a central event point for reacting to approval changes.

---

# Jobs

Directory:

    App/Jobs/

The main asynchronous job is:

    TicketToApiJob

This job sends an approved ticket to the external API.

---

# TicketToApiJob

Class:

    Lareon\Modules\Ticketing\App\Jobs\TicketToApiJob

The job implements:

    ShouldQueue

Therefore it is intended to run through Laravel's queue system.

---

# Job Configuration

Current retry configuration:

    tries = 24

Timeout:

    30 seconds

Backoff:

    60 seconds

This means failed jobs can be retried multiple times with a delay between attempts.

---

# API Job Flow

The job follows this general process:

    Ticket
      |
      v
    Get Ticket
      |
      v
    Get/Create API Request
      |
      v
    Already Successful?
       |
       +---- Yes ---> Stop
       |
       +---- No
             |
             v
        Mark Processing
             |
             v
        Send HTTP Request
             |
       +-----+------+
       |            |
    Success       Failure
       |            |
       v            v
    Save          Save
    Success       Failure
       |            |
       |        Server Error?
       |          |
       |       +--+--+
       |       |     |
       |      Yes    No
       |       |     |
       |       v     v
       |     Retry   End
       |
       v
      End

---

# API Request

The request is sent using Laravel's HTTP client.

The request contains:

    Accept: application/json

and:

    Idempotency-Key

The payload contains two main sections:

    ticket
    user

The ticket section includes:

    id
    title
    body
    file

The user section includes:

    id
    name
    email

---

# API Success

If the external API returns a successful response, the API request record is updated with:

    status = success

and:

    response_code
    response_body
    completed_at

are stored.

A success message is also written to the job log.

---

# API Failure

If the API returns an unsuccessful response, the request is marked:

    status = failed

The following information can be stored:

    response_code
    response_body
    error_message

A warning is also written to the job log.

---

# API Exception

If an exception occurs while sending the request:

    status = failed

The exception message is stored in:

    error_message

The exception class and message are also written to the logs.

The exception is re-thrown so that Laravel's queue system can handle the failed job according to its queue configuration.

---

# Database Workflow

The complete database workflow can be represented as:

    users
      |
      | creates
      v
    tickets
      |
      | has many
      v
    tickets_approvals
      |
      | administrator + role
      |
      +----------------------+
      |                      |
      v                      v
    ticket manager     chief ticket manager
      |                      |
      | APPROVED              | APPROVED
      +----------+-----------+
                 |
                 v
        tickets_api_requests
                 |
                 v
          External API

---

# Complete Ticket Lifecycle

The complete lifecycle is:

    1. User creates ticket
              |
              v
    2. Ticket status = PENDING
              |
              v
    3. Ticket Manager reviews ticket
              |
              v
    4. Approval status = IN_REVIEW
              |
        +-----+------+
        |            |
      Reject       Approve
        |            |
        v            v
     REJECTED    Chief Ticket Manager
                     |
                     v
              Approval = IN_REVIEW
                     |
                +----+----+
                |         |
              Reject    Approve
                |         |
                v         v
             REJECTED   APPROVED
                           |
                           v
                    TicketToApiJob
                           |
                           v
                     External API
                           |
                     +-----+-----+
                     |           |
                   Success      Failure
                     |           |
                     v           v
                  SUCCESS      FAILED
                     |
                     v
              Request stored
              in database/logs

---

# ServiceWrapper

Business operations are wrapped using:

    Teksite\Handler\Actions\ServiceWrapper

This provides a common mechanism for executing operations and returning service results.

For example:

    ServiceWrapper::make(false)
        ->do(...)
        ->run()

The result is generally represented using:

    ServiceResult

This keeps the application service/logic responses consistent.

---

# Data Flow

A normal ticket creation request follows:

    User
      |
      v
    Controller
      |
      v
    TicketLogic
      |
      v
    ServiceWrapper
      |
      v
    Ticket Model
      |
      v
    tickets table

An approval request follows:

    Administrator
          |
          v
    ApprovalTicketLogic
          |
          v
    TicketApproval
          |
          v
    tickets_approvals
          |
          v
    TicketApprovalObserver
          |
          v
    UpdateTicketStatusEvent
          |
          v
    Additional event listeners / workflow

---

# API Data Flow

When the ticket reaches the API stage:

    Approved Ticket
          |
          v
    TicketToApiJob
          |
          v
    TicketApi record
          |
          v
    HTTP Client
          |
          v
    /api/endpoint
          |
          v
    HTTP Response
          |
          v
    TicketApi
          |
          +---- Database
          |
          +---- Job Log

---

# Database Relationships

The relationships can be represented as:

    User
      |
      +---- hasMany Tickets
      |
      +---- hasMany TicketApprovals
      |
      v
    Ticket
      |
      +---- belongsTo User
      |
      +---- hasMany TicketApprovals
      |
      +---- hasMany TicketApiRequests
      |
      v
    TicketApproval
      |
      +---- belongsTo Ticket
      +---- belongsTo User
      +---- belongsTo Role

    TicketApiRequest
      |
      +---- belongsTo Ticket

---

# Architectural Responsibilities

The responsibility of each layer can be summarized as follows:

    Controller
        |
        | HTTP handling
        v
    Logic
        |
        | Business operation
        v
    Action
        |
        | Complex/specific business action
        v
    Query
        |
        | Data retrieval
        v
    Model
        |
        | Database representation
        v
    Database

Asynchronous processing:

    Event
        |
        v
    Listener / Job
        |
        v
    External Service

---

# Current Architectural Considerations

The current implementation is primarily designed for development and testing.

Some areas should be improved before production usage.

## Role Identification

Several parts of the application identify roles using their display title:

    ticket manager
    chief ticket manager

A more robust implementation should use immutable role keys or identifiers.

---

## API Status

`ApiStatusEnum` currently uses integer values, while `TicketToApiJob` stores string statuses such as:

    pending
    processing
    success
    failed

These two approaches should be unified.

---

## Approval Rounds

The database supports:

    round

but the current business logic primarily uses:

    round = 1

A future multi-round workflow can make better use of this field.

---

## Ticket Status

Ticket status is currently calculated from approval records instead of being persisted directly.

This is useful for avoiding inconsistent duplicated state, but complex workflows may require additional state management.

---

# Summary

The Ticketing module follows a modular and layered architecture.

The major components are:

    Ticket
        |
        +---- TicketApproval
        |
        +---- TicketApi
        |
        +---- TicketLogic
        |
        +---- ApprovalTicketLogic
        |
        +---- TicketBulkAction
        |
        +---- TicketListQuery
        |
        +---- TicketApprovalObserver
        |
        +---- UpdateTicketStatusEvent
        |
        +---- TicketToApiJob

The main business workflow is:

    User
      |
      v
    Ticket
      |
      v
    Ticket Manager
      |
      v
    Chief Ticket Manager
      |
      v
    API Job
      |
      v
    External API

The architecture separates data access, business logic, actions, asynchronous processing, events, and database models, making it possible to extend the Ticketing module independently from the rest of the CMS.
