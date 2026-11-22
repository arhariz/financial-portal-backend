# CONSTITUTION.md — Binding code rules: acknowledgement, precedence, decision gates, rule index
# Authority: Owner / CTO
# Owner / approver: Owner / CTO
# Last Updated: 2026-11-21 (D-042)
# Source of truth for: mandatory AI acknowledgement, conflict precedence, GOV decision gates, forbidden commands, rule index
# Does NOT contain: rule bodies — those live in `rules/*.md`
# Read order: see `docs/DOCS_MANIFEST.md`
# Conflict rule: STATE_LEDGER wins for executed state; CONSTITUTION modules win for code rules; EXECUTION_LOCKS wins for business policy.
# Status: ACTIVE

---

## MANDATORY AI ACKNOWLEDGEMENT BLOCK

Every AI must reproduce this block verbatim before writing any code:

```
CONSTITUTION ACKNOWLEDGED
Task: [module code and name]
I have read docs/DOCS_MANIFEST.md, docs/State_Ledger.md, docs/EXECUTION_LOCKS_ACTIVE.md,
docs/Feature_Flags_Registry.md, docs/Constitution.md, and the rule module(s) this task touches:
  [rules/money.md / rules/backend.md / rules/security.md / rules/testing.md /
   rules/frontend.md / rules/mobile.md / rules/reporting.md / rules/operations.md /
   rules/documentation.md] in full.
Last STATE_LEDGER entry: [pull the actual latest entry code + date directly from docs/State_Ledger.md — do not guess]
Rules that apply: [list rule codes e.g. MON-02, PNL-03, WAL-04, GOV-01]
Schema tables touched: [list them]
I will not write code until the above is confirmed accurate.
```

`docs/EXECUTION_LOCKS_ACTIVE.md` is mandatory reading: it holds founder business locks and wins for business policy. A rule in `rules/*.md` never overrides a lock.

---

## CONFLICT PRECEDENCE

1. `docs/State_Ledger.md` — executed state, blockers, flags, queue.
2. `docs/Constitution.md` + `rules/*.md` — code rules.
3. `docs/EXECUTION_LOCKS_ACTIVE.md` — founder business policy (locked decisions, deferred items).
4. `docs/project_Context.md` / `docs/Schema_Registry.md` — architecture and tables.
5. `docs/plan.md` — advisory unless the decision is marked LOCKED there (then it is also a lock).

If two sources disagree and precedence does not settle it, **do not guess**: record both values in `docs/State_Ledger.md` §Open Questions and ask the owner. One fact, one home.

---

## DECISION GATES

**GOV-01 | Undecided business policy.** Any business, pricing, commission rate, MLM depth, agent tier name, deduction definition, exchange-rate rule, fee or accounting policy NOT already locked in `docs/plan.md`, `docs/EXECUTION_LOCKS_ACTIVE.md` or a Decision entry → STOP. Do not invent a default, placeholder or "sensible" value. Record the question in `docs/State_Ledger.md` §Open Questions against the blocked work and wait for a numbered Decision. | Verify: every config key, enum case and constant traces to a rule code or Decision ID.

**GOV-02 | Documentation / schema divergence.** A rule or prompt citing a column, table or term that does not exist is a DEFECT. Fix the document or the schema in the same work package — never silently pick one. | Verify: `Schema::getColumnListing()` compared against the citing rule.

**GOV-03 | Rule changes.** Adding or altering a financial, KYC, commission, wallet or P&L rule requires a Decision entry in `docs/State_Ledger.md` BEFORE code. A rule is never inferred from a skill, a prompt library entry, or another AI's output. | Verify: ledger entry cites the Decision ID.

---

## CAREER-ENDING COMMANDS — NEVER ON DEV OR PRODUCTION

`migrate:fresh` · `migrate:reset` · `db:wipe` · `DROP DATABASE` · `DROP SCHEMA` · `TRUNCATE` ·
`migrate:rollback` (owner approval only) · `db:seed` without `--class` · `composer update` ·
`APP_DEBUG=true` on production · `rm -rf storage/` · direct `UPDATE`/`DELETE` on ledger tables.

`migrate:fresh` is permitted in exactly one place: the test suite's `RefreshDatabase` rebuild of the test database. Dev and production are never rebuilt.

---

## ROOT RULES (GOV)

GOV-04 | Conflicting sources | Follow CONFLICT PRECEDENCE; if unresolved, stop and ask the owner | ledger question opened
GOV-05 | Code, prompt or document size | **No arbitrary limits.** Health is judged by cohesion, clarity, tests and review | design review, not line counts
GOV-06 | Implementation prompts | Must be detailed and complete; no size limit | prompt contains scope, inputs, criteria, verification
GOV-07 | Rule loading | Read 1–4 always + only the routed `rules/*.md` module(s) | ACK block names modules
GOV-08 | State Ledger retention | Keep last **4 detailed entries** + queue; rotate older details to `docs/State_Ledger_Archive.md`. Decisions are never archived | archive pointer line
GOV-09 | Claims of completion | Run verification commands and show output before saying done/fixed/passing; no fabricated numbers | `verification-before-completion`

---

## RULE INDEX

Rule IDs are **immutable**. A rule moved between modules keeps its code and number forever. A retired rule is marked retired, never reused.

| Module | Families | Rules | Primary enforcement |
|---|---|---:|---|
| `docs/Constitution.md` (root) | GOV | 9 | Ledger citation + review |
| `rules/money.md` | MON, TME, WAL, PNL, COM | 33 | Ledger/joint guard tests, schema inspection |
| `rules/backend.md` | COD, DB, QUE, API, FIL | 34 | PHPStan, Pint, guard tests |
| `rules/security.md` | SEC | 10 | Security review, CI scanners |
| `rules/testing.md` | TST | 13 | Test suite + reconciliation assertions + seeder coverage |
| `rules/frontend.md` | FE | 10 | `tsc`/ESLint, `npm run build` |
| `rules/mobile.md` | MOB | 13 | `flutter analyze`, integration tests |
| `rules/reporting.md` | REP | 11 | Report tests, module health widgets, role gates |
| `rules/operations.md` | OPS | 11 | Deploy script, verification gate, task-card loading, DONE gate |
| `rules/documentation.md` | DOC | 8 | Doc review + closeout |

**Rule format:** `CODE | Trigger | Constraint | Verify`.

---

## MODULE MAP

- `rules/money.md` — money, time, wallets, P&L, commissions (locked business mechanics).
- `rules/backend.md` — PHP, database, queues, API design, Filament 5.
- `rules/security.md` — OWASP, auth, secrets, files, audit.
- `rules/testing.md` — TDD, money-path tests, reconciliation, seeder coverage, CI gates.
- `rules/frontend.md` — React 19, Vite 8, TypeScript 7, Tailwind 4.
- `rules/mobile.md` — Flutter 3.50, Riverpod, flavors, device testing.
- `rules/reporting.md` — investor, CFO, CEO, CTO reports.
- `rules/operations.md` — local → staging → production, deploy, archive.
- `rules/documentation.md` — file map, naming, freshness, closeout.

---

*State + queue: `docs/State_Ledger.md`. Tables: `docs/Schema_Registry.md`. Founder locks: `docs/EXECUTION_LOCKS_ACTIVE.md`. Context: `docs/project_Context.md`. Terms: `docs/plan.md` §Domain glossary via `docs/project_Context.md`. Prompts: `docs/Prompt_Library.md`. Map: `docs/DOCS_MANIFEST.md`.*
