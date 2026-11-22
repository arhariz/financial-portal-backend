# rules/testing.md — TST rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-042)
# Source of truth for: TST-01..13
# Does NOT contain: business rules, schema, deployment procedure
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

TST-01 | Money path | TDD: failing test first, red → green → refactor | test before code
TST-02 | Test pyramid | Unit → feature/integration → E2E (Playwright) → manual `fp-local-verification-gate` | suite structure
TST-03 | Every money change | Success, insufficient funds, invalid KYC, suspended user, unauthorized role, invalid scale, duplicate, concurrent double-spend, DB rollback, reversal | parameterized tests
TST-04 | After every money scenario | Assert `sum(credits) − sum(debits) = balance`; distribution items sum to entered amount | reconciliation assertion
TST-05 | Amount assertions | DECIMAL-safe (string/integer), never float equality | assertion review
TST-06 | Test isolation | `RefreshDatabase` for test DB only; no shared mutable state | fresh run twice
TST-07 | External services | Fake Brevo, R2, FCM; use fakes over mocks for owned code | no network in tests
TST-08 | Distribution | Proportional accuracy, weekend eligibility, ongoing eligibility, loss to capital, profit to wallet, reversal, concurrent run | feature tests
TST-09 | Commission | Each tier/level, deduction profile, vesting, clawback, role-bonus isolation | feature tests
TST-10 | Queues | Idempotency key, retry count, failed-job audit, no double credit | queue tests
TST-11 | Bug fix | Add a regression test before closing | regression test present
TST-12 | CI gates | `php artisan test`; **PHPStan level 6 + Larastan, zero baseline, `--memory-limit=2G`**; `php vendor/bin/pint --test`; `npx tsc --noEmit`; `npx eslint . --max-warnings 0`; `npm run build`; `fvm flutter analyze`; `fvm flutter test`; gitleaks + Trivy/Semgrep | CI green + command output |
TST-13 | Seeder coverage | **Every feature ships a seeder that creates the minimum data needed to exercise it.** `DatabaseSeeder` orchestrates every feature seeder; each seeder is idempotent and succeeds against a fresh migrated database; invoke only as `php artisan db:seed --class=<Name>Seeder` (bare `db:seed` is a career-ending command); seeders run on local/staging only and never against production | `php artisan db:seed --class=DatabaseSeeder` on a fresh test DB; seeder assertions in the feature suite |

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
