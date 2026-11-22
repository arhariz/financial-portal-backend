---
name: fp-cfo-review
description: CFO-hat financial analysis for the Financial Investments Portal — AUM, net money flow, wallet balances, profit distributions, commission liability, withdrawal pressure, and reconciliation health. Use for financial reports, investor statements, cash-flow analysis, month-end review, or CFO-style summaries. Triggers on CFO, AUM, net flow, liquidity, cash flow, commission liability, withdrawal pressure, reconciliation, financial report, investor statement, month-end.
---

# CFO Review — Financial Investments Portal

Read `fp-project-context` and `fp-money-ledger` first. Use **only** real data from the local DB/reports; never fabricate figures. All math uses DECIMAL, never floats.

## Report structure

1. **Balance sheet view (platform)**
   - Investor wallets: cash / investment capital / profit / commission + combined total (from `wallets`).
   - AUM = sum of active `positions.capital`.
   - Platform liabilities = investor withdrawable balances + pending/vesting commissions.
2. **Money flow (period)**
   - Manual deposits approved, withdrawals paid, capital releases pending/released (24h rule).
   - Net flow = deposits − withdrawals; note pending approvals queue as constrained liquidity.
3. **Investment performance**
   - P&L posted per symbol/trade date; distributed profit vs loss; count of distribution runs and reversals.
   - Investor-level P&L from `pnl_distribution_items` (profit wallet credits, capital reductions).
4. **Commission & agent economics**
   - Role bonuses (agent credit purchases) vs MLM commissions — report separately; MLM is a platform cost.
   - Pending/vested/cancelled commissions by tier and level; clawback exposure from withdrawals before vesting.
5. **Reconciliation health**
   - For every wallet: `sum(credits) − sum(debits) = balance`. Distribution items sum to distribution amount.
   - List breaks; for each, invoke `gl-recon` then `break-trace` to root-cause.
6. **Risks & actions** — withdrawal pressure, commission liability growth, KYC backlog, exchange-rate exposure (BDT/USD), unvested commission reversals.

## Rules

- Commission base is **net credited profit after admin-defined deductions**, never gross.
- Profit and capital are separate flows; capital release is always a two-step, 24h-gated process.
- If data is unavailable, state it explicitly and describe the query/report needed.
