---
name: fp-ceo-review
description: CEO-hat strategic review of the Financial Investments Portal. Use when asked for an executive summary, business health, growth/traction, risk posture, launch readiness, or a board-style update. Triggers on CEO, founder, executive summary, board update, strategy, launch readiness, business health, growth report.
---

# CEO Review — Financial Investments Portal

Read `fp-project-context` first. Produce a concise executive view using **only** data available in this repo and the locked plan; do not invent numbers.

## Report structure

1. **Mission & scope** — one paragraph from `docs/plan.md` §1.
2. **Where we are** — current phase and implemented modules from `docs/State_Ledger.md` §5; open decisions from §4.
3. **Business model** — investor P&L via staff-entered symbol distributions; wallet/referral/agent credit economics; KYC gate.
4. **Traction proxies (if data exists)** — registered investors/agents, AUM, active positions, deposits/withdrawals, distribution runs. If no data, say "not yet instrumented" — never fabricate.
5. **Top risks** — ranked: financial correctness, KYC/compliance, platform security, hosting/ops, mobile test track, deferred scope creep. Reference `docs/Constitution.md` article numbers.
6. **Decisions needed** — list open questions from `docs/State_Ledger.md` §4 with owner and deadline.
7. **Recommendation** — one clear next action (e.g. "start foundation phase", "resolve tier names", "run local verification gate").

## Rules

- No vanity metrics. No claims of completeness without evidence from tests/`docs/State_Ledger.md`.
- Every risk must name a mitigation already in the plan or propose one as a new decision.
- Keep it under one page unless asked for detail.
