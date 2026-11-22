---
name: fp-commission-mlm
description: Locked MLM commission rules (plan Section 5) — downline tree, agent tiers, tier×level rate matrix, configurable events, platform cost, net base, vesting, clawback, auto-promotion. Use when implementing or reviewing agents, referrals, commissions, bonuses, downline reports, or promotion rules.
---

# Commission / Referral MLM (plan §5 — locked)

Formal rules: `COM-01..08` and `MON-01/05/08` in `rules/money.md`. Load that module via `fp-project-context`.

- Agents refer **both investors and other agents**; optional referral code at signup; link changes admin-only with audit.
- Commission events, each enabled/rated by admin: **new investment** (includes profit→new position), **profit distribution**, **credit purchase**.
- Commission is a **platform cost** — the downline's investment/credit is never reduced.
- Rates: admin-configurable matrix **agent tier × MLM level**, depth admin-configurable. Tier names/rates configured at runtime.
- Profit-based commission base = net amount actually credited after **admin-defined deductions**.
- The credit-purchase **role bonus** (e.g. 5% to commission wallet) is **separate from MLM** — upline gets nothing on it.
- Commission credits to commission wallet **after an admin-configurable holding period per event**.
- Early withdrawal before vesting → pending commission **cancelled**, already-credited commission **reversed**.
- Agent tiers: admin assignment + **auto-promotion via admin-configurable thresholds**.

## Testing checklist

Each tier/level combination, deduction profile, vesting timing, clawback/cancellation, role-bonus isolation from MLM, downline across agents+investors, and audit of link changes.
