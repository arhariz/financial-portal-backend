# Financial Investments Portal — Project Plan

- **Version:** 1.4 (Locked — stack, client, hosting, delivery, local environment)
- **Status:** Planning complete — implementation pending
- **Last updated:** 2026-11-21
- **Companion files:** `docs/project_Context.md`, `docs/Schema_Registry.md`, `docs/Constitution.md`, `docs/State_Ledger.md`, `docs/Prompt_Library.md`

---

## 1. Product Concept

A company-managed, forex-style investment portal. Investors fund a wallet, invest in symbols (XAUUSD, BTCUSD, USOIL, etc.), and staff post daily symbol P&L that is distributed proportionally to positions. Agents buy USD credit with BDT and earn role-based bonuses plus multilevel commissions. Full KYC is mandatory. Delivered as a responsive web portal plus native mobile apps.

## 2. Locked Scope

### Section 1 — Roles & Access
- Roles: Super Admin (Owner), Admin, KYC Reviewer, P&L Operator, Finance/Withdrawal Manager, Credit Manager, Commission Manager, Support, Agent (3 tiers), Investor.
- Multi-role users allowed. Predefined permission sets with per-user overrides by Super Admin.
- Permission matrix: symbols/users/roles/settings → Super Admin + Admin; P&L entry/Distribute → P&L Operator + Admin + Super Admin; corrections/reversals → Admin + Super Admin (P&L Operator only if overridden); KYC approval → KYC Reviewer + Admin + Super Admin; withdrawal approval → Finance Manager + Admin + Super Admin; exchange rate → Admin + Super Admin; agent credit/rates → Credit Manager + Admin + Super Admin; commission config → Commission Manager + Admin + Super Admin; Support = view-only.
- Agent tier changes: admin assignment + auto-promotion via admin-configurable thresholds. Tier names TBD.

### Section 2 — Auth & KYC
- Registration with optional agent/referral code; admin-configurable email or phone verification plus manual verification; 2FA optional for all.
- KYC required for investors and agents; admin-configurable document checklist per user type; gates investing and withdrawal (registration/deposit allowed before approval).
- Workflow: Submit → Pending → Approve / Reject (with reason) → Resubmit. Admin can suspend/freeze accounts with reason.

### Section 3 — Wallet & Money Engine
- Wallets: Cash, Investment capital, Profit, Commission + combined total shown on dashboard. Admin-managed BDT/USD exchange rate with history.
- Deposits: manual BDT payment proof → admin/Credit Manager approval → USD credited to cash wallet at current rate. Gateway deferred.
- Withdrawals: admin-configurable payout methods per request, no platform fee. Profit withdrawable anytime. Capital: request → admin approval → 24h → capital moves to cash wallet → separate withdrawal request. Commission → cash first.
- Agent credit: agent pays BDT (proof or wallet) at admin rate → face-value USD to cash wallet + role-based bonus (e.g. 5%) to commission wallet; investor pays BDT at admin rate and agent transfers USD via in-app transfer with ledger.
- Internal transfers: Cash→Investment (new position), Profit→Cash, Profit→Investment (new position), Commission→Cash, Commission→Investment. Investment→Cash only via approved withdrawal. Immutable ledger for every movement.

### Section 4 — Investment & P&L Engine
- Admin-managed symbols; one position per investment; timezone Asia/Dhaka.
- Eligibility: second business day after investment (Fri/Sat/Sun → Tuesday), then every subsequent daily distribution until capital withdrawn.
- Staff enters P&L per symbol + trade date → clicks Distribute → proportional split by current position capital among eligible positions.
- Profit → profit wallet; loss → debit the profit wallet first up to its available balance, then debit the remainder from position capital (immutable `loss_adjustment` rows; block the entry if funds cannot cover it). Profit transferred into investment creates a new position. (D-064 supersedes D-010 for loss handling.)
- Corrections/reversals allowed with role permission and full audit; account-level P&L reports.

### Section 5 — Commission / Referral (MLM)
- Agents refer investors and other agents; optional referral code at signup; downline link changeable by admin only with audit.
- Commission events (admin-configurable): new investment (incl. profit reinvest), profit distribution, credit purchase. Platform cost — downline unaffected.
- Rate matrix: agent tier × MLM level with per-cell percentage; depth admin-configurable. Profit commission on net amount credited after admin-defined deductions.
- Credit-purchase role bonus is separate from MLM — no upline cut on it.
- Commission vests after an admin-configurable holding period per event; withdrawal before vesting cancels/reverses pending commission.

### Section 6 — Reporting & Dashboards
- Investor: wallets + combined total, portfolio/positions, P&L history + chart, transaction ledger, KYC/referral status.
- Admin: AUM + money flow, approvals queue, P&L distribution status, agent performance, system controls. Widget layout configurable per role by Super Admin; per-user personalization allowed.
- Reports: transaction ledger, P&L (investor/symbol/date), commission, agent/downline, KYC/account; extensible. Filters + CSV/Excel export; role-based access.
- **Admin observability (locked D-034):** a Filament **Reports** section with 8 tabs and **12 module health widgets** (ledger reconciliation, queue health, P&L distribution, deposit approvals, withdrawals/capital releases, KYC review, agent/commission, exchange rate/symbols, notifications, security/audit, storage/backup, money flow). Every work package is not DONE until its module widget is visible and drillable (OPS-11). End-of-project sign-off requires all widgets green or accepted amber via `fp-admin-report-health` (`docs/Admin_Observability_Spec.md`).

### Section 7 — Notifications
- Channels: in-app, email, SMS, mobile push. Events: KYC, deposits/withdrawals, P&L distributions, commissions/agent updates, security alerts.
- User preferences per channel/event (security alerts mandatory); admin-editable templates with variables.

### Section 8 — Audit & Security
- All staff actions logged with actor, timestamp, IP/device; login history; config-driven retention. Immutable ledger and permission checks throughout.

### Section 9 — Mobile App
- v1: investor (dashboard, invest, withdraw, KYC upload) + agent (credit purchase, sell credit, downline) + push alerts.
- Admin approvals, biometric login, and other features added later based on need.

## 3. Technology Stack (Locked)

| Layer | Choice |
|---|---|
| Backend / API | Laravel 13 (PHP 8.3), REST API via Sanctum |
| Admin panel | Filament 5 (Livewire 4, Tailwind CSS 4.1) |
| Web portal | React 19.3.1 + Vite 8.3.8 + TypeScript 7.0.2 + Tailwind CSS 4.3 + shadcn/ui |
| Web libraries | TanStack Query 5, TanStack Table 9, React Hook Form 7, Zod 4, Zustand 5, Recharts 3, Axios 1.21 |
| Mobile | Flutter 3.50.0 + Dart 3.14 (FVM-pinned), Riverpod 3, go_router 18, Dio 5, FCM 17, flutter_secure_storage 11, Freezed 4 |
| Database | MySQL 8 / MariaDB, `DECIMAL(18,4)` USD money columns (display 2dp), `DECIMAL(12,4)` rate, largest-remainder allocation; transactional ledger tables |
| Queue / cache | Redis (database queue fallback for MVP) |
| Packages | Spatie permission + activitylog, Maatwebsite Excel, Laravel Notifications, FCM push |
| Web hosting | **Vercel** — static Vite SPA + `vercel.json` rewrite (`/(.*) → /index.html`), Node 22 LTS, custom domain `app.example.com` |
| Backend hosting | **DigitalOcean Ubuntu VPS** — PHP-FPM, queue workers, scheduler, MySQL, Redis; custom domain `api.example.com` |
| File storage | **Cloudflare R2** — KYC documents, deposit proofs, exports; served via Cloudflare CDN |
| Email | **Brevo** — transactional email via API/SMTP (KYC, deposits, withdrawals, P&L, commissions, security alerts) |

> **Version rationale (locked 2026-11-21):** Laravel 13 (released 2026-03-17) is the only supported major — bug fixes to Q3 2027, security fixes to 2028-03-17. Laravel 12's bug fixes ended 2026-08-13 and security ends 2027-02-24, so it was rejected. **PHP 8.3 is locked everywhere** (local + production) because it is already installed in Laragon and fully supported by Laravel 13; PHP 8.3 security support ends 2027-12-31. Laravel 14 is expected Q1 2027 and requires PHP 8.4, so the 13→14 upgrade later must be paired with a PHP bump. Laravel 15 is not announced.

> **Client/hosting rationale (locked 2026-11-21):** Combination A chosen — React SPA on Vercel (no SSR needed for an authenticated portal), Laravel API + workers on a DigitalOcean Ubuntu VPS (Vercel has no native PHP runtime and cannot host our queue workers/scheduler), Flutter native app. TypeScript 7.0.2 native compiler with the official `@typescript/typescript6` alias for ESLint until 7.1. Flutter 3.50.0 pinned with FVM; new `material_ui`/`cupertino_ui` packages and Swift Package Manager for iOS adopted from day one. Web and API share one root domain (`app.example.com` + `api.example.com`) so Sanctum cookie SPA auth works; mobile uses Sanctum Bearer tokens in `flutter_secure_storage`. Vercel env vars use `VITE_` only for public values — no secrets in the client bundle.

## 4. Deferred / Later

- Agent tier names, commission rate values, MLM depth (admin-configured at runtime).
- Payment gateway integration (post-MVP).
- Admin-defined commission deduction types.
- Admin approvals, biometric login, and other mobile features; live/real-time trading updates (future heavy server).
- USD stored to 2 decimals (minor default to confirm).
- Additional reports and notifications as needs arise.
- Laravel 13 → 14 upgrade after Q1 2027 release and ecosystem maturity (single-major, non-urgent).
- Public SEO/marketing site (separate small Next.js app later if needed).
- TypeScript 6 tooling alias removed once TS 7.1 ships the compiler API.
- PHP 8.3 → 8.4 upgrade, paired with the Laravel 13 → 14 upgrade (PHP 8.3 security ends 2027-12-31).

## 5. Suggested Implementation Sequence

1. **Foundation:** Laravel project, schema + immutable ledger, auth + RBAC, Filament admin skeleton.
2. **Money core:** wallets, exchange rate, manual deposits, withdrawals, capital release, agent credit, internal transfers.
3. **Investment core:** symbols, positions, eligibility, distribution job, P&L reports.
4. **KYC workflow + approvals.**
5. **MLM commissions** (rules, vesting, downline, auto-promotion).
6. **Notifications + audit hardening.**
7. **React web portal.**
8. **Flutter mobile v1.**
9. **Exports, configurable dashboards, polish, testing.**

## 6. Delivery & Promotion Workflow (Locked)

**Rule: the complete system is built and proven on localhost before any production deployment.**

1. **Phase 1 — Localhost (Laragon, Windows):** all backend, admin, web, and mobile development happens here against a local MySQL/Redis. Use synthetic users, fake KYC documents, and test payments only. No production credentials, no real customer data.
2. **Phase 2 — Local verification gate (mandatory before promotion):**
   - Fresh-database migration from scratch + `php artisan db:seed --class=DatabaseSeeder` succeed (TST-13 — every feature has a seeder).
   - Full automated suite green: Laravel feature/unit tests (ledger reconciliation, distribution, commissions, withdrawals), React build/typecheck, Flutter analyze/test.
   - Manual end-to-end walkthrough of every money path in the browser and admin panel: deposit approval → wallet → investment → P&L distribution → profit withdrawal → capital release → agent credit → commission vesting.
   - Ledger reconciliation query confirms `credits − debits = balance` for every wallet.
   - Zero known P0/P1 defects; decisions in `docs/State_Ledger.md` marked implemented.
3. **Phase 3 — Staging (recommended, production-mirroring):** separate DigitalOcean host/env, separate Cloudflare R2 bucket + CDN path, Brevo **test** sender, separate DB/Redis, non-production domains. Mobile gets a staging API flavor. Run the same verification gate here.
4. **Phase 4 — Production:** deploy only after sign-off. Migrations run as an explicit CI/release step; secrets set in server `.env`/Vercel dashboard; no direct DB edits; rollout observed with logs/alerts.

**Mobile testing is a separate track:** Flutter is validated independently with unit/widget/integration tests, emulator + simulator runs, and physical-device testing; it consumes a flavor-specific API endpoint (`dev` → localhost, `staging`, `prod`). Mobile builds are promoted separately from web/backend, and store distribution (TestFlight / Play internal testing) comes only after the app passes its own gate. No mobile build may point at production during development.
