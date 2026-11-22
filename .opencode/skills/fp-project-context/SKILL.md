---
name: fp-project-context
description: Must-load context for every task on the Financial Investments Portal. Use at the start of any implementation, review, debugging, or planning task in this repository. Triggers on any work in C:\laragon\www\financial_portal involving the portal, Laravel, Filament, React, Flutter, wallets, P&L, commissions, KYC, agents, or reports.
---

# Financial Portal — Locked Project Context

## Mandatory bootstrap

Before doing any work, reproduce the `CONSTITUTION ACKNOWLEDGED` block from `docs/Constitution.md` with:
- the real latest `docs/State_Ledger.md` entry code and date (pull it, never guess),
- the rule codes that apply (e.g. MON-02, PNL-03, GOV-01),
- the schema tables touched,
- the `rules/*.md` modules read.

## Read order (from `docs/DOCS_MANIFEST.md`)

Always: `docs/DOCS_MANIFEST.md`, `docs/State_Ledger.md`, `docs/EXECUTION_LOCKS_ACTIVE.md`, `docs/Feature_Flags_Registry.md`, `docs/Constitution.md`.

Then load **only** the routed rule module(s) from `docs/Constitution.md` GOV-07:
- Money/P&L/commission/wallet → `rules/money.md`
- Backend → `rules/backend.md`
- Frontend → `rules/frontend.md`
- Mobile → `rules/mobile.md`
- Security → `rules/security.md`
- Tests → `rules/testing.md`
- Reports → `rules/reporting.md`
- Deploy/ops → `rules/operations.md`
- Docs/closeout → `rules/documentation.md`

Add `docs/Schema_Registry.md`, `docs/project_Context.md`, `docs/plan.md`, `docs/Prompt_Library.md`, `docs/Skills_Registry.md` as the task requires. Do not load every rule file — that defeats the token-cost design.

## Authority

- `docs/State_Ledger.md` wins for executed state.
- `docs/Constitution.md` + `rules/*.md` win for code rules.
- `docs/EXECUTION_LOCKS_ACTIVE.md` wins for business policy — a rule never overrides a lock.
- `docs/Feature_Flags_Registry.md` controls deferred capabilities (default OFF).
- If conflict remains: stop, record both values, ask the owner (GOV-04).

## Locked stack (summary)

- PHP 8.3 + Laravel 13 + Filament 5 + MySQL 8 + Redis — API on DigitalOcean Ubuntu (`api.example.com`).
- React 19.3 + Vite 8 + TypeScript 7 + Tailwind 4 SPA on Vercel (`app.example.com`).
- Flutter 3.50 + Dart 3.14 (FVM-pinned), FCM push, Sanctum Bearer tokens in secure storage.
- Cloudflare R2 + CDN for uploads; Brevo for transactional email.
- Localhost-first development; production only after `fp-local-verification-gate`; mobile has its own track.

## Workflow

After any task, run `fp-task-closeout`: update `docs/State_Ledger.md` snapshot/queue/entry log, rotate old details to the archive (GOV-08), and sync `docs/Schema_Registry.md` if the schema changed.
