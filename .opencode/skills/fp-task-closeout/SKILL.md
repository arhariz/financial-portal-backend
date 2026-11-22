---
name: fp-task-closeout
description: Closeout protocol for every completed task on the Financial Investments Portal. Use after implementing, fixing, or reviewing anything in this repository. Triggers on done, complete, finished, close task, update ledger, change history, implemented module.
---

# Task Closeout Protocol

After any completed task in this repository:

1. **docs/State_Ledger.md**
   - Mark the module status in §5 (Implemented Modules).
   - Add an ENTRY LOG row with date, change, and actor; update `CURRENT_SNAPSHOT` and `BUILD_QUEUE`. Keep only the **last 4 detailed entries** active; rotate older details to `docs/State_Ledger_Archive.md` with a pointer line (GOV-08 / OPS-08).
   - Resolve any related Open Question in §4, or add a new one if discovered.
   - If a decision changed, add a new Decision ID and supersede the old one — decisions are never archived.
2. **docs/Schema_Registry.md** — if the database structure changed, update the table registry and money-flow invariants.
3. **docs/plan.md** — only if a locked requirement genuinely changed (requires owner approval and a new decision).
4. **docs/Constitution.md** — only if a rule was added/amended through the official amendment procedure.
5. **Tests** — confirm the relevant tests were run and are green; never close a money-path task without ledger reconciliation assertions.
6. Report a concise summary: what changed, which files, test result, and any new deferred item.
