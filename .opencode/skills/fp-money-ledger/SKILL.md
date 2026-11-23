---
name: fp-money-ledger
description: Financial integrity rules for every wallet, deposit, withdrawal, transfer, credit purchase, and correction in the Financial Investments Portal. Use when touching money code, migrations, balances, ledger tables, exchange rates, or reports. Triggers on wallet, balance, ledger, DECIMAL, deposit, withdrawal, capital release, agent credit, transfer, reversal, reconciliation, float, or transaction.
---

# Money & Ledger Rules

Source of truth: `rules/money.md` (MON-01..08, TME-01..04, WAL-01..06, PNL-01..07, COM-01..08), plus DB-01/02/06 in `rules/backend.md`, TST-03/04/05 in `rules/testing.md`, and `docs/Schema_Registry.md` §3. Load `rules/money.md` + `rules/testing.md` with `fp-project-context`.

## Hard rules

1. **Never use FLOAT/DOUBLE for money.** USD columns are `DECIMAL(18,4)` (display 2dp), exchange rate `DECIMAL(12,4)`, ratios `DECIMAL(10,6)`. Clients receive decimal **strings**; no JS/Dart binary-float math on money. Remainder via largest-remainder allocation (MON-08).
2. **Every balance change writes an immutable `wallet_transactions` row** in the same DB transaction as the wallet update. Direct balance writes are forbidden.
3. **Ledger rows are append-only.** No UPDATE, no DELETE. Corrections are new compensating rows referencing the original via `reversal_of_id` / `reference_id`.
4. **All money flows are atomic** with row locking where concurrency is possible. Wrap deposit approval, distribution, withdrawal, credit purchase, transfer, and commission posting in one DB transaction.
5. **Reconciliation must always hold:** for every wallet, `sum(credits) − sum(debits) = balance`. Distribution items must sum exactly to the distribution amount.
6. **No negative wallet balances.** A loss debits the *profit wallet* first up to its available balance, then debits the remainder from *position capital*; both stay ≥ 0, and a loss that cannot be covered blocks the entry for admin correction (D-064).
7. **Asia/Dhaka** defines business dates; store UTC timestamps. T+2 eligibility and the 48h capital-release rule are exact — never approximate.

## Locked money flows (summary)

- Manual deposit: BDT proof → approval → USD to cash wallet at current admin rate.
- Profit withdrawal: anytime. Capital: request → approval → 24h → cash wallet → **separate** withdrawal. Commission → cash first.
- Agent credit: BDT at admin rate → USD face value to cash + role bonus to commission wallet; in-app transfer to investor with ledger.
- Transfers: Cash→Investment (new position), Profit→Cash, Profit→Investment (new position), Commission→Cash, Commission→Investment. Investment→Cash **only** via approved release.

## Tests required

For every money change: success, failure/rollback, insufficient funds, invalid KYC, suspended user, concurrent double-spend, idempotency, and reversal. Assert reconciliation after every scenario with DECIMAL-safe comparisons.
