---
name: fp-cto-review
description: CTO-hat technical review of the Financial Investments Portal — architecture compliance, money-integrity controls, queue/reliability, security posture, test coverage, and deployment readiness. Use for technical health checks, architecture reviews, incident readiness, pre-deploy assessment, or tech risk registers. Triggers on CTO, architecture review, technical debt, security posture, deployment readiness, reliability, queues, incident readiness, tech risk.
---

# CTO Review — Financial Investments Portal

Read `fp-project-context`, `docs/Constitution.md`, and `docs/Skills_Registry.md` first.

## Report structure

1. **Architecture compliance** — check every layer against the locked stack: Laravel 13/PHP 8.3 + Filament 5 on DigitalOcean; React 19/Vite 8/TS 7 SPA on Vercel; Flutter 3.50 (FVM) mobile; MySQL 8 + Redis; Cloudflare R2; Brevo.
2. **Money integrity** — DECIMAL columns, immutable ledger, atomic transactions, reconciliation invariants, no client-side math, corrections via reversals only. Cite `fp-money-ledger`.
3. **Async reliability** — queue workers/scheduler on VPS; distribution idempotency; commission vesting jobs; failed-job alerts (Horizon optional).
4. **Security** — Sanctum cookie vs Bearer split, KYC gate in service layer, RBAC deny-by-default, suspension blocks transactions, secret handling (no `VITE_` secrets), gitleaks/trivy/semgrep in CI.
5. **Testing & gates** — money-path tests, ledger reconciliation assertions, local verification gate (`fp-local-verification-gate`), separate mobile gate (`fp-mobile-test-track`).
6. **Deployment posture** — two vendors (Vercel + DigitalOcean), custom domains `app.example.com`/`api.example.com`, R2 uploads, migrations as explicit release step, no direct DB edits.
7. **Risk register** — table: risk, likelihood, impact, mitigation, owner, status. Include the deferred Laravel 14/PHP 8.4 upgrade and agent-tier naming.

## Rules

- Report evidence, not intentions: cite file paths, test results, or config entries.
- Any finding that violates a Constitution article is P0/P1 regardless of severity elsewhere.
- End with a single prioritized action list (max 3 items).
