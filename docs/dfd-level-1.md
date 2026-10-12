# Rush Fitness Gym — Data Flow Diagram Level 1

This Level 1 DFD is derived from the Laravel routes, controllers, models, and migrations in the Rush Fitness Gym system.

## Diagram code

```mermaid
flowchart LR
    subgraph externalEntities ["External Entities"]
        member["Member"]
        coach["Coach"]
        admin["Administrator"]
        mail["Email Service"]
    end

    subgraph systemProcesses ["Rush Fitness Gym Processes"]
        p1(["1.0 Manage User Access"])
        p2(["2.0 Process Membership Application"])
        p3(["3.0 Review Membership Approval"])
        p4(["4.0 Manage Coach Assignment"])
        p5(["5.0 Maintain Coaching Schedule"])
        p6(["6.0 Record Gym Attendance"])
        p7(["7.0 Exchange Messages and Notifications"])
    end

    subgraph dataStores ["Data Stores"]
        d1[("D1 User Access Data")]
        d2[("D2 Gym Profile Data")]
        d3[("D3 Membership Finance Data")]
        d4[("D4 Coach Assignment Data")]
        d5[("D5 Coaching Schedule Data")]
        d6[("D6 Attendance Data")]
        d7[("D7 Communication Data")]
    end

    member -->|"Registration and credentials"| p1
    coach -->|"Credentials and profile"| p1
    admin -->|"Credentials and account changes"| p1
    p1 -->|"Access result"| member
    p1 -->|"Access result"| coach
    p1 -->|"Access result"| admin
    p1 -->|"Account and session data"| d1
    d1 -->|"Credentials roles sessions"| p1
    p1 -->|"Password reset code"| mail

    member -->|"Application profile and receipt"| p2
    d2 -->|"Plans and available coaches"| p2
    p2 -->|"Member profile"| d2
    p2 -->|"Pending subscription and payment"| d3
    p2 -->|"Review package"| p3
    p2 -->|"Submission confirmation"| member

    admin -->|"Approval decision"| p3
    d3 -->|"Pending application details"| p3
    p3 -->|"Subscription and payment status"| d3
    p3 -->|"Approved coach request"| p4
    p3 -->|"Membership decision event"| p7

    member -->|"Coach selection"| p4
    coach -->|"Assignment decision"| p4
    d4 -->|"Pending assignment requests"| p4
    p4 -->|"Assignment request and review"| d4
    p4 -->|"Assigned coach link"| d2
    p4 -->|"Assignment decision event"| p7

    coach -->|"Availability and client notes"| p5
    admin -->|"Schedule approval decision"| p5
    member -->|"Calendar and records query"| p5
    d5 -->|"Availability schedules and records"| p5
    p5 -->|"Schedule changes and records"| d5
    d2 -->|"Member and coach roster"| p5
    p5 -->|"Calendar and coaching records"| member
    p5 -->|"Schedule and client data"| coach
    p5 -->|"Schedule decision event"| p7

    member -->|"QR access request"| p6
    admin -->|"Scanned QR value"| p6
    d3 -->|"Active subscription and token"| p6
    d6 -->|"Existing daily attendance"| p6
    p6 -->|"Attendance record"| d6
    p6 -->|"Check-in result"| admin
    p6 -->|"Attendance event"| p7

    member -->|"Message content and media"| p7
    coach -->|"Message content and media"| p7
    admin -->|"Message content and media"| p7
    p7 -->|"Messages and alerts"| d7
    d7 -->|"Conversation and unread alerts"| p7
    p7 -->|"Messages and notifications"| member
    p7 -->|"Messages and notifications"| coach
    p7 -->|"Messages and notifications"| admin

    style externalEntities fill:#FFECBD,stroke:#FFC943
    style systemProcesses fill:#C2E5FF,stroke:#3DADFF
    style dataStores fill:#CDF4D3,stroke:#66D575
```

## Data-store mapping

| Store | Name | Database tables |
|---|---|---|
| D1 | User Access Data | `users`, `password_reset_tokens`, `sessions` |
| D2 | Gym Profile Data | `members`, `coaches`, `membership_plans` |
| D3 | Membership Finance Data | `subscriptions`, `transactions` |
| D4 | Coach Assignment Data | `coach_assignment_requests` |
| D5 | Coaching Schedule Data | `coach_availability_changes`, `availability_slots`, `schedule_events`, `coach_client_records` |
| D6 | Attendance Data | `attendances` |
| D7 | Communication Data | `messages`, `notifications` |

Message and receipt binaries are stored through Laravel's configured filesystem. Their paths and attachment metadata are retained in the `messages` and `transactions` tables.

## Process table

| Process No. | Process Name | Inputs | Outputs | Data Stores Used |
|---|---|---|---|---|
| 1.0 | Manage User Access | Member registration/login details; coach and administrator credentials/profile changes; stored credentials, roles, and sessions | Role-based access results; updated account/session data; password-reset code sent to the email service | D1 — `users`, `password_reset_tokens`, `sessions` |
| 2.0 | Process Membership Application | Member profile, selected plan/coach, payment details, receipt; active plans and available coaches | Member profile; pending subscription and transaction; review package; submission confirmation | D2 — `members`, `coaches`, `membership_plans`; D3 — `subscriptions`, `transactions` |
| 3.0 | Review Membership Approval | Administrator decision; pending membership, payment, and receipt details | Approved/declined subscription and payment status; optional coach request; membership decision event | D3 — `subscriptions`, `transactions` |
| 4.0 | Manage Coach Assignment | Member coach selection; coach decision; coach request from approved application; pending requests | Assignment request/review status; assigned coach link; assignment decision event | D4 — `coach_assignment_requests`; D2 — `members`, `coaches` |
| 5.0 | Maintain Coaching Schedule | Coach availability and client notes; administrator schedule decision; member calendar/record query; roster and stored schedule data | Availability changes; approved slots; calendar events; coaching records; calendar/client views; schedule decision event | D5 — `coach_availability_changes`, `availability_slots`, `schedule_events`, `coach_client_records`; D2 — `members`, `coaches` |
| 6.0 | Record Gym Attendance | Member QR request; administrator-scanned QR value; active subscription/access token; existing daily attendance | QR/access validation result; daily attendance record; administrator check-in result; attendance event | D3 — `subscriptions`; D6 — `attendances` |
| 7.0 | Exchange Messages and Notifications | Member, coach, and administrator messages/media; membership, assignment, schedule, and attendance events; stored conversations and alerts | Stored messages/alerts; conversations; unread alerts; delivered messages and notifications | D7 — `messages`, `notifications` |

## DFD rule check

- Every process has at least one input and one output.
- Every data flow is labeled.
- No external entity connects directly to another external entity.
- No data store connects directly to another data store.
- External entities interact with data stores only through numbered processes.

