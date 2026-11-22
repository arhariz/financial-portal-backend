# Prompt Library — Financial Investments Portal

This file is the **protocol + task-card prompts**. It is aligned 1:1 with `docs/Build_Queue_Detail.md`: you never invent a task here — you take a card from the live queue. Prompts are detailed by design — no size limit.

---

# PART 0 — UNIFIED SESSION START (paste ONLY this block in a new session)

Copy the entire block below into the first message of any new AI session. It makes the agent find the next queued task, ask you to confirm, run pre-check gates, and only then start.

```
UNIFIED SESSION START — Financial Investments Portal

You are entering this project as a senior AI agent. Follow this protocol exactly.
DO NOT WRITE CODE until Step 5 says you may.

STEP 0 — LOAD AUTHORITY (in this order; do not read everything)
1. docs/DOCS_MANIFEST.md — read order and module-loading rule.
2. docs/State_Ledger.md — CURRENT_SNAPSHOT, BUILD_QUEUE (summary), KNOWN ISSUES, last 4 ENTRY LOG rows.
3. docs/EXECUTION_LOCKS_ACTIVE.md — founder policy; wins over any rule.
4. docs/Feature_Flags_Registry.md — deferred capabilities are default OFF.
5. docs/Constitution.md — mandatory ACK block, conflict precedence, GOV-01/02/03, career-ending commands.
Do NOT read the whole docs/Build_Queue_Detail.md. Do NOT load all rules/*.md yet.

STEP 1 — FIND THE NEXT TASK
- From docs/State_Ledger.md §BUILD_QUEUE (summary only), find the first work package with no unmet "Blocked by" and at least one task not DONE.
- Open EXACTLY ONE next task card in docs/Build_Queue_Detail.md (OPS-10). If no card is unblocked, report that and STOP.
- Report to me: card ID, title, scope/files, rule codes, schema tables, verification commands, DONE-widget dependency, prerequisites, and the real last STATE_LEDGER entry code + date.
- Never hardcode the next card — read it from the live BUILD_QUEUE summary; open issues Q-005/Q-006/Q-007 (role/profile policy) and Q-008 (disk capacity) block only their named cards.

STEP 2 — ACKNOWLEDGE
Reproduce the CONSTITUTION ACKNOWLEDGED block verbatim with:
- Task card: [exact WPn-Tnn]
- rule module(s) named by the card
- real latest docs/State_Ledger.md entry (never guessed)
- rule codes from the card
- schema tables from the card
- final line: "I will not write code until the above is confirmed accurate."

STEP 3 — STOP AND ASK ME
Show the Step 1 summary + ACK draft, then STOP and ask exactly:
"Proceed with [WPn-Tnn]? Reply START to run the pre-check gate, or tell me the correction."
Do not implement anything in this step. Any reply other than START means stop and wait.

STEP 4 — PRE-CHECK STATUS GATE (run ONLY after I reply START)
Report PASS/FAIL with evidence for each:
A. AUTHORITY — ACK accurate; no GOV-01 blocker applies to THIS card (check KNOWN ISSUES; Q-005/6/7 block WP3-T08..T10, Q-008 blocks all heavy tooling); no RED widget from earlier modules.
B. DEPENDENCIES — all prerequisite cards DONE; required health widget exists if an earlier module is being marked DONE (OPS-11 / REP-09).
C. ENVIRONMENT — php -v = 8.3.x; composer -V available; node -v >= 20.19; for mobile cards fvm flutter --version = 3.50/Dart 3.14; MySQL + Redis reachable; free disk > 2 GB (Q-008). AGENTS.md Laravel Boost guideline: if `laravel/boost` is not installed and this card changes application code, STOP and ask permission to run `composer require laravel/boost --dev && php artisan boost:install`, then re-read AGENTS.md.
D. SAFETY — no migrate:fresh / db:wipe / TRUNCATE on dev or production; no production credentials locally; no real KYC documents; no mobile build pointing at production.
E. QUALITY TOOLS — PHPStan level 6 + Larastan zero baseline, Pint --test, php artisan test, ESLint/tsc/build, flutter analyze, gitleaks/Trivy available (or explicitly created within the card).
If ANY item FAILS: stop, list failures with proposed fixes, and ask me for a decision. Do not code.

STEP 5 — START (only if all gates PASS)
- Paste the matching WP prompt block from this file Part 2 with the exact card ID.
- Implement ONLY this card. One card = one commit. Never merge cards.
- Use DECIMAL(18,4) money, immutable ledger entries, atomic transactions, Asia/Dhaka T+2, permission/KYC/suspension checks; no client-side money math (MON-01..08, WAL, PNL, COM).
- Never invent defaults (GOV-01); never skip tests (TST-12); never exceed the card scope.
- Verify with the exact commands in the card plus the global gate; show command output before claiming done (GOV-09).
- Close with fp-task-closeout: update CURRENT_SNAPSHOT, next card in BUILD_QUEUE, ENTRY LOG (last 4), rotate older details to docs/State_Ledger_Archive.md, and confirm the module health widget (OPS-11).
- End with: what changed, evidence, the new active card, and any open issue.

HARD STOPS (always):
- No code before Step 3 confirmation AND Step 4 all-PASS.
- No full docs/Build_Queue_Detail.md load; no all-rules load.
- No destructive DB commands on dev/production; corrections only via reversal entries.
- No "done" without command output.
- No deferred item (LOCK-11) without a Decision + feature flag.
```

---

# PART 1 — PROTOCOL (supporting blocks; Part 0 above is the entry point)

## SESSION BOOTSTRAP — paste verbatim

```
CONSTITUTION ACKNOWLEDGED
Task card: [WPn-Tnn — copy from docs/Build_Queue_Detail.md]
Task: [card title + name]
I have read docs/DOCS_MANIFEST.md, docs/State_Ledger.md (BUILD_QUEUE summary), docs/EXECUTION_LOCKS_ACTIVE.md,
docs/Feature_Flags_Registry.md, docs/Constitution.md, the one card in docs/Build_Queue_Detail.md, and the rule module(s) it names:
  [rules/money.md / rules/backend.md / rules/security.md / rules/testing.md /
   rules/frontend.md / rules/mobile.md / rules/reporting.md / rules/operations.md /
   rules/documentation.md] in full.
Last STATE_LEDGER entry: [pull actual latest entry code + date — do not guess]
Rules that apply: [copy rule codes from the card]
Schema tables touched: [copy from the card]
I will not write code until the above is confirmed accurate.
```

## PROMPT CONTRACT — applies to every task card

1. Open `docs/State_Ledger.md` §BUILD_QUEUE (summary only) and find the next work package.
2. Open **exactly one** card in `docs/Build_Queue_Detail.md` (OPS-10). Never load the whole detail file.
3. Paste the SESSION BOOTSTRAP with the real card ID and rule codes.
4. Paste the matching **WP prompt block** from Part 2 below.
5. Implement only that card. One card = one commit.
6. Verify with the exact commands in the card + the global gate (`fp-local-verification-gate`).
7. Close with `fp-task-closeout`: update snapshot, queue, entry log (last 4), archive rotated details.
8. Confirm the module health widget for that work package is visible if the module is now DONE (OPS-11 / REP-09).

If this file and `docs/State_Ledger.md` disagree, the ledger wins. If policy conflicts, `docs/EXECUTION_LOCKS_ACTIVE.md` wins.

---

# PART 2 — BUILD QUEUE PROMPTS (one block per work package)

| WP | Work package | Prompt block | Rule modules | DONE widget |
|---|---|---|---|---|
| WP1 | Laravel 13 + Filament 5 bootstrap | WP1 below | backend, operations | Config Health (WP9-T06) |
| WP2 | Schema + ledger migrations | WP2 below | money, backend, testing | Ledger Reconciliation (WP9-T02) |
| WP3 | Auth + RBAC | WP3 below | security, backend | Security & Audit (WP9-T05) |
| WP4 | Wallet engine | WP4 below | money, backend, testing | Money Flow / Approvals (WP9-T04) |
| WP5 | Investment + P&L | WP5 below | money, backend, testing | P&L Distribution (WP9-T03) |
| WP6 | KYC workflow | WP6 below | security, reporting | KYC Review Queue (WP9-T05) |
| WP7 | MLM commissions | WP7 below | money, reporting | Agent & Commission (WP9-T06) |
| WP8 | Web + mobile | WP8 below | frontend, mobile | Overview tab (WP9-T01) |
| WP9 | Admin observability + Reports | WP9 below | reporting, operations | `fp-admin-report-health` |
| WP10 | Database seeders & demo fixtures | WP10 below | testing, money, security | All widgets (data layer) |
| WP11 | Repository & environment setup | WP11 below | testing, operations | none (setup only) |

## WP1 — Bootstrap prompt

```
TASK CARD: [WP1-T01..WP1-T07 — paste the exact card from docs/Build_Queue_Detail.md]
After SESSION BOOTSTRAP, implement only this card.
Rules: COD-01..10, DB-01..08, API-01, FIL-01..05, TST-12.
Deliver: the files named in the card only; no deferred items (LOCK-11).
Verify: php artisan --version = 13.x; /health JSON; /admin loads;
  php artisan permissions:sync --check green;
  php vendor/bin/phpstan analyse --memory-limit=2G (level 6, zero baseline);
  php vendor/bin/pint --test; php artisan test.
Closeout: fp-task-closeout + note Config Health widget dependency (WP9-T06).
```

## WP2 — Schema/ledger prompt

```
TASK CARD: [WP2-T01..WP2-T11]
After SESSION BOOTSTRAP, implement only this card.
Rules: MON-01/02/03/08, DB-01..08, TST-03/04/05/11.
Use docs/Schema_Registry.md exactly: DECIMAL(18,4) USD, (12,4) rate, (10,6) ratios; append-only ledger/distribution/commission models; one table per migration with down().
NEVER run migrate:fresh/db:wipe/TRUNCATE on dev or production — only the test suite's RefreshDatabase.
Verify: fresh test DB migration; Schema::getColumnListing(); guard tests for no float columns, all FK indexes, append-only models; php artisan test.
Closeout: fp-task-closeout + confirm Ledger Reconciliation widget dependency (WP9-T02).
```

## WP3 — Auth/RBAC prompt

```
TASK CARD: [WP3-T01..WP3-T10]
After SESSION BOOTSTRAP, implement only this card.
Rules: SEC-01..07, FIL-02, DB-08, TST-03, API-02, REP-08.
WP3-T08..T10 are BLOCKED on Q-005/Q-006/Q-007 — do not implement until the owner decision is recorded in docs/State_Ledger.md.
Permission matrix from docs/plan.md §1; deny by default; can() never hasRole(); KYC + suspension checked in service layer.
Sanctum cookie for SPA (SESSION_DOMAIN=.example.com, supports_credentials, withXSRFToken); Bearer for mobile in secure storage.
Verify: full role matrix feature test; KYC/suspension blocks invest/withdraw; audit rows written for staff actions.
Closeout: fp-task-closeout + Security & Audit widget dependency (WP9-T05).
```

## WP4 — Wallet engine prompt

```
TASK CARD: [WP4-T01..WP4-T09]
After SESSION BOOTSTRAP, implement only this card.
Rules: MON-01..08, TME-04, WAL-01..06, DB-06, API-06, QUE-05, TST-03/04/05.
Money flows: single DB transaction + lockForUpdate; wallet_transactions row + balance update atomically;
largest-remainder allocation; no negative wallets; capital release only via approval + 24h.
Verify: ledger:reconcile command returns zero breaks; tests for deposit, withdrawal, transfer, credit purchase, agent transfer, concurrent double-spend, reversal.
Closeout: fp-task-closeout + Money Flow / Approval widget dependency (WP9-T04).
```

## WP5 — Investment & P&L prompt

```
TASK CARD: [WP5-T01..WP5-T07]
After SESSION BOOTSTRAP, implement only this card.
Rules: PNL-01..07, TME-01..04, MON-01/05/08, QUE-01..05, TST-08, REP-02.
Eligibility in Asia/Dhaka: second business day; Fri/Sat/Sun → Tuesday; ongoing until withdrawal.
Distribution: one per symbol + trade date; split by current capital; profit → profit wallet, loss → position capital; largest remainder so items sum exactly.
Verify: proportional accuracy, weekend fixture, reversal via compensating entry, concurrent run, no double distribution.
Closeout: fp-task-closeout + P&L Distribution widget dependency (WP9-T03).
```

## WP6 — KYC prompt

```
TASK CARD: [WP6-T01..WP6-T05]
After SESSION BOOTSTRAP, implement only this card.
Rules: SEC-02/06/07, REP-05, FIL-01/02, TST-03.
Admin-configurable document checklist per user type; uploads via signed R2 URL; reviewer approve/reject with reason + resubmit; service-layer gate on invest/withdraw.
Verify: pending user blocked; no local disk writes; notification events queued; audit log written.
Closeout: fp-task-closeout + KYC Review Queue widget dependency (WP9-T05).
```

## WP7 — MLM commissions prompt

```
TASK CARD: [WP7-T01..WP7-T10]
After SESSION BOOTSTRAP, implement only this card.
Rules: COM-01..08, MON-01/05/08, REP-01, TST-09.
Tier labels Seed/Growth/Master Investor (display only, tier integer 1/2/3); tier×level matrix; platform cost; role bonus separate; deductions = loss_adjustment/reversal/admin_adjustment; vesting + clawback.
Verify: each tier/level, net base after deductions, role-bonus isolation, vesting timing, cancellation on early withdrawal, downline report role-gated.
Closeout: fp-task-closeout + Agent & Commission widget dependency (WP9-T06).
```

## WP8 — Web + mobile prompt

```
TASK CARD: [WP8-T01..WP8-T15 — state whether WEB or MOBILE]
After SESSION BOOTSTRAP, implement only this card.
WEB rules: FE-01..10, REP-02, SEC-04/05 — TanStack Query, RHF+Zod, decimal strings, withCredentials+withXSRFToken, no client money math, VITE_ public only.
MOBILE rules: MOB-01..13, TST-12 — FVM flavors, Riverpod, go_router, Dio, Freezed, secure storage, R2 via API, FCM staging only.
Verify: WEB = tsc --noEmit + eslint --max-warnings 0 + npm run build; MOBILE = fvm flutter analyze + fvm flutter test + fp-mobile-test-track (ngrok for device).
Closeout: fp-task-closeout + Overview tab dependency (WP9-T01).
```

## WP9 — Admin observability / Report section prompt

```
TASK CARD: [WP9-T01..WP9-T08]
After SESSION BOOTSTRAP, implement only this card.
Rules: REP-09..11, OPS-11, FIL-04, GOV-09, docs/Admin_Observability_Spec.md.
Build read-only Filament widgets with green/amber/red state, timestamp, metric, owner, drill-down; Support view-only.
NEVER add destructive actions to a widget; corrections go through the proper module action.
Verify: widget renders with real data; state thresholds from the catalogue; drill-down opens correct resource.
Final card WP9-T08: run fp-admin-report-health — 12 widgets + 8 tabs + 11-point checklist; verdict must be HEALTHY before claiming project complete.
Closeout: fp-task-closeout + final State_Ledger entry with evidence.
```

## WP10 — Database seeders prompt

```
TASK CARD: [WP10-T01..WP10-T08]
After SESSION BOOTSTRAP, implement only this card.
Rules: TST-13, MON-01..08, DB-01..08, SEC-01/06, FIL-02, OPS-01, GOV-09.
Every feature seeder creates the minimum data needed to exercise that feature; DatabaseSeeder orchestrates all feature seeders.
Seeders must be idempotent and succeed on a fresh migrated test DB; money seeders insert wallet_transactions rows so credits−debits=balance.
NEVER run bare db:seed, migrate:fresh, db:wipe or TRUNCATE on dev/production — invoke only php artisan db:seed --class=<Name>Seeder.
Verify: php artisan db:seed --class=DatabaseSeeder on a fresh test DB; seeder assertions in the feature suite; php artisan test.
Closeout: fp-task-closeout + note which widgets now have data (OPS-11).
```

## WP11 — Repository & environment setup prompt

```
TASK CARD: [WP11-T01..WP11-T05 — paste the exact card from docs/Build_Queue_Detail.md]
After SESSION BOOTSTRAP, implement only this card.
Rules: TST-12, OPS-01, COD-01, GOV-01, GOV-07, GOV-09.
This package prepares the local toolchain and git repository ONLY — no application code, no business policy, no schema tables, no money/KYC/commission path.
Never invent a remote URL, branch policy or commit identity; require an owner decision for values not already recorded (GOV-01). GitHub account `arhariz` owns the frontend + backend repos (D-051) but is a different-project context — confirm commit identity; NEVER touch files of any other project (D-051).
Verify: C: free > 2 GB (Q-008); git work tree + initial commit with confirmed author; gitleaks/trivy/semgrep versions; laravel/boost installed + AGENTS.md re-read.
Closeout: fp-task-closeout + re-run the full pre-check gate (A–E all PASS) before unblocking WP1-T07.
```

---

# PART 3 — REFERENCE PATTERNS (optional, use only when a card asks)

## P-001 — Implement a locked module

```
After SESSION BOOTSTRAP, implement module: [NAME / SECTION NUMBER].
Scope: [locked plan section + exact files].
Rules: apply the rule codes named in the ACK block.
Use DECIMAL(18,4) money, ledger entries, atomic transactions, Asia/Dhaka dates, permission checks from plan §1.
Deliver: migrations, models, services, tests (rollback/failure/reconciliation), Filament resources if applicable.
Do not implement deferred items from PLAN §4 / LOCK-11. Close via fp-task-closeout.
```

## P-002 — Create a migration + model

```
Use docs/Schema_Registry.md exactly.
Money: DECIMAL(18,4) USD / (12,4) rate / (10,6) ratios — no floats; largest remainder (MON-08).
Soft deletes only where registry allows; ledger/distribution/commission tables append-only.
Include relationships + factory + feature test for CRUD/validation. Register in docs/State_Ledger.md.
```

## P-003 — Implement a money movement

```
Apply MON-01..08, TME-02/03, DB-06, API-06, SEC-02/03, TST-03/04/05.
Single DB transaction + lockForUpdate; wallet_transactions row + balance update atomically;
current exchange_rates row; permission + KYC/suspension checks; immutable ledger entry; notification event.
Tests: success, insufficient balance, invalid KYC, suspension, concurrent double-spend, reversal.
```

## P-004 — Implement the P&L distribution job

```
Apply MON-01..08, TME-02/03, PNL-01..07, QUE-01..05, TST-08.
DistributePnl(symbol, trade_date): eligible positions by Asia/Dhaka T+2; share = current capital / total;
largest-remainder allocation; profit → profit wallet, loss → position capital; items sum exactly to posted P&L.
Idempotent, reversible via reversal_of_id, audited. Tests: proportional, weekend, reversal, concurrent.
```

## P-005 — Implement a commission rule

```
Apply COM-01..08, MON-01/05/08, TST-09.
Events: investment (incl. profit reinvest), profit_distribution, credit_purchase. Platform cost; role bonus separate.
Base = net credited after deduction_profile (loss_adjustment / reversal / admin_adjustment).
Pending commission with vest_at; clawback on early withdrawal. Tests: tier/level, deductions, vesting, cancellation.
```

## P-006 — Build a Filament resource

```
Apply FIL-01..05, SEC-01, docs/plan.md §1 permission matrix.
CRUD, filters, status badges, export where relevant; every action gated by policy; Support view-only.
If the module is DONE, add/verify its health widget (REP-09, OPS-11).
```

## P-007 — Build a React web feature

```
Apply FE-01..10. React 19.3 + Vite 8 + TS 7 + Tailwind 4 + shadcn/ui.
TanStack Query for server state, Zustand for token/UI only, RHF + Zod for forms.
Sanctum cookie (withCredentials + withXSRFToken). Decimal strings only; no money math in client.
Loading/empty/error/denied states; VITE_ public only. Verify tsc + eslint + build.
```

## P-008 — Build a Flutter mobile feature

```
Apply MOB-01..13. Flutter 3.50 FVM, Riverpod, go_router, Dio, Freezed, material_ui/cupertino_ui.
Sanctum Bearer in flutter_secure_storage; R2 uploads via API; FCM staging only; dev flavor via ngrok.
No money math in Dart; exclude admin approvals + biometric. Verify flutter analyze + widget/integration tests.
```

## P-009 — Write tests for financial logic

```
Apply TST-01..12 and MON-01..08.
Success, boundary (0/min/max), insufficient funds, invalid KYC, suspended user, duplicate/idempotency,
reversal/rollback, concurrency. Assert credits − debits = balance after every scenario (TST-04).
DECIMAL-safe assertions (TST-05).
```

## P-010 — Code review against the Constitution

```
Review [FILE/FEATURE] against the rule codes in the ACK block.
Report violations with file/line, severity and concrete fix. Watch for float money, direct balance writes,
missing ledger entries, missing permission/KYC checks, non-atomic flows, client-side business logic.
Do not change code — report only.
```

## P-011 — Close out a task

```
Run fp-task-closeout: update docs/State_Ledger.md CURRENT_SNAPSHOT, BUILD_QUEUE, ENTRY LOG (last 4),
resolve related Known Issues, note new deferred items; rotate older entries to docs/State_Ledger_Archive.md (GOV-08).
If schema changed, verify docs/Schema_Registry.md. If module DONE, verify widget visible (OPS-11).
```

## P-012 — Debug a production-style issue

```
Given symptom: [DESCRIPTION]. Use systematic-debugging + gl-recon → break-trace.
Diagnose with logs, ledger reconciliation query, queue status.
Fix via compensating ledger entries only — never edit ledger rows. Add regression test; update docs/State_Ledger.md.
```

## P-013 — Local verification gate

```
Apply OPS-01..11, GOV-08/09, TST-12. Verify on localhost only:
1. Fresh test DB migration + seeders (NEVER migrate:fresh on dev/prod).
2. php artisan test — reconciliation asserted after every money scenario.
3. php vendor/bin/phpstan analyse --memory-limit=2G (level 6, Larastan, zero baseline); php vendor/bin/pint --test — STOP on error.
4. npx tsc --noEmit; npx eslint . --max-warnings 0; npm run build; fvm flutter analyze + flutter test.
5. gitleaks detect; trivy fs .; Semgrep — no secrets/critical CVEs.
6. Manual money-path E2E with synthetic data only.
7. No production credentials, no real KYC docs, no prod-pointing mobile build.
Report PASS/FAIL per gate with evidence. Do not deploy until all gates pass.
```

## P-014 — Mobile test track

```
Apply MOB-13, OPS-01, docs/plan.md §6. Dev flavor → localhost via ngrok; never production.
Unit tests for pure logic, widget tests for states, integration test for happy path.
Emulator/simulator + physical-device checklist. FCM staging only; token in flutter_secure_storage.
Report passed checks + remaining physical-device validation.
```

## P-015 — Report-section health

```
Run fp-admin-report-health with docs/Admin_Observability_Spec.md.
Verify 8 tabs + 12 widgets, green/amber/red with owner/drill-down, 11-point final checklist.
RED blocks completion; AMBER needs a CTO decision in docs/State_Ledger.md.
End with HEALTHY or NOT HEALTHY + top 3 actions. No verbal "it works" (GOV-09).
```
