# docs/DOCS_MANIFEST.md — File map, read order, ownership and conflict rules
# Authority: Owner / CTO
# Last Updated: 2026-11-22 (D-051)
# Source of truth for: file map, owners, read order, authority, module loading
# Does NOT contain: executed state, queue, code rules, tables
# Conflict rule: STATE_LEDGER for executed state; CONSTITUTION for code rules; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

## READ ORDER

| # | File (owner) | Purpose |
|---|---|---|
| 1 | `docs/DOCS_MANIFEST.md` | File map, read order, authority |
| 2 | `docs/State_Ledger.md` | Current snapshot, blockers, live build queue, last 4 detailed entries |
| 3 | `docs/Constitution.md` | ACK block, conflict precedence, GOV gates, forbidden commands, rule index |
| 4 | `docs/EXECUTION_LOCKS_ACTIVE.md` | Founder business locks (mandatory) |
| 5 | `docs/Feature_Flags_Registry.md` | Deferred capabilities, default OFF (mandatory) |
| 6 | `rules/money.md` | MON, TME, WAL, PNL, COM |
| 7 | `rules/backend.md` | COD, DB, QUE, API, FIL |
| 8 | `rules/security.md` | SEC |
| 9 | `rules/testing.md` | TST |
| 10 | `rules/frontend.md` | FE |
| 11 | `rules/mobile.md` | MOB |
| 12 | `rules/reporting.md` | REP |
| 13 | `rules/operations.md` | OPS |
| 14 | `rules/documentation.md` | DOC |
| 15 | `docs/Schema_Registry.md` | Tables and money-flow invariants |
| 16 | `docs/project_Context.md` | Architecture, roles, environment, topology |
| 17 | `docs/Prompt_Library.md` | Paste **Part 0 UNIFIED SESSION START** in new sessions; Parts 1–3 are supporting blocks |
| 18 | `docs/Skills_Registry.md` | Installed skills and reinstall commands |
| 19 | `docs/plan.md` | Locked product plan (advisory unless marked LOCKED) |

**Module loading rule.** Read 1–5 always. Then read **only the rule module(s) the task touches** (6–14), plus 15–19 as the task requires. The ACK block names the modules read and the rule codes applied.

## APPENDIX — NOT IN THE READ ORDER

| File | Purpose |
|---|---|
| `docs/State_Ledger_Archive.md` | Rotated detailed entries and completed module details |
| `docs/Build_Queue_Detail.md` | 95 single-commit task cards — load **one card only** (OPS-10) |
| `docs/Admin_Observability_Spec.md` | 12 module health widgets, 8 Report tabs, final sign-off checklist |
| `skills/` directories | Custom + external AI skills (`docs/Skills_Registry.md`) |

## AUTHORITY & MAINTENANCE

Code rules → executed state → founder policy → tables → architecture → plans (advisory). If two files disagree, **do not guess**: record both values and ask the owner. One fact, one home: each file's header names its source of truth.

**Rule IDs are immutable.** A rule keeps its code and number when it moves between modules. A retired rule is marked retired, never reused. Git is the record — no version numbers in filenames. Update headers; commit with `fp-task-closeout`.

No arbitrary code, prompt or document size limits (GOV-05). Detail takes priority over byte targets.
