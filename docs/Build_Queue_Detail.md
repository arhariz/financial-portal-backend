# docs/Build_Queue_Detail.md — Task cards for the live BUILD_QUEUE
# Authority: Owner / CTO
# Last Updated: 2026-11-23 (D-062)
# Source of truth for: granular implementation tasks, dependencies, verification
# Does NOT contain: executed state (docs/State_Ledger.md), code rules (rules/*.md), business policy (docs/EXECUTION_LOCKS_ACTIVE.md)
# Conflict rule: STATE_LEDGER wins for current queue position; this file defines HOW each item is split.
# Status: ACTIVE

---

## HOW TO USE

1. Read the summary `BUILD_QUEUE` in `docs/State_Ledger.md` to find the next work package.
2. Open **only the matching task card** below — do not load the whole file into context.
3. Paste `SESSION BOOTSTRAP` from `docs/Prompt_Library.md` with this card's ID, then paste the **matching WP prompt block** (WP1…WP9) from Part 2 of `docs/Prompt_Library.md`.
4. One card = one commit. Do not merge two cards into one implementation step.
5. Update `docs/State_Ledger.md` snapshot/queue and mark the card DONE in its table.

Each card obeys GOV-05 (no arbitrary size limits) and TST-12 (exact CI commands).

---

## WP1 — Laravel 13 bootstrap + Filament 5 skeleton (7 tasks)

> **Progress (2026-11-22, D-050):** WP1-T01…WP1-T06 **DONE** — Laravel 13.43.0 + `/health`; Filament 5.11.4 at `/admin`; Spatie RBAC 30 permissions / 12 roles; PHPStan L6 zero-baseline + Pint PSR-12, **18 tests green**. WP1-T07 **DONE (D-057)** — `.github/workflows/ci.yml` added; YAML valid; test/PHPStan/Pint all exit 0. **WP1 COMPLETE.** Next card: **WP2-T01**.

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP1-T01 | Create Laravel 13 project | `composer.json`, `.env`, `bootstrap/app.php`, `routes/web.php`, health route | plan §3, D-024 | GOV-01, COD-01..04 | `php artisan --version` = 13.x; `/health` returns JSON |
| WP1-T02 | Install Filament 5 admin panel | `app/Providers/Filament/AdminPanelProvider.php`, `app/Models/User.php`, `config/filament.php` | plan §1 | FIL-01..05 | `/admin` loads; `FilamentUser` implemented |
| WP1-T03 | Install RBAC + audit packages | `app/Permissions/Manifest.php`, `config/role_matrix.php`, migrations from Spatie | plan §1, D-001 | FIL-02, SEC-07 | `permissions:sync --check` green |
| WP1-T04 | Configure PHPStan 6 + Pint + tests | `phpstan.neon`, `pint.json`, `phpunit.xml`/`pest.php`, `tests/` | D-032 | COD-02/03/04, TST-12 | `phpstan analyse --memory-limit=2G` 0 errors; `pint --test` pass |
| WP1-T05 | Sanctum + API skeleton | `routes/api.php`, `config/cors.php`, `config/sanctum.php`, `/api/v1/health`, `tests/Feature/ApiSkeletonTest.php` | D-022 | API-01, SEC-04/05 | auth:sanctum route responds; CORS config explicit; 3 tests green; PHPStan 0, Pint pass |
| WP1-T06 | App layer structure | `app/Actions`, `app/Repositories`, `app/Enums`, `app/Services`, test folders | rules/backend.md | COD-05/06 | namespace autoload verified with tinker |
| WP1-T07 | CI workflow skeleton **(DONE, D-057)** | `.github/workflows/ci.yml` — PHP 8.3, composer install, key generate, then test + PHPStan L6 + Pint; SQLite in-memory | D-032, D-057 | TST-12 | YAML parses (1 job, 8 steps) ✅; `php artisan test` 18/18 ✅; PHPStan 0 errors ✅; Pint pass ✅ |

## WP2 — Schema + ledger migrations (11 tasks)

> **Progress (2026-11-23, D-075): WP2 COMPLETE (T01–T11 DONE)** — identity + KYC + wallet/ledger + exchange-rate + money-request + investment + distribution + agent + commission + notification/dashboard/audit schema + global guard suite. `getColumnListing()` + MySQL `information_schema` verified on dev DB; append-only ledger + commission + audit guards active; missing `wallet_transactions.created_by` index fixed. Ledger Health widget verified with data (OPS-11). 105 tests / 952 assertions, PHPStan 0, Pint pass. Next card: **WP3-T01**.

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP2-T01 | Identity tables **(DONE, D-058)** | 2 migrations (`add_identity_columns_to_users_table`, `create_user_profiles_table`), enums `UserStatus`/`KycStatus`, `User` casts + SoftDeletes, `UserFactory` defaults, `tests/Feature/IdentitySchemaTest.php` (6 tests) | Schema_Registry §2, D-058 | DB-01..08, COD-01..04, TST-12 | `getColumnListing()` dev DB ✅; 24 tests / 160 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T02 | KYC tables **(DONE, D-059)** | 2 migrations (`create_kyc_document_types_table`, `create_kyc_documents_table`), enums `KycUserType`/`KycDocumentStatus`, `tests/Feature/KycTablesTest.php` (5 tests) | Schema §2, D-004, D-059 | DB-02/04/08, COD-01..04, TST-12 | `getColumnListing()` dev DB ✅; CRUD + review-flow smoke tests ✅; FK/status indexes ✅; 29 tests/197 assertions, PHPStan 0, Pint pass ✅ |
| WP2-T03 | Wallets + immutable ledger **(DONE, D-060)** | 2 migrations, enums `WalletType`/`TransactionDirection`/`WalletTransactionType`, models `Wallet`/`WalletTransaction` (+factories, append-only guards), `tests/Feature/WalletLedgerTest.php` (6 tests) | Schema §2/§3, D-029, D-060 | MON-01/02/03, WAL-01..06, DB-02/04, TST-04 | append-only guards throw ✅; MySQL `decimal(18,4)` ✅; reconciliation ✅; 35 tests/228 assertions, PHPStan 0, Pint pass ✅ |
| WP2-T04 | Exchange rates **(DONE, D-063)** | `exchange_rates` + model + factory + `tests/Feature/ExchangeRateSchemaTest.php` (6 tests) | Schema §2, D-063 | MON-01, DB-04 | `decimal(12,4)` ✅; 3 indexes on dev MySQL ✅; 41 tests/246 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T05 | Money request tables **(DONE, D-065)** | 3 migrations; enums `ManualDepositStatus`/`WithdrawalStatus`/`CapitalReleaseStatus`/`WithdrawalSourceWalletType`; models + factories; `tests/Feature/MoneyRequestSchemaTest.php` (6 tests) | Schema §2, D-065 | DB-01/04/08 | status enums ✅; `decimal(18,4)` + FK/status/date indexes on dev MySQL ✅; 47 tests/318 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T06 | Investment tables **(DONE, D-066)** | 2 migrations; enums `SymbolCategory`/`SymbolStatus`/`PositionStatus`/`PositionSource`; models + factories; `tests/Feature/InvestmentSchemaTest.php` (6 tests) | Schema §2, D-009/029, D-066 | PNL-01/02, DB-01 | `eligible_date DATE` + index ✅; `decimal(18,4)` on dev MySQL ✅; 53 tests/359 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T07 | Distribution tables **(DONE, D-067)** | 2 migrations; enum `PnlDistributionStatus`; models + factories; `tests/Feature/DistributionSchemaTest.php` (6 tests) | Schema §2, D-010/029, D-064, D-067 | PNL-04/05, MON-08 | signed `decimal(18,4)` + `decimal(10,6)` ✅; sum-to-posted (integer-scaled) ✅; all FK/date indexes on dev MySQL ✅; 59 tests/398 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T08 | Agent tables **(DONE, D-068)** | 2 migrations; `config/agent_tiers.php` (D-031 labels); models + factories; `tests/Feature/AgentSchemaTest.php` (6 tests) | Schema §2, D-031, D-068 | COM-01/08 | integer tier ✅; D-031 labels in config ✅; `decimal(18,4)` + indexes on dev MySQL ✅; 65 tests/432 assertions ✅; PHPStan 0 ✅; Pint pass ✅ |
| WP2-T09 | Commission tables **(DONE, D-069)** | `deduction_profiles`, `commission_rules`, `commissions` + `config/deduction_types.php`, 3 enums, models/factories, `tests/Feature/CommissionSchemaTest.php` (7 tests) | Schema §2, D-030 | COM-02..07 | 3 D-030 default profiles seeded idempotently ✅; append-only `Commission` ✅; `decimal(18,4)`/`decimal(10,6)` + all indexes verified on dev MySQL ✅; 7 new tests (63 assertions), suite 72/72 (495), PHPStan 0, Pint pass ✅ |
| WP2-T10 | Notification/dashboard/audit tables **(DONE, D-070)** | `notification_templates`, `notifications`, `notification_preferences`, `dashboard_layouts`, `audit_logs`, `login_history` + 3 enums + `config/notification_events.php` + models/factories + `tests/Feature/NotificationAuditSchemaTest.php` (9 tests) | Schema §2 | SEC-07, FIL-04 | audit append-only update/delete guards throw ✅; 5 locked event groups + mandatory security alerts ✅; all FK/status/date indexes + JSON columns verified on dev MySQL ✅; 9 new tests (90 assertions), suite 81/81 (585), PHPStan 0, Pint pass ✅ |
| WP2-T11 | Schema guard suite **(DONE, D-075)** | `tests/Unit/SchemaGuardTest.php` (6 tests) + additive FK-index migration | Schema_Registry | DB-01..08, TST-03/04 | zero float columns ✅; all FK indexes ✅ (found + fixed `wallet_transactions.created_by`); decimal money casts + enum statuses + append-only guards ✅; 6 new tests (320 assertions), suite 105/105 (952), PHPStan 0, Pint pass ✅ |

## WP3 — Auth + RBAC (10 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP3-T01 | User model + roles/status **(DONE, D-079)** | `app/Models/User.php`, casts, traits + `tests/Feature/UserModelTest.php` (6 tests) | plan §1/§2 | SEC-01, FIL-02, COD-02/03 | multi-role + enum + soft-delete + permission-based panel access ✅; 6 new tests (15 assertions), suite 114/114 (985), PHPStan 0, Pint pass ✅ |
| WP3-T02 | Registration + referral code **(DONE, D-080)** | `app/Http/Controllers/Auth/RegisterController.php`, `app/Http/Requests/RegisterRequest.php`, `app/Services/RegistrationService.php`, `routes/api.php`, `tests/Feature/RegistrationTest.php` (6 tests) | plan §2 | SEC-01/02, GOV-01 | valid code links downline at level 1 ✅; invalid code 422 ✅; investor role assigned ✅; duplicate/missing fields 422 ✅; 6 new tests (23 assertions), suite 120/120 (1008), PHPStan 0, Pint pass ✅ |
| WP3-T03 | Login/logout (cookie + Bearer) **(DONE, D-081)** | `app/Http/Controllers/Auth/AuthController.php`, `app/Http/Requests/LoginRequest.php`, `routes/api.php`, `tests/Feature/AuthModesTest.php` (5 tests) | D-022 | SEC-04/05 | Bearer login + token revoke ✅; cookie session login ✅; invalid creds 401 ✅; 5 tests (12 assertions), suite 125/125 (1020), PHPStan 0, Pint pass ✅ |
| WP3-T04 | Permission matrix + policies **(DONE, D-082)** | `config/role_matrix.php` verified by `tests/Feature/PermissionMatrixTest.php` (6 tests); UserPolicy reference pattern; remaining model policies ship with Filament resources (FIL-01) | plan §1 | FIL-02, SEC-01 | all 12 roles match declared permissions ✅; super_admin/admin all ✅; support view-only ✅; investor/agent tiers none ✅; unknown abilities denied ✅; deny-by-default ✅; 6 tests (393 assertions), suite 131/131 (1413), PHPStan 0, Pint pass ✅ |
| WP3-T05 | KYC + suspension guard service **(DONE, D-083)** | `app/Services/GuardService.php`, `app/Http/Middleware/EnsureMoneyActionAllowed.php`, bootstrap alias `money.action`, `tests/Feature/GuardServiceTest.php` (6 tests) | plan §2 | SEC-02/03 | KYC-approved + active required for invest/withdraw ✅; pending/rejected/none KYC blocked ✅; suspended blocked even with approved KYC ✅; 6 tests (14 assertions), suite 137/137 (1427), PHPStan 0, Pint pass ✅ |
| WP3-T06 | Login history + staff audit **(DONE, D-084)** | `app/Services/AuditService.php`, `app/Services/LoginHistoryService.php`, `app/Listeners/LogSuccessfulLogin.php`, `LogFailedLogin.php`, `LogLogout.php`, `app/Providers/AppServiceProvider.php`, `tests/Feature/AuditLogTest.php` (6 tests) | plan §8 | SEC-07 | actor/IP/UA recorded ✅; login history + logged_out_at ✅; append-only guards ✅; JSON round-trip ✅; 6 tests (31 assertions), suite 143/143 (1458), PHPStan 0, Pint pass ✅ |
| WP3-T07 | Filament user management | `app/Filament/Resources/Users/*`, `app/Policies/UserPolicy.php` | plan §1, FIL-01/02 | FIL-01, FIL-02, SEC-01 | users list/create/edit gated by `users.view`/`users.manage`; non-admin gets 403 |
| WP3-T08 | Role management **(blocked: Q-005)** | `app/Filament/Resources/Roles/*`, `app/Policies/RolePolicy.php` | Q-005, FIL-01/02 | FIL-01, FIL-02, SEC-01 | role CRUD gated by `roles.manage`; code-vs-runtime source-of-truth decision implemented |
| WP3-T09 | Per-user permission overrides **(DONE, D-087; Q-006 closed)** | `app/Filament/Resources/Users/Schemas/UserForm.php` (permissions multi-select), `app/Policies/PermissionPolicy.php`, `tests/Feature/PermissionOverrideTest.php` (6 tests) | Q-006, FIL-02 | FIL-02, SEC-01 | additive-only overrides ✅; grant extra perm ✅; cannot subtract role grant ✅; support/investor denied ✅; 6 tests (13 assertions), suite 160/160 (1496), PHPStan 0, Pint pass ✅ |
| WP3-T10 | User profile management **(DONE, D-088; Q-007 closed)** | `app/Models/UserProfile.php`, `UserProfileFactory`, `app/Filament/Resources/UserProfiles/*`, `app/Policies/UserProfilePolicy.php`, `app/Observers/UserProfileObserver.php`, `tests/Feature/UserProfileTest.php` (7 tests) | Schema §2 | FIL-01, SEC-06, SEC-07 | profile CRUD ✅; id_number masked in listings ✅; audit-logged on change ✅; view gated on users.view/kyc.view, manage on users.manage ✅; unique user_id ✅; 7 tests (10 assertions), suite 167/167 (1506), PHPStan 0, Pint pass ✅ |

## WP4 — Wallet engine (9 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP4-T01 | WalletService deposit posting **(DONE, D-085)** | `app/Services/WalletService.php` + `tests/Feature/WalletServiceTest.php` (5 tests) | Schema §3, D-005 | MON-02/04/05, WAL-01 | credit + ledger row + balance ✅; idempotent wallet ✅; 4dp precision ✅; accumulation ✅; non-positive rejection ✅; reconciliation holds ✅; 5 tests (18 assertions), suite 148/148 (1476), PHPStan 0, Pint pass ✅ |
| WP4-T02 | Exchange rate admin CRUD + history **(DONE, D-089)** | `app/Services/ExchangeRateService.php`, `app/Filament/Resources/ExchangeRates/*`, `app/Policies/ExchangeRatePolicy.php`, `tests/Feature/ExchangeRateTest.php` (6 tests) | D-005 | MON-01, DB-04 | append-only history ✅; admin list/create 200 ✅; support view-only ✅; investor denied ✅; 4dp storage ✅; current() latest effective ✅; 6 tests (11 assertions), suite 173/173 (1517), PHPStan 0, Pint pass ✅ |
| WP4-T03 | Manual deposit request flow **(DONE, D-090)** | `DepositController` (submit + signed R2 proof URL), `ManualDepositService` (idempotent submit/approve/reject), `ProofStorageService`, `r2` disk config, Filament `ManualDepositResource` + policy, additive idempotency-key migration, `tests/Feature/ManualDepositTest.php` (12 tests) | D-006, D-090 | MON-04, SEC-06, API-06, FIL-02/05 | signed R2 proof URL ✅; idempotent submit at admin rate ✅; approve credits USD atomically + immutable ledger row ✅; reject with reason ✅; queue gated on deposits.view/approve ✅; 12 tests (46 assertions), suite 185/185 (1564), PHPStan 0, Pint pass ✅ |
| WP4-T04 | Withdrawal request flow **(DONE, D-091)** | `WithdrawalController` + `StoreWithdrawalRequest` (request/idempotency), `WithdrawalService` (approve/reject/pay), `WalletService::withdraw` (atomic debit), Filament `WithdrawalRequests` resource + policy at `/admin/withdrawals`, additive idempotency-key migration, `tests/Feature/WithdrawalTest.php` (14 tests) | D-007 | WAL-03/05, MON-04 | KYC/suspension gate ✅; idempotent request ✅; approve records reviewer ✅; pay atomically debits wallet + immutable ledger row ✅; no double payment / no negative balance ✅; 14 tests (51 assertions), suite 199/199 (1616), PHPStan 0, Pint pass ✅ |
| WP4-T05 | Capital release + 24h job **(DONE, D-092)** | `CapitalReleaseController`/`StoreCapitalReleaseRequest` (request + idempotency), `CapitalReleaseService` (approve/reject/release), `ReleaseCapital` named-queue job, `WalletService::transfer` (atomic investment→cash, two immutable rows), Filament `CapitalReleaseRequests` resource + policy at `/admin/capital-releases`, idempotency + nullable `release_at` migrations, `tests/Feature/CapitalReleaseTest.php` (14 tests) | D-007, TME-04 | QUE-01..05, WAL-06 | 24h gate blocks early release ✅; approval sets release_at +24h and queues named job ✅; after gate two immutable ledger rows + cash credit ✅; double release no-op ✅; failed-job audit ✅; 14 tests (48 assertions), suite 213/213 (1665), PHPStan 0, Pint pass ✅ |
| WP4-T06 | Internal transfer service **(DONE, D-093)** | `TransferController`/`StoreTransferRequest`, `InternalTransferService` (locked direction whitelist + idempotency via new `internal_transfers` table), `EligibilityDateService` (Asia/Dhaka T+2), `WalletService::transfer` typed ledger rows, `Position` date casts, `tests/Feature/InternalTransferTest.php` (19 tests) | D-005/008 | WAL-02..05 | cash/profit/commission → cash/investment ✅; Investment→Cash blocked (WAL-06) ✅; new active position with T+2 eligible date on invest transfer ✅; weekend fixtures ✅; idempotent duplicate ✅; 19 tests (60 assertions), suite 232/232 (1725), PHPStan 0, Pint pass ✅ |
| WP4-T07 | Agent credit purchase + role bonus **(DONE, D-095; amended D-096)** | `AgentCreditController`/`StoreAgentCreditRequest` (admin-only, idempotent), `AgentCreditService` (BDT at current admin rate + **tier-configured bonus rate**), `agent_credit_purchases` + `agent_credit_bonus_rules` tables, Filament `/admin/agent-credits` read-only + `/admin/agent-credit-bonus-rules` settings (settings.manage), `tests/Feature/AgentCreditTest.php` (17 tests) | D-008, D-096 | COM-05, MON-02 | face value (cash `deposit`) + tier-rate bonus (commission `role_bonus`) immutable rows ✅; no rule → bonus 0 / one row ✅; tier selection + inactive rule + 6dp precision ✅; no upline/MLM row ✅; rate not hardcoded ✅; idempotent ✅; 17 tests (54 assertions), suite 249/249 (1779), PHPStan 0, Pint pass ✅ |
| WP4-T08 | Agent credit transfer to investor **(DONE, D-097)** | `AgentCreditTransferController`/`StoreAgentCreditTransferRequest`, `AgentCreditTransferService` (idempotent, cross-user `WalletService::transfer`), `agent_credit_transfers` table, Filament `/admin/agent-credit-transfers` read-only + policy, `tests/Feature/AgentCreditTransferTest.php` (10 tests) | D-008 | COM-05, MON-04 | investor cash credited + agent cash debited with two immutable rows ✅; no fee/commission/bonus ✅; KYC/suspension gates ✅; idempotent ✅; audited ✅; 10 tests (37 assertions), suite 259/259 (1816), PHPStan 0, Pint pass ✅ |
| WP4-T09 | Reconciliation command/report **(DONE, D-072; pulled forward per D-071)** | `php artisan ledger:reconcile` + `app/Services/LedgerReconciliationService.php` + `tests/Feature/LedgerReconcileCommandTest.php` (6 tests) | Schema §3 | MON-05, TST-04 | integer-scaled 4dp, no float drift ✅; break detection + non-zero exit ✅; `--json` shape ✅; dev DB `breakCount: 0` ✅; suite 87/87 (599), PHPStan 0, Pint pass ✅ |

## WP5 — Investment & P&L (7 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP5-T01 | Symbol admin CRUD | `SymbolResource`, model, status | plan §4 | PNL-01 | active/paused test |
| WP5-T02 | Position creation + eligibility date | `OpenPositionAction`, T+2 calculator | D-009, TME-02 | PNL-02/03 | weekend fixture test |
| WP5-T03 | Eligibility query scope | model scope + staff view | TME-02/03 | PNL-03 | eligible list correct |
| WP5-T04 | Daily P&L entry (draft) | `PnlDistributionResource`, FormRequest | plan §4 | PNL-04, MON-01 | one per symbol+date |
| WP5-T05 | Distribute job + largest remainder | queued job, items, wallet postings | D-010/029, D-064 | PNL-05/06, MON-08 | profit-wallet-first loss split + sum exact (D-064) |
| WP5-T06 | Reversal/correction | reversal action + permission | D-011, D-064 | PNL-07, MON-03 | compensating entries; profit wallet credited before capital (D-064) |
| WP5-T07 | P&L reports | investor/symbol/date report queries | plan §6 | REP-01/02 | filters + export test |

## WP6 — KYC workflow (5 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP6-T01 | Admin-config document checklist | `KycDocumentTypeResource`, per user type | D-004 | SEC-06, GOV-01 | investor vs agent lists |
| WP6-T02 | User KYC submission + R2 upload | upload FormRequest, signed URL | D-004 | SEC-06, MON-10 | file stored, no local disk |
| WP6-T03 | Reviewer queue approve/reject/resubmit | Filament page + service | D-004 | SEC-01/07 | reason stored, resubmit works |
| WP6-T04 | KYC gate wired to invest/withdraw | service-level check | D-004 | SEC-02 | pending user blocked |
| WP6-T05 | KYC notifications | templates + events | D-016 | REP-08 | email/in-app queued |

## WP7 — MLM commissions (10 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP7-T01 | Agent profile + tier labels | `AgentResource`, tier config | D-031 | COM-08 | Seed/Growth/Master labels only |
| WP7-T02 | Referral code + downline tree | signup hook, `referrals` writer | D-014 | COM-01 | agent+investor downline |
| WP7-T03 | Commission rules admin CRUD | tier × level matrix, depth, events | D-013 | COM-02/04 | matrix test |
| WP7-T04 | Deduction profiles | 3 default types + mandatory note | D-030 | COM-07 | new type needs Decision |
| WP7-T05 | Commission on new investment | event listener/job | D-012 | COM-02/03 | platform cost, no source deduction |
| WP7-T06 | Commission on profit distribution | net base calculation | D-013/030 | COM-04/07 | net after deductions |
| WP7-T07 | Vesting + wallet posting | holding-period job | D-014 | COM-06, QUE-05 | vest_at respected |
| WP7-T08 | Clawback on early withdrawal | cancel/reverse commission | D-014 | COM-06 | pending cancelled |
| WP7-T09 | Auto-promotion thresholds | config-driven rules | D-002 | COM-08 | threshold test |
| WP7-T10 | Downline + commission reports | Filament widgets + export | D-015 | REP-01/08 | role-gated reports |

## WP8 — Web portal + Flutter mobile (15 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP8-T01 | Web scaffold | Vite+React+TS+Tailwind+shadcn, `vercel.json` | D-021 | FE-01/02/03 | `npm run build` green |
| WP8-T02 | Web auth + API client | Axios config, Sanctum cookie flow | D-022 | FE-07, SEC-04 | login/logout works |
| WP8-T03 | Web dashboard + wallets | query hooks, combined total | plan §6 | FE-04/06 | wallets rendered |
| WP8-T04 | Web invest flow | form + API call | plan §4 | FE-06/08 | new position created |
| WP8-T05 | Web P&L history + chart | Recharts, filters | plan §6 | REP-02, FE-08 | chart matches API |
| WP8-T06 | Web withdrawal + capital release | request forms | D-007 | WAL-03/06 | two-step flow shown |
| WP8-T07 | Web KYC upload | file upload UI | D-004 | FE-07, SEC-06 | upload via signed URL |
| WP8-T08 | Web agent screens | credit purchase + downline | D-031 | COM-01/08 | agent flows work |
| WP8-T09 | Web reports + CSV export | tables + export | D-015 | REP-01/07 | role-gated |
| WP8-T10 | Flutter scaffold + flavors | FVM, `dev/staging/prod`, SPM iOS | D-021 | MOB-01..04 | `flutter analyze` clean |
| WP8-T11 | Flutter auth + secure token | Sanctum Bearer, secure storage | D-022 | MOB-05/10 | token persisted securely |
| WP8-T12 | Flutter investor screens | dashboard, invest, withdraw, KYC upload | D-018 | MOB-06/09 | integration happy path |
| WP8-T13 | Flutter agent screens | credit purchase, sell credit, downline | D-018/031 | COM-01/08 | agent happy path |
| WP8-T14 | Flutter FCM push | firebase_messaging per flavor | D-016 | MOB-12 | staging push received |
| WP8-T15 | Mobile test track + physical device | ngrok dev tunnel, device checklist | D-023 | MOB-13, OPS-01 | `fp-mobile-test-track` pass |

---

## WP9 — Admin observability & Report section (8 tasks)

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP9-T01 | Reports page group + Overview shell **(DONE, D-073; UI redesign D-077)** | `app/Filament/Pages/Reports.php`, redesigned `resources/views/filament/pages/reports.blade.php`, `AdminPanelProvider`, `tests/Feature/ReportsSectionTest.php` (7 tests) | Admin_Observability_Spec §Reports | REP-09, FIL-04 | 8 icon tabs with interactive switching + deep-link ✅; hero + widget grid + honest placeholders + polished drill-down ✅; super_admin/support 200, investor denied ✅; suite 108/108 (970), PHPStan 0, Pint pass ✅ |
| WP9-T02 | Ledger Reconciliation + Wallet widget **(DONE, D-074; UI corrected D-076)** | `app/Filament/Widgets/LedgerHealth.php` (StatsOverviewWidget) + `LedgerWalletsTable.php` (TableWidget) + `LedgerHealthSeeder` + `tests/Feature/LedgerHealthWidgetTest.php` (8 tests) | Schema §3, WP4-T09 | MON-05, REP-10 | Native Filament stat cards + wallet table with badges/drill-down ✅; GREEN with zero breaks on seeded dev data (`breakCount: 0`) ✅; AMBER/RED states + timestamp + owner ✅; 8 tests (24 assertions), suite 106/106 (960), PHPStan 0, Pint pass ✅ |
| WP9-T03 | Queue Health + P&L Distribution widgets | `QueueHealth.php`, `PnlStatus.php` | WP5-T05, QUE rules | QUE-03, PNL-04, REP-10 | zero failed jobs; latest date shown |
| WP9-T04 | Money Flow + Approval widgets **(DONE, D-099)** | `app/Filament/Widgets/MoneyFlow.php`, `ApprovalQueues.php`, `app/Services/MoneyFlowService.php`, dashboard + Reports Overview/Money Flow tabs, `tests/Feature/MoneyFlowWidgetTest.php` (7 tests) | WP4, REP-01 | WAL-03/06, REP-10 | live AUM/30d net flow from immutable ledger rows (integer-scaled 4dp) ✅; queue aging green/amber/red + drill-down ✅; reports.view gate + owner labels ✅; dev `state: green` (AUM 100.0000, breakCount 0) ✅; 7 tests (25 assertions), suite 266/266 (1841), PHPStan 0, Pint pass ✅ |
| WP9-T05 | KYC + Security/Audit widgets | `KycQueue.php`, `SecurityAudit.php` | WP6, SEC-07 | SEC-02/07, REP-10 | no overdue KYC; audit rows present |
| WP9-T06 | Agent/Commission + Config widgets | `CommissionHealth.php`, `ConfigHealth.php` | WP7, D-029/030/031 | COM-06/08, REP-10 | vesting + rate freshness shown |
| WP9-T07 | Notification + Storage/Backup widgets | `NotificationHealth.php`, `OpsHealth.php` | plan §7/§8, OPS-07 | REP-10, OPS-07 | failed delivery + backup age shown |
| WP9-T08 | Exports + final report sign-off | export actions, `fp-admin-report-health` run | Admin_Observability_Spec §Final sign-off | REP-11, OPS-11, GOV-09 | all 12 widgets green/accepted amber; verdict HEALTHY |

---

## WP10 — Database seeders & demo fixtures (12 tasks)

> **Progress (2026-11-23, D-062):** WP10-T01, WP10-T02 **DONE** — `DatabaseSeeder` + `RolePermissionSeeder` + `UserSeeder` (30 permissions / 12 roles / 13 users, idempotent). Owner clarified (D-062): **every feature ships a seeder with sample data to verify function**; seeder cards already queued stay in WP10 (not pulled forward), and **missing coverage was added as T09–T12** (user profiles, money requests, agents/referrals, notifications/audit). Next card: **WP10-T03** (blocked by WP2-T04/T06).

Every feature ships a seeder that creates the minimum data needed to exercise it (TST-13). Seeders are local/staging only, idempotent, and always invoked with `--class`.

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP10-T01 | Seeder orchestrator + RBAC seeder | `database/seeders/DatabaseSeeder.php`, `RolePermissionSeeder.php` | `app/Permissions/Manifest.php` | TST-13, FIL-02 | `db:seed --class=DatabaseSeeder` creates 30 permissions / 12 roles |
| WP10-T02 | User seeder | `UserSeeder.php` — one user per staff role, all 3 agent tiers, investors | plan §1, D-001/031 | TST-13, SEC-01 | every role has at least one seeded user |
| WP10-T03 | Market seeder | `SymbolSeeder.php`, `ExchangeRateSeeder.php` | Schema §2 | TST-13, MON-01 | active symbols + exchange-rate history |
| WP10-T04 | Wallet seeder | `WalletSeeder.php` — 4 wallets per user, funded by ledger rows | Schema §3, MON-02/04 | TST-13, TST-04 | credits−debits=balance for every wallet |
| WP10-T05 | KYC seeder | `KycSeeder.php` — document types per user type + pending/approved/rejected docs | Schema §2, D-004 | TST-13, SEC-06 | all three statuses present |
| WP10-T06 | Investment seeder | `PositionSeeder.php`, `PnlSeeder.php` — positions + distributions | Schema §2, D-009/010 | TST-13, PNL-01..05 | eligible dates correct; items sum to posted amount |
| WP10-T07 | Commission seeder | `CommissionSeeder.php` — deduction profiles, tier×level rules, commissions | Schema §2, D-030/031 | TST-13, COM-01..08 | matrix complete; pending + vested rows |
| WP10-T08 | Demo scenario + seed gate | `DemoScenarioSeeder.php`, verification/CI step | plan §6, D-041 | TST-13, OPS-01, GOV-09 | deposit→invest→distribute→withdraw→vest runnable end-to-end |
| WP10-T09 | User profile seeder | `UserProfileSeeder.php` — one profile per seeded user with sample full_name/address/city/country/dob; dev-only `id_number` | Schema §2 / WP2-T01, D-062 | TST-13, SEC-06/07 | every seeded user has a profile; sensitive fields dev-only |
| WP10-T10 | Money request seeder | `MoneyRequestSeeder.php` — `manual_deposits`, `withdrawal_requests`, `capital_release_requests` in each status + matching ledger rows | Schema §2 / WP2-T05, D-062 | TST-13, MON-04, WAL-06 | all statuses present; request→ledger links valid |
| WP10-T11 | Agent & referral seeder | `AgentSeeder.php` — agents for tier 1/2/3 users, unique referral codes, upline/downline rows | Schema §2 / WP2-T08, D-031, D-062 | TST-13, COM-01/08 | 3 tiers seeded; referral codes + downline links present |
| WP10-T12 | Notification / audit seeder | `NotificationSeeder.php` — templates, notifications, preferences, dashboard_layouts, audit_logs, login_history | Schema §2 / WP2-T10, D-062 | TST-13, SEC-07, FIL-04 | templates per event group; audit rows append-only |

---

## WP11 — Repository & environment setup (5 tasks)

> **Progress (2026-11-23, D-052):** WP11-T02 **DONE** — `git init` on `main`, local author `arhariz <arhariz.com@gmail.com>`, root commit `7f3f38f`; WP11-T05 remote **DONE** — private repo `https://github.com/arhariz/financial-portal-backend`, pushed `7f3f38f` + `b77fbe8` (CODEOWNERS + README). Branch policy **DONE (D-053)** — owner made repo PUBLIC; secret scanning + push protection enabled; branch protection on `main` verified (1 review, code-owner, no force-push/delete; owner bypass enabled due to single-owner self-approval rule). WP11-T01 **DONE (D-054)** — disk gate re-run green: `C:` 3.19 GB free > 2 GB; no cache deletion needed; Q-008 CLOSED. WP11-T03 **DONE (D-055)** — gitleaks 8.30.1 / Trivy 0.76.2 / Semgrep 1.186.0 on user PATH, all version commands exit 0; gate E satisfied. WP11-T04 **DONE (D-056)** — Boost v2.10.4 installed, `boost:install --guidelines` regenerated AGENTS.md/CLAUDE.md and re-read. **WP11 COMPLETE.** Next card: **WP1-T07**.

This package prepares the local toolchain and repository only: no application code, no business policy, no money/KYC/commission path, no schema tables. Owner values (remote URL, default branch, protection rules) are required before WP11-T05 — never invent them (GOV-01).

| ID | Task | Scope / files | Inputs | Rules | Verify / Done |
|---|---|---|---|---|---|
| WP11-T01 | Restore disk space on `C:` (Q-008) **(DONE, D-054)** | Gate verified green — `C:` 3.19 GB free > 2 GB; no cache deletion required (least-destructive). Q-008 closed. | Q-008, D-046, D-050, D-054 | OPS-01, GOV-09 | `C:` free > 2 GB ✅; pre-check gate C re-run green ✅ (PHP/Composer/Node/MySQL/Redis) |
| WP11-T02 | Initialize local Git repository **(DONE, D-052)** | `git init -b main`, local author `arhariz <arhariz.com@gmail.com>`, commit `7f3f38f` (650 files, no secrets) | D-050, D-051, D-052 (owner confirmed identity) | COD-01, GOV-01, GOV-09 | `git rev-parse` true; `git status` clean; `git log -1` = `7f3f38f` — verified |
| WP11-T03 | Install security scanners **(DONE, D-055)** | gitleaks 8.30.1 + Trivy 0.76.2 (portable ZIPs in `%LOCALAPPDATA%\Programs\security-tools`); Semgrep 1.186.0 (Python 3.12.10 + `pip install --user`); all dirs on persistent user PATH | D-032, WP11-T01, D-055 | TST-12 | `gitleaks version` → 8.30.1 ✅; `trivy --version` → 0.76.2 ✅; `semgrep --version` → 1.186.0 ✅ (all exit 0) |
| WP11-T04 | Install Laravel Boost **(DONE, D-056)** | `laravel/boost` v2.10.4 in `vendor/`; `boost:install --guidelines --no-interaction`; `AGENTS.md`/`CLAUDE.md` regenerated + `boost.json`; AGENTS.md re-read | AGENTS.md guideline, WP11-T01, D-050, D-056 | GOV-07, GOV-09 | `vendor/laravel/boost` ✅; AGENTS.md has `<laravel-boost-guidelines>` ✅; `php artisan --version` = 13.43.0 ✅ |
| WP11-T05 | Git remote & branch policy **(DONE, D-053)** | Remote `origin` = `https://github.com/arhariz/financial-portal-backend.git` (**public**, `main`), pushed `7f3f38f` + `b77fbe8` + `0d813f9`. Secret scanning + push protection enabled; branch protection verified. Frontend repo not created (separate workspace). | D-051, D-052, D-053 (Q-009 closed) | GOV-01, GOV-09 | `gh repo view` → PUBLIC; `gh api` → scanning enabled + protection rules present — verified |

---

*Summary queue: `docs/State_Ledger.md` §BUILD_QUEUE. Rules: `docs/Constitution.md` + routed `rules/*.md`. State policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
