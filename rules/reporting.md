# rules/reporting.md — REP rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: REP-01..11
# Does NOT contain: schema, code rules, deployment
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

REP-01 | Definitions | AUM = sum active `positions.capital`; net flow = deposits − withdrawals; liability = withdrawable balances + pending/vesting commission | report test
REP-02 | Investor report | Wallets, positions with eligibility, P&L by symbol/date, transaction ledger, KYC status | report test
REP-03 | CFO review | Balance sheet, money flow, distributions, commission vs role bonus, reconciliation health (`fp-cfo-review`) | report review
REP-04 | CEO review | Phase, traction proxies only if data exists, risks with mitigations, decisions needed, one next action (`fp-ceo-review`) | report review
REP-05 | CTO review | Architecture compliance, money integrity, queues, security, gates, risk register (`fp-cto-review`) | report review
REP-06 | Variance/roll-forward | Explain flux from underlying activity; opening + activity − reversals = closing | `variance-commentary`, `roll-forward`
REP-07 | Access | Reports role-gated; Support view-only; no KYC document export | policy test
REP-08 | Honesty | Never fabricate numbers; if data missing, state it and name the query needed | evidence review
REP-09 | Every module | Exposes a Filament health widget in the Reports group before its work package is DONE | `docs/Admin_Observability_Spec.md` + OPS-11
REP-10 | Widget state | Green/amber/red with timestamp, metric, owner and drill-down; Support view-only | widget review
REP-11 | Final sign-off | Report section healthy only when all 12 widgets pass the 11-point checklist; red blocks completion | `fp-admin-report-health`

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
