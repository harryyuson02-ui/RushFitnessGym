# SDD ledger — plan: docs/superpowers/plans/2026-10-07-membership-coach-approval-separation.md

Ruling: Execute in the current main-branch workspace because the user repeatedly requested changes to the currently running local application and the uncommitted prior feature work is a dependency; an isolated worktree would not update that running app. Cost if wrong: changes remain uncommitted and must be reviewed as one working-tree diff.

Pre-flight: Task 1 produces assignment relationships consumed by Tasks 2–5; names and ownership rules match the specification.
Pre-flight: Task 2 produces post-admin pending requests consumed by Task 3; creation timing matches the specification.
Pre-flight: Task 3 sets members.coach_id consumed by Task 5 chat/client visibility; accepted assignment is the sole source of that relationship.

Task 1: complete (working-tree implementation, tests: ApprovalWorkflowTest relationship/uniqueness coverage passed).
Task 2: complete (working-tree implementation, tests: admin membership approval and application routing passed).
Task 3: complete (working-tree implementation, tests: coach training-request privacy and decisions passed).
Task 4: complete (working-tree implementation, tests: active replacement selection and authorization passed).
Task 5: complete (working-tree implementation, tests: seeded history plus client/chat isolation passed).
Task 6: complete (Pint passed; PHPUnit 148/148 passed; Vite build passed; git diff --check passed; local SQLite migrated and seeded).
Final review: self-review because current instructions prohibit spawning an unrequested reviewer; no Critical, Important, or Minor findings remained after reviewing the workflow diff and live coach page.
