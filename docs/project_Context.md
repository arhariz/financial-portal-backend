# Project Context — Financial Investments Portal

This file is the canonical "what this project is" reference for any developer or AI agent. Read `docs/plan.md` for locked decisions, `docs/Schema_Registry.md` for data design, and `docs/Constitution.md` for non-negotiable rules.

## 1. One-Paragraph Summary

A company-managed, forex-style investment platform where investors deposit BDT (manual, admin-approved) into a USD wallet, invest in market symbols (XAUUSD, BTCUSD, USOIL, etc.), and staff enter daily per-symbol P&L that the system distributes proportionally to eligible positions after a T+2 holding rule. Agents buy USD credit with BDT at an admin-set exchange rate, earn a role-based bonus, sell credit to investors, and earn multilevel referral commissions. KYC gates investing and withdrawals. Web + native mobile clients consume one Laravel API.

## 2. Domain Glossary

| Term | Meaning |
|---|---|
| **Symbol** | Tradable instrument (XAUUSD, BTCUSD, USOIL) managed by admin; can be active/paused. |
| **Position** | One investment record: user, symbol, capital, investment date, eligibility date, status. One position per investment. |
| **Eligible** | A position may receive P&L from the second business day after investment (Fri/Sat/Sun → Tuesday). |
| **Trade date** | The market day the P&L refers to; staff enters P&L per symbol + trade date. |
| **Distribute** | Staff action that splits a symbol's daily P&L across eligible positions by current capital. |
| **Current capital** | A position's live capital after prior losses, transfers-in, and withdrawals (basis for P&L split). |
| **Wallets** | Cash (USD), Investment capital, Profit, Commission. Combined total shown to investor. |
| **Exchange rate** | Admin-set BDT per USD (e.g. 130). All BDT↔USD conversions use it; history kept. |
| **Manual deposit** | User pays BDT, submits proof, admin approves → USD credited to cash wallet. |
| **Capital release** | Approved capital withdrawal; after 24h capital moves Investment→Cash, then a separate withdrawal request is made. |
| **Agent tier** | 3 levels: **Seed Investor (1) / Growth Investor (2) / Master Investor (3)**, with different credit bonuses and commission rates. Names are display config; internal `tier` is integer 1/2/3. |
| **Downline / level** | Referral tree built from optional signup codes; agents refer investors and other agents. |
| **Role bonus** | Extra USD (e.g. 5%) credited to an agent's commission wallet when they buy credit; separate from MLM. |
| **Commission** | MLM payout on configured events, vesting after an admin-set holding period. |
| **KYC** | Document verification for investors/agents; gates investing and withdrawal. |

## 3. Key Business Rules (summary of locked)

1. All dates use **Asia/Dhaka**; timestamps stored in UTC and converted for display.
2. Investing and withdrawal require **approved KYC**; registration and deposit do not.
3. P&L splits are proportional to **current capital**, not original deposit.
4. Profit lands in the **profit wallet**; a loss debits the **profit wallet** first up to its available balance, then debits the remainder from **position capital** (two immutable `loss_adjustment` rows; block if insufficient). Profit→investment creates a **new position**. (D-064)
5. Capital never returns to cash automatically — only via **admin-approved release + 24h**.
6. Commission is a **platform cost**; the downline's amount is never reduced.
7. Role bonus from credit purchase is **not** commissionable by upline.
8. Every money movement writes an **immutable ledger entry**; corrections are compensating entries.
9. Users may hold **multiple roles**; every action is checked against role permissions.
10. Web and mobile are thin clients — all business logic lives in the Laravel API.

## 4. User Roles

- **Super Admin (Owner)** — unrestricted, sets defaults and per-role dashboard layouts.
- **Admin** — near-full control.
- **KYC Reviewer** — reviews/approves KYC.
- **P&L Operator** — enters per-symbol P&L and distributes.
- **Finance/Withdrawal Manager** — approves withdrawals and capital releases.
- **Credit Manager** — approves manual deposits/agent credit purchases.
- **Commission Manager** — configures MLM rules.
- **Support** — view-only reports, no money operations.
- **Agent (3 tiers)** — buys/resells credit, builds downline, earns commissions.
- **Investor** — deposits, invests, tracks P&L, withdraws.

## 5. Scope Boundaries

**In MVP:** manual deposits only, full KYC workflow, wallet engine, investment/P&L engine, MLM commissions, notifications, audit, Filament admin, React web portal, Flutter mobile v1 (investor + agent).

**Not in MVP:** payment gateway, live/real-time trading feeds, admin approvals in mobile, biometric login, PDF exports, scheduled reports.

## 6. Environment

- Local development: **Laragon 8.6.1** on Windows, project root `C:\laragon\www\financial_portal`, served at **`http://forexbd.test`** (Apache 2.4.66 vhost `forexbd.test.conf`, docroot `public/`; the Laragon `financial_portal.test` vhost also points at `public/`).
- **Verified installed local tools (2026-11-21):** PHP **8.3.31** CLI (Laragon `php-8.3.30-Win32-vs16-x64`), Composer **2.9.7**, Node **v26.12.0**, npm **11.20.0**. Required PHP extensions present: pdo_mysql, redis, mbstring, openssl, bcmath, gd, zip, intl, curl, fileinfo, tokenizer, xml. Laragon also ships MySQL, phpMyAdmin, HeidiSQL, Redis, Mailpit, nginx/Apache, Git, and **ngrok**. **Verified services:** MySQL **8.4.3** (:3306), Redis **8.2.5** (:6379), Apache **2.4.66** (:80/:443), Git 2.53.0. **Not available on this host:** xdebug, pcov, pcntl, posix — no coverage tooling, and Horizon cannot run on Windows.
- Locked runtime: **PHP 8.3 + Laravel 13 + Filament 5**, MySQL 8/MariaDB, Redis, Node (Vite 8 requires ≥20.19 — installed 26.12 is compatible) + React 19.3.1 + Vite 8.3.8 + TypeScript 7.0.2, Flutter 3.50.0 + Dart 3.14 (FVM-pinned).
- **Installed (2026-11-21):** Laravel **13.43.0** via `composer create-project` (no global installer on this host); database `financial_portal` on MySQL 8.4.3; queue/session/cache drivers = `database`; 60 npm packages; `vite build` exit 0; `php artisan test` 2/2 passing; `pint --test` clean; `GET /health` JSON live; **Filament 5.11.4** admin panel at `http://forexbd.test/admin` (superadmin `admin@forexbd.test`, role `super_admin`), including a user-management resource at `/admin/users` gated by `users.view`/`users.manage`; **Spatie permission 8.5 + activitylog 4.12** (30 permissions / 12 roles via `permissions:sync`); **PHPStan 2.3.7 L6 + Larastan 3.15** (zero baseline) and **Pint PSR-12** configured, PHPUnit 8 tests / 65 assertions green. **Not yet installed:** Inertia 3, React 19, Sanctum, Maatwebsite Excel (Pest 4 optional — TST-12 accepts PHPUnit).
- Version policy: Laravel 13 is the only supported major (security to 2028-03-17). **PHP 8.3 locked locally and in production** for parity (security support to 2027-12-31); Laravel 14 (expected Q1 2027) requires PHP 8.4, so that future upgrade is paired with a PHP bump. TypeScript 7 uses `@typescript/typescript6` alias for ESLint until 7.1.
- **Production topology (Combination A):**
  - Web portal → Vercel static SPA, domain `app.example.com`; `vercel.json` SPA rewrite; Node 22 LTS.
  - Laravel API + Filament + queue workers + scheduler → **DigitalOcean Ubuntu VPS**, domain `api.example.com`.
  - Uploads (KYC docs, payment proofs, exports) → **Cloudflare R2** served via Cloudflare CDN. Never on local disk (Vercel is read-only; VPS disk not durable/shared).
  - Transactional email → **Brevo** (API/SMTP).
  - Auth: Sanctum cookie mode for the SPA (same root domain, `supports_credentials: true`, `SESSION_DOMAIN=.example.com`); Sanctum Bearer tokens in `flutter_secure_storage` for mobile.
  - Vercel `VITE_` variables are public by design — API URL only; no secrets in the client bundle.
- **Delivery rule:** complete system is developed and fully verified on localhost (Laragon) first. Production deployment only after the local verification gate passes (fresh migrations, full automated suite, ledger reconciliation, manual E2E of all money paths). Staging mirrors production with separate DB/Redis/R2/Brevo test sender.
- **Seeder rule (TST-13):** every feature must ship a database seeder that creates the minimum data needed to exercise it, so any feature can be checked on a fresh migrated database. `DatabaseSeeder` orchestrates all feature seeders; seeders are idempotent and always invoked as `php artisan db:seed --class=<Name>Seeder` (bare `db:seed` is forbidden). Implemented so far: `RolePermissionSeeder` + `UserSeeder` — 30 permissions / 12 roles / 13 users (dev password `Password@123`). See WP10.
- **Mobile testing is a separate track:** Flutter unit/widget/integration tests, emulator/simulator + physical devices, flavor-specific API endpoints (`dev`/`staging`/`prod`). Mobile never points at production during development; app builds promoted independently. Laragon's **ngrok** provides an HTTPS tunnel from a physical device to the localhost Laravel API during dev-flavor testing (physical devices cannot reach `localhost` directly).
- Laravel 13.43.0 skeleton installed 2026-11-21; **no application code yet**.

## 7. File Registry

| File | Purpose |
|---|---|
| `docs/plan.md` | Locked project plan (all 10 sections, stack, sequence). |
| `docs/project_Context.md` | This file — domain, rules, roles, scope, environment. |
| `docs/Schema_Registry.md` | Canonical database table/column registry and conventions. |
| `docs/Constitution.md` | ACK, precedence, GOV gates, forbidden commands, immutable rule index. |
| `docs/DOCS_MANIFEST.md` | Read order, owners, authority, module loading. |
| `docs/EXECUTION_LOCKS_ACTIVE.md` | Founder business locks; wins for policy. |
| `docs/Feature_Flags_Registry.md` | Deferred capabilities, default OFF, activation procedure. |
| `rules/*.md` | 9 rule files (money, backend, frontend, mobile, security, testing, reporting, operations, documentation). |
| `docs/State_Ledger.md` | Snapshot, build queue, decisions, deferred items, open questions, last 4 detailed entries. |
| `docs/State_Ledger_Archive.md` | Older history rows + completed module details (decisions never archived). |
| `docs/Build_Queue_Detail.md` | 78 single-commit task cards; load one card at a time (OPS-10). |
| `docs/Admin_Observability_Spec.md` | 12 module health widgets + Report section + final sign-off. |
| `docs/Prompt_Library.md` | Reusable prompts for implementing work on this project. |
| `docs/Skills_Registry.md` | Inventory of 64 installed AI skills (52 external + 12 custom) and reinstall commands. |
