# STATE_LEDGER_ARCHIVE.md — Rotated detailed entries
# Authority: Owner / CTO — append only; never edit past entries.
# Last Updated: 2026-11-23 (D-059)
# Source of truth for: history rows rotated out of the active ledger
# Active ledger: docs/State_Ledger.md — decisions D-xxx are NEVER archived.
# Retention: active ledger keeps last 4 detailed entries + snapshot + queue.
# Status: ARCHIVE

---

## Archived entry log (through D-055)

| Entry | Date | Detail |
|---|---|---|
| D-000a | 2026-11-21 | Plan finalized and locked across 10 sections. |
| D-000b | 2026-11-21 | Stack selected: recommended stack with MySQL. |
| D-000c | 2026-11-21 | Created 6 context/planning files. |
| D-020 | 2026-11-21 | CTO/CFO/CEO review: Laravel 12 rejected; Laravel 13 + PHP 8.4 + Filament 5 locked (PHP part later superseded). |
| D-021/022 | 2026-11-21 | Frontend/app/hosting review: Combination A, Vercel + DigitalOcean + R2 + Brevo. |
| D-023 | 2026-11-21 | Delivery rule: localhost-first gate, staging mirror, separate mobile test track. |
| D-024 | 2026-11-21 | Verified Laragon tools; PHP 8.3 locked locally + production; ngrok noted for mobile device testing. |
| D-025 | 2026-11-21 | Installed 43 skills (35 external + 8 custom); created docs/Skills_Registry.md. |
| D-026 | 2026-11-21 | Research round 2: +20 skills → 63 total; skipped live-market/BI tools. |
| D-027 | 2026-11-21 | Governance: Constitution + 8 role rule files, task routing, prompt contract, ledger archive. |
| D-027b | 2026-11-21 | Owner correction: no arbitrary code/prompt/document size limits (GOV-05); external skill size advice advisory only. |
| D-028 | 2026-11-21 | arhariz-style governance adoption: ACK block, GOV gates, immutable rule IDs, forbidden commands, execution locks, feature flags, snapshot/queue. |
| D-029 | 2026-11-21 | Q-001 resolved: USD `DECIMAL(18,4)` internal, 2dp display, largest-remainder; MON-01/08 added. |
| D-030 | 2026-11-21 | Q-002 resolved: commission deductions = loss_adjustment / reversal / admin_adjustment (COM-07). |
| D-031 | 2026-11-21 | Q-003 resolved: Seed / Growth / Master Investor tier names (COM-08); `rules/money.md` created. |
| D-032 | 2026-11-21 | Quality gates locked: PHPStan level 6 + Larastan zero-baseline, Pint --test, ESLint/tsc/build, Flutter analyze/test, gitleaks/Trivy/Semgrep, OWASP ASVS L2/L3. |
| D-033 | 2026-11-21 | Build queue split into 70 task cards (now 78 with WP9); one card loaded at a time (OPS-10). |
| D-034 | 2026-11-21 | Admin observability: 12 health widgets, 8-tab Report section, 11-point sign-off; WP9 + REP-09..11 + OPS-11. |
| D-035 | 2026-11-21 | Prompt Library aligned to build queue: WP1…WP9 prompts + exact `WPn-Tnn` card ID in ACK; generic patterns moved to optional Part 3. |
| D-036 | 2026-11-21 | Docs relocated to `docs/` (13 files, 270 reference substitutions across 34 files); Laravel 13.43.0 installed on PHP 8.3.31, MySQL 8.4.3 (`financial_portal`), database queue/session/cache; npm 60 packages; `vite build` exit 0; `/` and `/up` return 200. |
| D-037 | 2026-11-21 | Local URL = `http://forexbd.test`; Apache vhost created (docroot `public/`), hosts entry added; `financial_portal.test` vhost repointed off the project root (was exposing `.env`); verified `/` 200, `/up` 200, `/.env` 404. |
| D-038 | 2026-11-21 | WP1-T01 DONE: `GET /health` returns JSON (200); `php artisan --version` = 13.43.0. IMPLEMENTED MODULES now lists Foundation. Next card: WP1-T02. |
| D-039 | 2026-11-21 | WP1-T02 DONE: Filament 5.11.4 panel live at `/admin`; `User implements FilamentUser` via `can()` gate; superadmin `admin@forexbd.test` seeded; unauth 302 / login 200 / auth 200. Next card: WP1-T03. |
| D-040 | 2026-11-21 | WP1-T03 DONE: Spatie 8.5 + activitylog 4.12; 30 permissions / 12 roles; `permissions:sync --check` exit 0; panel access now permission-driven; deny-by-default verified. Next card: WP1-T04. |
| D-041 | 2026-11-21 | WP1-T04 DONE: PHPStan L6 + Larastan zero-baseline (0 errors), Pint PSR-12, PHPUnit 8/8 (65 assertions); 7 initial PHPStan errors fixed at source. Next card: WP1-T05. |
| D-042 | 2026-11-21 | TST-13 seeder coverage added (rules 151 → 152); WP10 seeder/verification package (8 cards) queued, total 78 → 86. Next card: WP1-T05. |
| D-043 | 2026-11-21 | WP10-T01/T02 DONE: `DatabaseSeeder` + RBAC + user seeders; 30/12/13 seeded, idempotent; PHPUnit 11/11 (106 assertions). Next card: **WP1-T05** / **WP10-T03**. |
| D-044 | 2026-11-21 | WP3-T07 DONE: Filament `UserResource` + `UserPolicy` (users.view/manage); admin 200 / support 403; `users.view` removed from 4 non-admin roles per plan §1. PHPUnit 15/15 (108). Next card: **WP1-T05**. |
| D-045 | 2026-11-21 | Q-005/Q-006/Q-007 logged (role management, per-user overrides, user profile); WP3-T08…T10 queued blocked; WP3 7 → 10, total 87 → 90. |
| D-046 | 2026-11-21 | Host `C:` hit 0 bytes free; cleared ~660 MB regenerable cache, reinstalled Pint 1.32.3; gates green again (Pint/PHPStan/PHPUnit 15/15); logged as **Q-008**. |
| D-047 | 2026-11-21 | WP1-T05 DONE: Sanctum v4.5 + API skeleton — `routes/api.php`, explicit `config/cors.php`, `User` gains `HasApiTokens`, `bootstrap/app.php` registers `api` routes + `statefulApi()`; gates green. Next card: **WP1-T06**. |
| D-048 | 2026-11-21 | WP1-T06 DONE: App layer structure — `app/Actions`, `app/Repositories`, `app/Enums`, `app/Services`, `tests/Unit/*`; autoload verified; gates green. Next card: **WP1-T07**. |
| D-049 | 2026-11-22 | UNIFIED SESSION START added to `docs/Prompt_Library.md` Part 0: find next queue card → ACK → STOP and ask START → 5-point pre-check gate → implement one card. Stale root duplicate removed. |
| D-050 | 2026-11-22 | WP1-T07 pre-check gate FAILED (no git repo; `C:` 0.24 GB < 2 GB; scanners absent; no Boost). Owner halted the card; **WP11 — Repository & environment setup** (5 cards) queued; stale BUILD_QUEUE cells corrected; manifest card count 78→95. No code written. Next card: **WP11-T01**. |
| D-051 | 2026-11-22 | Owner: GitHub account `arhariz` for frontend + backend repos (different-project context); never touch other project files. WP11-T05 remains GOV-01-blocked on repo URL/default branch; commit identity TBD. |
| D-052 | 2026-11-23 | Backend repo `https://github.com/arhariz/financial-portal-backend` created (private, `main`) and pushed (`7f3f38f`, `b77fbe8`). `gh` 2.105.0 installed; auth reused from Desktop credential. Secret scanning + branch protection blocked by free plan → Q-009. Frontend repo not created. |
| D-053 | 2026-11-23 | Owner made repo **PUBLIC**; enabled secret scanning + push protection and branch protection on `main` (1 review, code-owner, no force-push/delete). `enforce_admins=false` after single-owner self-approval deadlock; PR #1 squash-merged. **Q-009 CLOSED**. |
| D-054 | 2026-11-23 | **WP11-T01 DONE** — disk gate re-run green (C: 3.19 GB > 2 GB), no cache deletion needed; **Q-008 CLOSED**. Next card: **WP11-T03**. |
| D-055 | 2026-11-23 | **WP11-T03 DONE** — gitleaks 8.30.1, Trivy 0.76.2, Semgrep 1.186.0 installed + on PATH; all version commands exit 0. Gate E satisfied. Next card: **WP11-T04**. |

## Archived implemented modules

None — implementation has not started.

## Archive policy

1. Rotate only detailed entry rows beyond the active ledger's last 4; keep the queue and snapshot current.
2. Leave a pointer in `docs/State_Ledger.md`: `Archived history → docs/State_Ledger_Archive.md (through <entry>)`.
3. **Never archive decisions** D-001…D-nnn; they remain in the active ledger with supersession notes.
4. Append only; no edits to past rows.
