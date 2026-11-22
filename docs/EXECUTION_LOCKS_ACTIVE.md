# docs/EXECUTION_LOCKS_ACTIVE.md — Founder business locks (policy, not code rules)
# Authority: Owner / Founder
# Owner / approver: Owner / Founder
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: locked business policy, deferred scope, approval requirements
# Does NOT contain: code rules, executed state, table inventory
# Conflict rule: EXECUTION_LOCKS wins for business policy; CONSTITUTION wins for code rules; STATE_LEDGER wins for executed state.
# Status: ACTIVE

---

## HOW TO USE

Read this file every session. A `rules/*.md` rule never overrides a lock below. Any change to a lock requires a numbered Decision in `docs/State_Ledger.md` with owner approval (GOV-03). If a policy is not listed here or in `docs/plan.md` as LOCKED, **GOV-01 applies: STOP — do not invent a default.**

## LOCKS

**LOCK-01 | Product model.** Company-managed, forex-style investment portal. Investors invest in symbols (XAUUSD, BTCUSD, USOIL, …). Returns are **staff-entered daily P&L**, not live trading. (plan §1, D-001…D-018)

**LOCK-02 | Roles.** Owner/Super Admin, Admin, KYC Reviewer, P&L Operator, Finance/Withdrawal Manager, Credit Manager, Commission Manager, Support, 3 Agent tiers, Investor. Multi-role allowed; Support is view-only. (D-001, D-002)

**LOCK-03 | Wallets.** Cash, Investment capital, Profit, Commission + combined total shown. Transfers: Cash→Investment, Profit→Cash, Profit→Investment, Commission→Cash, Commission→Investment. Investment→Cash only via approved release. (D-005, D-008)

**LOCK-04 | Money display and conversion.** Amounts shown in **USD**; investors/agents pay BDT at the **admin-set exchange rate** with history. Never hardcode a rate. (D-005, D-006)

**LOCK-05 | Deposits in MVP.** Manual BDT payment proof + approval only. Payment gateway is deferred. (D-006, DFR-002)

**LOCK-06 | Withdrawals.** Profit withdrawable anytime. Capital: request → approval → **24h** → cash wallet → separate withdrawal request. Commission → cash first. No platform fee. (D-007)

**LOCK-07 | Commission economics.** Commission is a **platform cost** — downline amount is never reduced. Credit-purchase role bonus is **separate from MLM**; upline earns nothing on it. Profit commission base = net credited after admin-defined deductions. (D-012, D-013)

**LOCK-08 | MLM configuration.** Agent tiers (names TBD), depth, rates per tier × level, events, holding period and auto-promotion thresholds are all **admin-configurable at runtime** — never hardcoded. (D-002, D-013, D-014, DFR-001, DFR-003)

**LOCK-09 | KYC.** Investors + agents require KYC; gates investing and withdrawal; document checklist per user type is admin-configurable. (D-003, D-004)

**LOCK-10 | Delivery.** Localhost-first; staging mirror; production only after `fp-local-verification-gate`. Mobile has an independent test track with flavors and never targets production from dev. (D-023, GOV-08)

**LOCK-11 | Deferred scope — DO NOT BUILD IN MVP.** Payment gateway; live/real-time trading updates; mobile admin approvals; biometric login; PDF exports; scheduled reports; public SEO site. Activation requires a Decision + release/version bump. (plan §4, DFR-002…DFR-010)

**LOCK-12 | Stack.** Laravel 13 + PHP 8.3 + Filament 5 + MySQL 8 + Redis on DigitalOcean Ubuntu (`api.example.com`); React 19.3 + Vite 8 + TS 7 SPA on Vercel (`app.example.com`); Flutter 3.50 (FVM) mobile; Cloudflare R2 + CDN; Brevo email. (D-020…D-024)

**LOCK-13 | No arbitrary size limits.** GOV-05 applies to code, prompts and documents. Refactor for cohesion, not line counts. (D-027)

**LOCK-14 | Rule authority.** `docs/Constitution.md` + `fp-*` project skills override any external skill advice. No external skill may introduce float money, client-side business logic, or destructive ledger edits. (D-025, D-026)

## POLICY DECISIONS (no longer blocked)

| Ref | Decision | Decision ID |
|---|---|---|
| Q-001 | USD stored `DECIMAL(18,4)` internally, displayed 2dp; largest-remainder allocation at 4dp | D-029 |
| Q-002 | Commission deduction types: `loss_adjustment`, `reversal`, `admin_adjustment` (mandatory note); new types need a Decision | D-030 |
| Q-003 | Agent tier display names: **Seed Investor / Growth Investor / Master Investor** for tier 1/2/3 | D-031 |
| Q-004 | Redis vs database queue | Redis on DigitalOcean VPS (D-022, D-024) |

**OPEN POLICY: NONE.** Any new business/rate/name decision still triggers GOV-01.

## CHANGE PROCEDURE

1. Open/answer an item in `docs/State_Ledger.md` §Open Questions.
2. Owner approves; add a Decision ID.
3. Update this file and `docs/plan.md` / `docs/EXECUTION_LOCKS_ACTIVE.md` pointers.
4. Implement with tests; close via `fp-task-closeout`.
