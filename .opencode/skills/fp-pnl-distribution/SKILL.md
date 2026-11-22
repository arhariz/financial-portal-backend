---
name: fp-pnl-distribution
description: Locked Investment & P&L Engine rules (plan Section 4) — symbols, positions, T+2 eligibility, per-symbol daily P&L entry, proportional distribution, profit wallet, loss to position capital, corrections. Use when implementing or reviewing any investment, position, symbol, distribution job, P&L report, or eligibility logic.
---

# Investment & P&L Engine (plan §4 — locked)

Formal rules: `PNL-01..07`, `TME-01..04`, `MON-01/05/08` in `rules/money.md`. Load that module via `fp-project-context`.

## Positions

- One position per investment: user, symbol, capital, original amount, investment date, eligibility date, status.
- Profit transferred into investment creates a **new position** with a fresh eligibility date.
- Investor sees visible wallets plus a combined total.

## Eligibility (Asia/Dhaka)

- First trade day = next business day after investment.
- Eligible from the **second business day** after investment:
  - Invest Mon–Thu → eligible Wed–Fri respectively.
  - Invest Fri/Sat/Sun → eligible **Tuesday**.
- Once eligible, participates in **every subsequent distribution** until capital is withdrawn.
- System auto-flags eligible positions; staff may not need to pick manually.

## Distribution (queued job)

- Scope: **one symbol + one trade date**.
- Staff enters signed P&L → clicks **Distribute**.
- Split base: each eligible position's **current capital** (after prior losses, transfers-in, withdrawals), NOT original amount.
- Profit → **profit wallet** (separate). Loss → reduces **position capital**.
- Idempotent (no double distribution), re-runnable via compensating reversal entry with `reversal_of_id`.
- Corrections/reversals require role permission and full audit.

## Testing checklist

Proportional accuracy, weekend eligibility rule, ongoing eligibility, loss to capital, profit to wallet, reversal, concurrent run, and distribution items summing exactly to the entered amount.
