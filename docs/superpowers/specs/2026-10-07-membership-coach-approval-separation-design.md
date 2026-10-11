# Membership and Coach Approval Separation

## Goal

Separate gym membership approval from coach assignment approval so administrators control plans, payments, and gym access while coaches only decide whether to train a member.

## Roles and Responsibilities

- Members submit a membership application containing a gym plan, payment details, and an optional preferred coach.
- Administrators review every pending membership application, including applications that request a coach.
- Administrator approval activates gym access and marks the associated payment as paid.
- Coaches never approve membership plans, payments, or gym access.
- After administrator approval, the requested coach receives a separate assignment request.
- A coach may accept or decline only assignment requests addressed to that coach.

## Data Model

Add a `coach_assignment_requests` table with:

- `id`
- `member_id`
- `coach_id`
- `status`: `pending`, `accepted`, or `declined`
- `reviewed_at`, nullable
- timestamps

Add `requested_coach_id`, nullable, to subscriptions. This preserves the member's coach preference while the membership awaits administrator review. `members.coach_id` continues to mean an accepted, active coaching relationship only.

Only one pending coach-assignment request may exist for a member at a time. Historical accepted and declined requests remain available for auditing.

## Membership Application Flow

1. The member selects an active membership plan, payment method, and optionally a preferred coach.
2. The application creates or updates the member profile without assigning `members.coach_id`.
3. It creates a pending subscription containing `requested_coach_id` and a pending transaction.
4. Every pending subscription appears in the administrator's Approval Reviews page.
5. The requested coach cannot see an assignment request before administrator approval.

## Administrator Review Flow

On approval:

1. Lock and verify the pending subscription.
2. Activate the subscription and mark its transaction paid.
3. If `requested_coach_id` is present, create a pending coach-assignment request.
4. Notify the member that gym access is active.
5. Notify the requested coach that a training assignment awaits review.

On decline:

1. Mark the subscription declined and transaction rejected.
2. Do not create a coach-assignment request.
3. Notify the member of the decision.

## Coach Review Flow

The coach Approvals page lists only pending `coach_assignment_requests` addressed to the authenticated coach.

The coach interface must not display membership plan names, plan prices, payment methods, payment references, uploaded receipts, or membership approval controls. The page is renamed from **Member approvals** to **Training requests**. Its actions are **Accept client** and **Decline request**, making clear that the coach is deciding only whether to train the member.

On acceptance:

1. Lock and verify the pending request.
2. Set `members.coach_id` to the accepting coach.
3. Mark the request accepted and set `reviewed_at`.
4. Notify the member that the coach assignment was accepted.

On decline:

1. Leave `members.coach_id` null.
2. Mark the request declined and set `reviewed_at`.
3. Notify the member that gym access remains active and another coach may be selected.

## Replacement Coach Flow

An active member with no accepted coach and no pending assignment request may select an available coach from the member portal. The request goes directly to that coach without another administrator membership review.

Members with a pending request cannot create another request. Members with an accepted coach cannot request a replacement until the accepted assignment is removed through a future reassignment feature.

## Chat and Client Visibility

- A pending coach request does not grant chat access.
- A declined coach request does not grant chat access.
- Chat access begins only when `members.coach_id` is set after coach acceptance.
- A coach's client directory continues to require both an accepted assignment and an active subscription.
- Administrators retain chat access to every portal account.

## Existing Data

- Existing members with `members.coach_id` remain accepted clients.
- Existing active seeded subscriptions remain active.
- New seeded data may include accepted assignment history matching the existing `members.coach_id` values.
- Existing pending subscriptions with `members.coach_id` must be migrated by moving that coach to `subscriptions.requested_coach_id` and clearing `members.coach_id` until coach acceptance.

## Interface Changes

- Admin Approval Reviews displays every pending membership and its requested coach, if any.
- Coach Approvals is presented as **Training requests** and displays assignment requests only. It may show the member's name, fitness goal, availability, and submitted date, but never subscription plan or payment data.
- Coach actions are labeled **Accept client** and **Decline request**; the coach never sees an **Approve member** action.
- Member dashboard shows coach assignment status: none, pending, accepted, or declined.
- Active members without a coach can choose an available coach.
- Membership status and coach status are displayed independently.

## Authorization and Failure Handling

- Administrator membership decisions require the admin middleware.
- Coach assignment decisions require the coach middleware and ownership of the request.
- Member coach requests derive the member from the authenticated account and require an active subscription.
- Repeated decisions return a conflict response and cannot create duplicate assignments.
- Database transactions and row locks protect all approval transitions.

## Verification

Tests must prove:

- Admin sees and can decide every pending membership.
- Coach cannot see an assignment before admin approval.
- Admin approval activates gym access and creates the correct coach request.
- Coach acceptance assigns the member; decline leaves the member active and unassigned.
- A declined member can request another available coach without admin review.
- Pending coach relationships do not grant chat or client-directory access.
- Direct URLs cannot bypass role or ownership checks.
- Existing seeded memberships and coach assignments remain functional.
