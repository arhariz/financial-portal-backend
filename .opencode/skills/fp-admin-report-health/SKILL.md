---
name: fp-admin-report-health
description: Final CTO-level review of the Filament Report section and every module health widget. Use when asked whether the project is complete, to audit admin visibility, or to run the end-of-project report sign-off. Triggers on report section, module health, admin widget, observability, final sign-off, project complete, is everything working.
---

# Admin Report Section Health Check

Read `docs/Admin_Observability_Spec.md`, `rules/reporting.md`, `rules/operations.md`, and `fp-project-context` first.

## Procedure

1. Confirm the Filament **Reports** page group exists with all 8 tabs (Overview, Ledger & Wallets, Investment & P&L, Agents & Commissions, KYC & Security, Money Flow, Operations, Exports).
2. For each of the 12 widgets in the catalogue, verify:
   - state (green / amber / red) with timestamp, metric, owner, and drill-down link;
   - data source query actually runs and returns current data;
   - Support role sees view-only.
3. Run the 11-point final sign-off checklist from `docs/Admin_Observability_Spec.md`.
4. Treat any RED as a blocker: no "project complete" claim until resolved and evidenced.
5. Acceptable AMBER requires a written CTO decision in `docs/State_Ledger.md` §Known Issues.
6. Produce a table: Widget | State | Evidence | Owner | Action. End with exactly one verdict: **HEALTHY** or **NOT HEALTHY** and the top 3 actions.

## Rules enforced

- REP-09 every module exposes a health widget.
- REP-10 green/amber/red with owner and drill-down.
- REP-11 final sign-off requires all green or accepted amber.
- OPS-11 work package not DONE until its widget is visible.
- GOV-09 evidence before claims — no verbal "it works".
