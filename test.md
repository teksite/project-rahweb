# Ticketing Module Tests

This document describes the Unit Tests and Feature Tests implemented for the Ticketing module.

---

## 1. Test Structure

The Ticketing tests are divided into two main categories:

```text
tests/
├── Unit/
│   └── Ticketing/
│       ├── Enums/
│       │   ├── ApiStatusEnumTest.php
│       │   └── TicketStatusEnumTest.php
│       └── Models/
│           └── TicketStatusTest.php│
└── Feature/
    └── Ticketing/
        ├── TicketApprovalTest.php
        ├── TicketApiTest.php
        ├── TicketEventListenerTest.php
        ├── TicketNotificationTest.php
        └── TicketVisibilityTest.php
```

### Unit Tests

Unit Tests verify isolated application components such as:

* Enums
* Model status calculation
* Logic classes
* Individual methods and behaviors

They should not depend on the complete application workflow.

### Feature Tests

Feature Tests verify that multiple application components work correctly together, including:

* Database
* Authentication
* Roles
* Models
* Events
* Listeners
* Notifications
* Queued Jobs
* Ticket visibility
* Approval workflow

---

# 2. Test Environment

The tests use Laravel's testing environment and `RefreshDatabase`.

Each test starts with a clean database state.

Make sure the testing database is correctly configured before running the tests.

Example:

```env
APP_ENV=testing
```

If a dedicated testing database is used, configure it in:

```text
phpunit.xml
```

or:

```text
.env.testing
```

---

# 3. Unit Tests

## 3.1 ApiStatusEnumTest

File:

```text
tests/Unit/Ticketing/Enums/ApiStatusEnumTest.php
```

This test verifies the `ApiStatusEnum`.

### What it checks

It verifies that every API status has the correct integer value:

```text
PENDING     = 0
PROCESSING  = 1
SUCCESS     = 2
FAILED      = 3
```

It also checks the translated labels returned by:

```php
ApiStatusEnum::label()
```

### Purpose

This protects the API request state machine from accidental changes to status values or labels.

---

# 4. TicketStatusEnumTest

File:

```text
tests/Unit/Ticketing/Enums/TicketStatusEnumTest.php
```

This test verifies the `TicketStatusEnum`.

### Status values

```text
IN_REVIEW = 0
APPROVED  = 1
REJECTED  = 2
PENDING   = 4
```

### What it checks

The test verifies:

* Enum values
* Status keys
* Badge CSS classes
* Generated HTML

For example:

```php
TicketStatusEnum::APPROVED->key()
```

must return:

```text
approved
```

The test also makes sure `toHtml()` contains the correct:

* Label
* CSS classes

### Purpose

This prevents UI status rendering and status identifiers from becoming inconsistent.

---

# 5. TicketStatusTest

File:

```text
tests/Unit/Ticketing/Models/TicketStatusTest.php
```

This is one of the most important tests in the Ticketing module.

It verifies the calculated status of a ticket based on its approvals.

## 5.1 Ticket without approvals

Expected:

```text
PENDING
```

If a ticket has no approval records, its status must be:

```php
TicketStatusEnum::PENDING
```

---

## 5.2 Ticket with an in-review approval

If an approval exists with:

```text
IN_REVIEW
```

the ticket must be:

```text
IN_REVIEW
```

---

## 5.3 Rejected ticket

If any approval has:

```text
REJECTED
```

the ticket must be:

```text
REJECTED
```

---

## 5.4 Fully approved ticket

If at least two approvals have:

```text
APPROVED
```

the ticket must be:

```text
APPROVED
```

---

## 5.5 Rejection priority

The test verifies that rejection has priority over approval.

Example:

```text
Manager 1 → APPROVED
Manager 2 → REJECTED
```

Expected ticket status:

```text
REJECTED
```

---

## 5.6 First manager approval

The test verifies:

```php
$ticket->approvementByFirstAdmin()
```

returns the approval belonging to:

```text
ticket manager
```

with:

```text
APPROVED
```

---

## 5.7 Chief manager approval

The test verifies:

```php
$ticket->approvementBySecondAdmin()
```

returns the approval belonging to:

```text
chief ticket manager
```

with:

```text
APPROVED
```


---

# 8. TicketApprovalTest

File:

```text
tests/Feature/Ticketing/TicketApprovalTest.php
```

This test verifies the approval workflow.

## 6.1 Manager can prepare approval

A user with:

```text
ticket manager
```

must be able to create an approval record.

Expected:

```text
status = IN_REVIEW
```

---

## 6.2 Non-manager cannot prepare approval

A normal user must not be allowed to create an approval.

Expected:

```text
success = false
```

and:

```text
tickets_approvals = 0
```

---

## 6.3 Manager can update approval

A ticket manager can update an approval to:

```text
APPROVED
```

and provide a review.

The test verifies the database contains the expected:

```text
ticket_id
admin_id
status
review
```

---

# 7. TicketEventListenerTest

File:

```text
tests/Feature/Ticketing/TicketEventListenerTest.php
```

This test verifies Events, Listeners, Notifications, and Jobs.

---

## 7.1 New ticket notifies ticket managers

When:

```php
NewTicketEvent
```

is dispatched, ticket managers must receive:

```text
NewTicketNotificationToAdmin
```

The test uses:

```php
Notification::fake()
```

so no real email is sent.

---

## 7.2 New ticket notifies ticket creator

When a new ticket is created, the ticket creator must receive:

```text
NewTicketNotificationToClient
```

---

## 7.3 Two approvals dispatch API Job

When a ticket has:

```text
Manager 1 → APPROVED
Manager 2 → APPROVED
```

the system must dispatch:

```text
TicketToApiJob
```

The test uses:

```php
Bus::fake()
```

so the real API request is not executed.

---

# 8. TicketNotificationTest

File:

```text
tests/Feature/Ticketing/TicketNotificationTest.php
```

This test verifies the content of ticket status notifications.

## 8.1 Approved notification

Verifies that the generated email contains:

```text
approved
```

when the approval status is:

```text
APPROVED
```

---

## 8.2 Rejected notification

Verifies that a rejection reason is included in the email.

Example:

```text
Invalid information
```

The test ensures the reason entered by the manager is shown to the client.

---

## 8.3 Notification data

The test verifies that `toArray()` contains:

```text
ticket_id
ticket_title
approval_id
status
role
review
user_id
```

This is useful if database or application notifications are enabled later.

---

# 9. TicketApiTest

File:

```text
tests/Feature/Ticketing/TicketApiTest.php
```

This test verifies the `TicketApi` model and its relationship with tickets.

## 9.1 API request belongs to ticket

Verifies:

```php
$ticketApi->ticket
```

returns the correct ticket.

---

## 9.2 API status is cast to Enum

The database stores the status as an integer, but Laravel must return:

```php
ApiStatusEnum
```

For example:

```php
$request->status
```

must return:

```php
ApiStatusEnum::PROCESSING
```

instead of:

```text
1
```

---

## 9.3 Response body casting

Verifies that:

```text
response_body
```

is correctly converted between JSON and PHP array.

---

## 9.4 Ticket deletion cascades to API requests

The migration specifies:

```text
ticket_id → cascadeOnDelete
```

Therefore, deleting a ticket must also delete its API requests.

---

# 10. TicketVisibilityTest

File:

```text
tests/Feature/Ticketing/TicketVisibilityTest.php
```

This is the most important Feature Test for the current approval workflow.

It verifies that managers see only tickets they are allowed to see.

---

## 10.1 Manager can see ticket without approval

A ticket without any approval must be visible to:

```text
ticket manager
```

Expected:

```text
visible = true
```

---

## 10.2 Manager can see own approval

If the current ticket manager has an approval record for the ticket, the ticket must remain visible to that manager.

---

## 10.3 Manager cannot see another manager's approval

If:

```text
Manager 2
```

has created the approval, another:

```text
Manager 1
```

must not see that ticket through the manager workflow.

---

## 10.4 Chief cannot see ticket before first manager approval

A:

```text
chief ticket manager
```

must not see a ticket while the first manager has only:

```text
IN_REVIEW
```

approval.

The first manager must approve it first.

---

## 10.5 Chief can see ticket after first manager approval

Once:

```text
ticket manager → APPROVED
```

the:

```text
chief ticket manager
```

must be able to see the ticket.

---

## 10.6 Chief cannot see rejected ticket

If the first manager rejects the ticket:

```text
ticket manager → REJECTED
```

the chief manager must not see it.

---

## 10.7 Manager cannot see ticket with API request

Once an API request exists for a ticket:

```text
tickets_api_requests
```

the ticket must no longer be visible in the manager workflow.

This prevents managers from acting on tickets that have already entered the external API workflow.

---

# 11. Running All Tests

Run the complete Laravel test suite:

```bash
php artisan test
```

---

# 12. Running Only Ticketing Tests

Run all Ticketing Unit Tests:

```bash
php artisan test tests/Unit/Ticketing
```

Run all Ticketing Feature Tests:

```bash
php artisan test tests/Feature/Ticketing
```

---

# 13. Running a Specific Test File

For example:

```bash
php artisan test tests/Unit/Ticketing/Enums/TicketStatusEnumTest.php
```

Or:

```bash
php artisan test tests/Feature/Ticketing/TicketVisibilityTest.php
```

---

# 14. Running a Specific Test Method

Use `--filter`:

```bash
php artisan test --filter=manager_can_see_ticket_without_approval
```

For example:

```bash
php artisan test --filter=TicketVisibilityTest
```

---

# 15. Stop on First Failure

For debugging:

```bash
php artisan test --stop-on-failure
```

Or:

```bash
php artisan test tests/Feature/Ticketing --stop-on-failure
```

This is recommended when a test failure is caused by a database setup or workflow problem.

---

# 16. Recommended Debugging Workflow

When a test fails, first run only the failing test:

```bash
php artisan test --filter=TestName
```

Then run the entire test class:

```bash
php artisan test tests/Feature/Ticketing/TicketVisibilityTest.php
```

Finally, run the complete Ticketing suite:

```bash
php artisan test tests/Feature/Ticketing
```

This makes it easier to determine whether the problem is:

1. An isolated test problem
2. A database/setup problem
3. A model problem
4. A role/permission problem
5. A query/visibility problem
6. An event/listener problem
7. A notification problem
8. A queue/job problem

---

# 17. Important Test Data

The Ticketing tests rely on the following roles:

```text
ticket manager
chief ticket manager
```

The Feature Tests create these roles automatically during `setUp()` when necessary.

This is important because `TicketListQuery` expects the roles to exist.

For example:

```php
Role::query()->firstOrCreate([
    'title' => 'ticket manager',
]);

Role::query()->firstOrCreate([
    'title' => 'chief ticket manager',
]);
```

Without these roles, methods such as:

```php
chiefTicketManagerRoleId()
```

may return `null`.

---

# 18. Database Isolation

Feature Tests use:

```php
use RefreshDatabase;
```

This means each test starts from a clean database state.

Tests should therefore not depend on:

* Data created by another test
* The order in which tests execute
* Existing tickets
* Existing approvals
* Existing API requests

Each test should create the data it needs.

---

# 19. Fake Notifications and Jobs

Tests should never send real emails or call the real external API.

For notifications:

```php
Notification::fake();
```

For queued jobs:

```php
Bus::fake();
```

This allows the test to verify that the correct notification or job was dispatched without performing the actual external operation.

---

# 20. Expected Ticket Workflow

The tests currently validate the following workflow:

```text
New Ticket
    │
    ▼
Pending
    │
    ▼
Ticket Manager
    │
    ├── Reject ───────────────► Rejected
    │
    └── Approve
            │
            ▼
    Chief Ticket Manager
            │
            ├── Reject ───────► Rejected
            │
            └── Approve
                    │
                    ▼
                Approved
                    │
                    ▼
              TicketToApiJob
```

The visibility rules are:

```text
Ticket Manager
    │
    ├── No approval              → Visible
    ├── Own approval             → Visible
    ├── Another manager approval → Not visible
    └── API request exists       → Not visible

Chief Ticket Manager
    │
    ├── First manager pending    → Not visible
    ├── First manager approved   → Visible
    └── First manager rejected   → Not visible
```

---

# 21. Recommended Test Command

During development, the recommended command is:

```bash
php artisan test tests/Feature/Ticketing --stop-on-failure
```

After all Feature Tests pass:

```bash
php artisan test tests/Unit/Ticketing
```

Finally:

```bash
php artisan test
```

The final command should pass before considering the Ticketing module test suite complete.
