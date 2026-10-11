# Membership and Coach Approval Separation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make administrators approve every membership and payment before coaches separately accept or decline training assignments.

**Architecture:** Subscriptions remain the source of gym-access state. A new `CoachAssignmentRequest` model owns coach-selection state and history; `members.coach_id` represents only an accepted assignment. Admin and coach controllers transition their own records independently inside database transactions.

**Tech Stack:** PHP 8.5, Laravel 13, Eloquent, Blade, SQLite, PHPUnit 12

**Spec:** `docs/superpowers/specs/2026-10-07-membership-coach-approval-separation-design.md`

## Global Constraints

- Preserve role middleware and direct-URL authorization.
- Coaches never receive plan, price, payment, reference, or receipt data.
- Coach requests appear only after administrator membership approval.
- Pending or declined coach requests do not grant chat or client access.
- Existing accepted assignments remain functional.
- Use TDD for every behavior change and run Pint on changed PHP files.

## Review Focus

- Repeated admin approval must not create duplicate coach requests.
- Repeated coach decisions must return conflict without changing assignments.
- A member cannot request two coaches concurrently.
- An inactive member cannot request a coach.
- Existing pending applications with a selected coach must remain reviewable by admin after migration.

---

### Task 1: Coach Assignment Persistence

**Files:**
- Create: `database/migrations/2026_10_07_000001_create_coach_assignment_requests_table.php`
- Create: `database/migrations/2026_10_07_000002_add_requested_coach_to_subscriptions.php`
- Create: `app/Models/CoachAssignmentRequest.php`
- Create: `database/factories/CoachAssignmentRequestFactory.php`
- Modify: `app/Models/Member.php`
- Modify: `app/Models/Coach.php`
- Modify: `app/Models/Subscription.php`
- Test: `tests/Feature/ApprovalWorkflowTest.php`

**Interfaces:**
- Produces `Member::coachAssignmentRequests()`, `Member::pendingCoachAssignment()`, `Coach::assignmentRequests()`, and `Subscription::requestedCoach()`.

- [ ] Write failing relationship and uniqueness tests, including one pending request per member.
- [ ] Run the focused tests and verify expected schema/model failures.
- [ ] Add migrations, model, factory, casts, fillable fields, and relationships.
- [ ] Run focused tests and verify they pass.

### Task 2: Membership Application and Admin Approval

**Files:**
- Modify: `app/Http/Controllers/Member/ApplicationController.php`
- Modify: `app/Http/Controllers/Admin/ApprovalController.php`
- Modify: `app/Http/Controllers/Admin/GymAccessApprovalController.php`
- Modify: `resources/views/admin/approvals/index.blade.php`
- Modify: `tests/Feature/ApprovalWorkflowTest.php`
- Modify: `tests/Feature/PortalWorkflowsTest.php`

**Interfaces:**
- Consumes `Subscription::requestedCoach()` and coach-assignment relationships from Task 1.
- Produces admin-only membership activation and post-approval pending assignment creation.

- [ ] Write failing tests proving every pending subscription appears for admin, including coach-selected applications.
- [ ] Write failing tests proving application does not set `members.coach_id` and coach cannot review before admin approval.
- [ ] Write failing idempotency tests for repeated administrator decisions.
- [ ] Change application persistence to store `requested_coach_id` on the subscription.
- [ ] Change admin queries and approval transaction to activate membership and create exactly one pending coach request.
- [ ] Update admin review UI to show plan and payment verification details plus requested coach.
- [ ] Run focused tests and verify they pass.

### Task 3: Coach Training Request Review

**Files:**
- Modify: `app/Http/Controllers/Coach/ApprovalController.php`
- Modify: `app/Http/Controllers/Coach/MemberApplicationApprovalController.php`
- Modify: `resources/views/coach/approvals/index.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/ApprovalWorkflowTest.php`

**Interfaces:**
- Consumes `CoachAssignmentRequest` from Task 1.
- Produces coach-owned accept/decline transitions and accepted `members.coach_id` assignment.

- [ ] Write failing tests proving coaches see only their post-admin assignment requests.
- [ ] Write failing response-content tests excluding plans, prices, payments, references, and receipts.
- [ ] Write failing tests for acceptance, decline, ownership, and repeated decisions.
- [ ] Replace subscription approval queries with assignment-request queries.
- [ ] On acceptance assign `members.coach_id`; on decline leave it null.
- [ ] Rename page copy to **Training requests** and actions to **Accept client** and **Decline request**.
- [ ] Run focused tests and verify they pass.

### Task 4: Replacement Coach Selection

**Files:**
- Create: `app/Http/Controllers/Member/CoachAssignmentController.php`
- Create: `app/Http/Requests/StoreCoachAssignmentRequest.php`
- Modify: `app/Http/Controllers/Portal/DashboardController.php`
- Modify: `resources/views/member/dashboard.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/ApprovalWorkflowTest.php`

**Interfaces:**
- Produces `POST /member/coach-request` for active, unassigned members without a pending request.

- [ ] Write failing tests for active-member selection, inactive rejection, duplicate-pending rejection, and accepted-assignment rejection.
- [ ] Add the authorized request class and controller transaction.
- [ ] Add member dashboard states for pending, accepted, declined, and available replacement coaches.
- [ ] Verify replacement requests bypass administrator review and reach only the selected coach.
- [ ] Run focused tests and verify they pass.

### Task 5: Chat, Client Visibility, Existing Data, and Seeders

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`
- Modify: `tests/Feature/DatabaseSeederTest.php`
- Modify: `tests/Feature/PortalWorkflowsTest.php`
- Modify migration data transformation in Task 1 files

**Interfaces:**
- Preserves `members.coach_id` as the sole accepted relationship consumed by chat and client queries.

- [ ] Write failing tests proving pending and declined requests grant neither chat nor client access.
- [ ] Add migration handling for existing pending subscriptions that currently store an unaccepted `members.coach_id`.
- [ ] Seed accepted assignment history for existing active seeded members.
- [ ] Verify seeded chat and client visibility remain correct.
- [ ] Run focused seeder and portal tests.

### Task 6: Full Verification

**Files:**
- Verify all modified files.

- [ ] Run `vendor\bin\pint --dirty --format agent` and require a clean result.
- [ ] Run `php artisan test --compact` and require zero failures.
- [ ] Run `npm run build` and require exit code 0.
- [ ] Run `git diff --check` and require no whitespace errors.
- [ ] Apply migrations and seeders to the local SQLite database.
- [ ] Verify live admin approval, coach training request, decline/replacement, chat restrictions, and accepted client visibility with isolated sessions.
