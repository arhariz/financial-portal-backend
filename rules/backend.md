# rules/backend.md — COD, DB, QUE, API, FIL rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: COD-01..10, DB-01..08, QUE-01..05, API-01..06, FIL-01..05
# Does NOT contain: money policy, security policy, testing, frontend, mobile, operations
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

## SECTION 1 — UNIVERSAL CODING (COD)

COD-01 | Every PHP file | `declare(strict_types=1);` | head -1 file.php
COD-02 | Every method | Full parameter type hints | PHPStan **level 6**
COD-03 | Every method | Explicit return types | PHPStan **level 6**
COD-04 | All PHP | PSR-12 via Pint | `php vendor/bin/pint --test` (CI + pre-commit)
COD-05 | Controller action | Business logic only in Service/Action; controller orchestrates | grep `DB::` in controllers = 0
COD-06 | Database access | All queries through Repository/Model; deep modules, small interfaces | code review
COD-07 | Async work | `ShouldQueue` jobs; never `dispatchSync()` in production | grep `dispatchSync`
COD-08 | External service call | try/catch; failure must not break main flow; log sanitized payload | service test
COD-09 | Namespace/class change | Verify with tinker; `optimize:clear` + `dump-autoload` + restart worker | commands run
COD-10 | Committed code | No `dd()`/`dump()`; no secrets; Telescope/DebugBar local only | grep + config check

## SECTION 2 — DATABASE (DB)

DB-01 | Money columns | `DECIMAL(18,4)` USD (display 2dp), `DECIMAL(12,4)` rate, `DECIMAL(10,6)` ratios — never FLOAT | schema inspection
DB-02 | Ledger/distribution/commission models | Append-only; no `updated_at` writes; corrections are reversal rows | model + guard test
DB-03 | Migration | One table per migration; always provide `down()` | migration review
DB-04 | Indexes | All FKs, status fields and date filters (`trade_date`, `created_at`) indexed | `SHOW INDEX`
DB-05 | Before inserts | `Schema::getColumnListing()` to verify columns | tinker
DB-06 | Balance/number changes | `DB::transaction()` + `lockForUpdate()` | service code + concurrency test
DB-07 | Business rows | No hard delete while referenced; deactivate instead; RESTRICT FKs | `information_schema`
DB-08 | Enums/status | PHP-backed enum validated against `docs/Schema_Registry.md`, never free strings | enum + validation test

## SECTION 3 — QUEUES (QUE)

QUE-01 | Queue jobs | Named queues only; set via `$this->onQueue()`, never a public `$queue` property | job classes
QUE-02 | Job failure | 3 tries, exponential backoff, logged to Horizon | `$tries` + config
QUE-03 | Failed job | Writes an audit entry and alerts; never silently dropped | failed() handler test
QUE-04 | Job payload | No floats; pass IDs and decimal strings only | job test
QUE-05 | Idempotency | Unique key on every money-posting job; retry cannot double-credit | concurrency test

## SECTION 4 — API (API)

API-01 | Routes | Versioned REST under `/api/v1`; JSON resource classes only | `route:list`
API-02 | Writes | FormRequest with authorize() checking role + KYC + suspension | feature test
API-03 | Responses | JSON resources; never raw Eloquent models | resource test
API-04 | Errors | Consistent envelope `{ message, errors, code }`; no stack traces | error test
API-05 | List endpoints | Server-side pagination, max 100; whitelisted filters | endpoint test
API-06 | Money endpoints | Accept `Idempotency-Key`; duplicate returns existing result | idempotency test

## SECTION 5 — FILAMENT 5 (FIL)

FIL-01 | Resources | One resource per `docs/Schema_Registry.md` table; every model has a policy | resource + policy test
FIL-02 | Permissions | `can('permission')`, never `hasRole()`; matrix from `docs/plan.md` §1 | `NoHasRoleRule` + policy test
FIL-03 | Money bulk actions | Require confirmation + audit reason | action test
FIL-04 | Dashboard widgets | Driven by `dashboard_layouts` (per-role default + per-user) | widget test
FIL-05 | Ledger tables | No delete actions exposed; corrections via reversal entries | resource review

---

## MACHINE-ENFORCED VERIFICATION (planned, then mandatory in CI)

- **PHPStan exactly level 6** with Larastan, zero-baseline (`phpstan-baseline.neon` stays empty). Raising the level requires a new Decision. Command: `php vendor/bin/phpstan analyse --memory-limit=2G`.
- Custom PHPStan rules: no float money columns; no direct `Wallet::update(['balance' => ...])`; no `UPDATE`/`DELETE` on ledger/distribution/commission models; no `hasRole()` (use `can()`).
- Guard tests: permission matrix smoke test, append-only model test, decimal-column schema test, KYC/suspension gate test, route/idempotency contract test.
- Zero-baseline doctrine: PHPStan/Larastan baseline must stay empty; no new suppression without a Decision.
- CI fails on any violation — "judgement only" rules should shrink over time.

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
