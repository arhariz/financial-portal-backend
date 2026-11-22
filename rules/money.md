# rules/money.md — MON, TME, WAL, PNL, COM rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-029, D-030, D-031)
# Source of truth for: MON-01..08, TME-01..04, WAL-01..06, PNL-01..07, COM-01..08
# Does NOT contain: database mechanics (backend.md), security policy, testing, operations
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

## SECTION 1 — MONEY (MON)

MON-01 | USD money storage | All USD money columns `DECIMAL(18,4)`; display **2 decimals**; exchange rate `DECIMAL(12,4)`; ratios `DECIMAL(10,6)`; no FLOAT | schema inspection
MON-02 | Balance change | Append-only `wallet_transactions` row + wallet update in the same DB transaction; no direct balance writes | guard test
MON-03 | Ledger immutability | Never UPDATE/DELETE ledger, distribution or commission rows; corrections are reversal rows | append-only test
MON-04 | Atomicity | Deposit approval, distribution, withdrawal, credit purchase, transfer, commission posting = one `DB::transaction()` with `lockForUpdate()` | code + concurrency test
MON-05 | Reconciliation | For every wallet: credits − debits = balance; distribution items sum exactly to posted P&L | TST-04
MON-06 | No negative wallets | Losses reduce position capital; wallets never below zero | boundary test
MON-07 | Client money | API sends decimal strings; no JS/Dart binary-float arithmetic | grep + FE/MOB tests
MON-08 | Rounding remainder | Largest-remainder allocation at 4dp; leftover assigned to largest share, ties by position id; never silent drift | distribution test

## SECTION 2 — TIME & BUSINESS RULES (TME)

TME-01 | Business dates | Asia/Dhaka; store UTC timestamps; convert at boundary | date test
TME-02 | T+2 eligibility | Second business day after investment; Fri/Sat/Sun → Tuesday; no approximation | weekend test
TME-03 | Eligibility duration | Once eligible, every subsequent distribution until capital withdrawn | eligibility test
TME-04 | Capital release | Request → approval → 24h → cash wallet → separate withdrawal | release flow test

## SECTION 3 — WALLETS (WAL)

WAL-01 | Wallets | Cash, Investment capital, Profit, Commission + combined total shown | UI/API test
WAL-02 | Cash → Investment | Creates a new position with fresh eligibility date | transfer test
WAL-03 | Profit → Cash | Allowed anytime | transfer test
WAL-04 | Profit → Investment | Creates a **new** position | transfer test
WAL-05 | Commission → Cash / Investment | Allowed; commission→cash first for withdrawal | transfer test
WAL-06 | Investment → Cash | **Only** via approved release + 24h (TME-04) | negative test

## SECTION 4 — INVESTMENT & P&L (PNL)

PNL-01 | Symbols | Admin CRUD; status active/paused | resource test
PNL-02 | Positions | One position per investment; stores capital, investment date, eligibility date | model test
PNL-03 | Eligibility | System auto-flags eligible positions by TME-02 | job test
PNL-04 | Distribution scope | One distribution per symbol + trade date | uniqueness test
PNL-05 | Split basis | Current capital of eligible positions, not original amount (MON-08 remainder) | proportional test
PNL-06 | P&L destination | Profit → profit wallet; loss → reduces position capital | ledger test
PNL-07 | Corrections | Reversal entry with `reversal_of_id`, role permission + audit | reversal test

## SECTION 5 — COMMISSION / MLM (COM)

COM-01 | Downline | Agents refer investors and other agents; optional code at signup; admin-only link changes with audit | referral test
COM-02 | Events | Admin-configurable: new investment (incl. profit reinvest), profit distribution, credit purchase; platform cost (COM-03) | rule test
COM-03 | Platform cost | Commission never reduces downline investment/credit | negative test
COM-04 | Rates | Admin-configurable tier × level matrix; depth admin-configurable; net base after COM-07 deductions | matrix test
COM-05 | Role bonus | Credit-purchase bonus is separate from MLM; upline earns nothing on it | isolation test
COM-06 | Vesting / clawback | Configurable holding period; early withdrawal cancels pending and reverses vested commission | vesting test
COM-07 | Deduction types | Default enabled: `loss_adjustment`, `reversal`, `admin_adjustment` (mandatory note). New types require a Decision (GOV-01) | deduction test
COM-08 | Tier names | Display labels only: **Seed Investor / Growth Investor / Master Investor** for tier 1/2/3; internal `tier` stays integer 1/2/3 | config test

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
