# docs/Feature_Flags_Registry.md — Deferred capabilities, default OFF
# Authority: Owner / CTO
# Owner / approver: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: flag names, default state, activation procedure
# Does NOT contain: code rules, business policy values, executed state
# Conflict rule: EXECUTION_LOCKS wins for policy; CONSTITUTION for code; STATE_LEDGER for state.
# Status: ACTIVE

---

## RULE

Every capability marked deferred in `docs/plan.md` §4 / `docs/EXECUTION_LOCKS_ACTIVE.md` LOCK-11 MUST be behind a config flag declared here and in `App\Support\FeatureFlags\FeatureFlagRegistry`. Default is **OFF in all environments** unless a Decision explicitly allows local/dev only. Activation requires a Decision ID in `docs/State_Ledger.md` + a release/version bump; never flip a flag silently.

Verify: `FeatureFlagsTest` asserts defaults; `.env.example` documents each key; production `.env` must not set them to true without a Decision.

## FLAGS

| Flag | Capability | Default | Decision required |
|---|---|---|---|
| `PAYMENT_GATEWAY_ENABLED` | Online card/bank/crypto deposits | OFF | DFR-002 |
| `LIVE_MARKET_UPDATES_ENABLED` | Real-time/live symbol P&L feeds | OFF | DFR-005 |
| `MOBILE_ADMIN_APPROVALS_ENABLED` | Admin approvals in mobile app | OFF | DFR-004 |
| `MOBILE_BIOMETRIC_ENABLED` | Biometric login in mobile | OFF | DFR-004 |
| `PDF_EXPORTS_ENABLED` | PDF report exports | OFF | DFR-006 |
| `SCHEDULED_REPORTS_ENABLED` | Scheduled email reports | OFF | DFR-006 |
| `PUBLIC_SEO_SITE_ENABLED` | Public Next.js marketing site | OFF | DFR-008 |

## NOT FLAGS — CONFIGURED AT RUNTIME BY ADMIN

These are not feature flags; they are admin-configurable data (GOV-01 protects their values): exchange rate, symbol list, agent tier names, commission rates/depth/holding period/deduction profiles, KYC document checklists, notification templates, dashboard layouts, payout methods.

## ACTIVATION PROCEDURE

1. Open the question in `docs/State_Ledger.md` §Known Issues.
2. Owner approves; add a Decision ID.
3. Add the decision reference to this file and `docs/EXECUTION_LOCKS_ACTIVE.md` / `docs/plan.md` §4.
4. Ship with flag OFF, verify `Model::count() === 0` where relevant, then enable in the release specified by the Decision.
