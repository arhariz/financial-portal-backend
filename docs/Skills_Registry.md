# Skills Registry — Financial Investments Portal

Installed **2026-11-21**, before any implementation. Skills are project-scoped and load automatically when their trigger matches. External skills live in `.agents/skills/` (52); custom project skills live in `.opencode/skills/` (12). **Total: 64.**

> **Authority:** `fp-project-context`, `fp-money-ledger` + `docs/Constitution.md` override generic skill advice whenever they conflict (e.g. no float money, no deleting ledger rows, no client-side business logic).

## 1. Custom project skills (`.opencode/skills/`) — 12

| Skill | Purpose |
|---|---|
| `fp-project-context` | Must-load context at the start of every task; points to plan/context/constitution/schema/ledger. |
| `fp-money-ledger` | DECIMAL money, immutable ledger, atomic money flows, reconciliation, no negative balances, Asia/Dhaka dates. |
| `fp-pnl-distribution` | Locked Section 4: positions, T+2/weekend eligibility, per-symbol distribution, current-capital split, profit wallet vs loss to capital. |
| `fp-commission-mlm` | Locked Section 5: downline, tier×level matrix, events, platform cost, net base, vesting, clawback, auto-promotion. |
| `fp-auth-kyc` | Locked Sections 1–2: roles/permission matrix, multi-role, Sanctum cookie vs Bearer, KYC workflow, suspension. |
| `fp-local-verification-gate` | Mandatory localhost-first gate before staging/production; fresh migrations, tests, money-path E2E, reconciliation. |
| `fp-mobile-test-track` | Separate Flutter test track: flavors, ngrok device testing, secure token storage, FCM on staging, independent promotion. |
| `fp-task-closeout` | Post-task protocol: update State_Ledger, Schema_Registry, and report summary. |
| `fp-ceo-review` | CEO-hat strategic summary: phase, traction proxies, top risks, decisions needed, one next action. |
| `fp-cfo-review` | CFO-hat: AUM, wallet balances, net flow, distributions, commission liability, withdrawal pressure, reconciliation health. |
| `fp-cto-review` | CTO-hat: architecture compliance, money integrity, queues, security, test gates, deployment posture, risk register. |
| `fp-admin-report-health` | Final Report-section health audit: 12 widgets, 8 tabs, 11-point sign-off, HEALTHY/NOT HEALTHY verdict. |

## 2. External skills (`.agents/skills/`) — 52

### Laravel / backend / API / testing
- From `AsyrafHussin/agent-skills` (14): `laravel-best-practices`, `laravel-database-optimization`, `laravel-queues`, `laravel-testing`, `laravel-owasp-security`, `api-design-patterns`, `project-docs`, `technical-debt`, `testing-best-practices`, `e2e-playwright-testing`, `state-management`, `typescript-react-patterns`, `tailwind-best-practices`, `react-vite-best-practices`

### Security, review & workflow
- `waybarrios/opencode-power-pack` (7): `code-reviewer`, `security-review`, `security-threat-model`, `sharp-edges`, `insecure-defaults`, `feature-dev`, `skill-creator`
- `obra/superpowers` (4): `systematic-debugging`, `requesting-code-review`, `receiving-code-review`, `verification-before-completion`
- `mattpocock/skills` (3): `code-review`, `grill-me`, `setup-pre-commit`
- `addyosmani/agent-skills` (2): `debugging-and-error-recovery`, `frontend-ui-engineering`

### Flutter / Dart
- `flutter/skills` official (8): `flutter-apply-architecture-best-practices`, `flutter-add-widget-test`, `flutter-add-integration-test`, `flutter-setup-declarative-routing`, `dart-run-static-analysis`, `flutter-build-responsive-layout`, `flutter-implement-json-serialization`, `dart-generate-test-mocks`
- `evanca/flutter-ai-rules` (6): `flutter-best-practices`, `effective-dart`, `riverpod`, `firebase-messaging`, `firebase-app-check`, `flutterfire-configure`

### Financial services / reporting
- `anthropics/financial-services` (7): `gl-recon`, `break-trace`, `roll-forward`, `variance-commentary`, `client-report`, `kyc-doc-parse`, `kyc-rules`
- `alirezarezvani/claude-skills` (1): `cfo-advisor`

**Count check:** 14 + 7 + 4 + 3 + 2 + 8 + 6 + 7 + 1 = 52.

## 3. Source repositories & verified stars (2026-11-21)

| Repo | Stars | Role |
|---|---:|---|
| `laravel/agent-skills` | 741 | Official Laravel skills (researched; not installed — overlap) |
| `AsyrafHussin/agent-skills` | 103 | Exact Laravel/React/TS/testing/security stack match |
| `flutter/skills` | 3,199 | Official Flutter skills |
| `evanca/flutter-ai-rules` | 674 | Docs-derived Flutter/Riverpod/Firebase rules |
| `waybarrios/opencode-power-pack` | 558 | Built for OpenCode; security + review workflows |
| `obra/superpowers` | 323k | Systematic debugging, review, verification discipline |
| `mattpocock/skills` | 329k | Code review, plan grilling, pre-commit setup |
| `addyosmani/agent-skills` | 119k | Frontend UI engineering, debugging recovery |
| `anthropics/financial-services` | 41k | GL reconciliation, break trace, KYC parsing/rules, client reports |
| `alirezarezvani/claude-skills` | 30k | CFO advisor |

### Researched but deliberately not installed
| Repo / tool | Stars | Why skipped |
|---|---:|---|
| `mattpocock/skills` full pack | 329k | Only 3 relevant skills needed; avoid context noise |
| `Leonxlnx/taste-skill` | 119k | We already have `frontend-design` + UI engineering |
| `vercel-labs/agent-browser` / `browser-use` | 53k / 122k | Playwright skill already installed |
| `himself65/finance-skills` | 3.6k | skills.sh listed skills that do not exist in repo; remaining skills are equity-research oriented — not our model |
| `OpenBB`, `qlib`, `ccxt`, `yfinance` | 76k / 50k / 45k / 26k | Real-market/live-data tools; our P&L is staff-entered. Bookmark for future |
| `metabase`, `superset`, `duckdb` | 50k / 76k / 43k | Future BI; Filament covers MVP reports |
| Security tools (Trivy 40k, Gitleaks 31k, Trufflehog 30k, Semgrep 18k, PHPStan 14k, Larastan 6.6k, Pest 12k, Rector 11k) | — | CI/dev dependencies, not agent skills — add during implementation |

## 4. Reinstall / update

```bash
# Reinstall any external skill with:
npx skills@latest add <repo> -a opencode -y --copy --skill <name> --skill <name2>

npx skills@latest update          # update external skills
npx skills@latest list --json     # list CLI-tracked skills (external only)
```

Custom `fp-*` skills are plain `SKILL.md` files under `.opencode/skills/<name>/` — edit them directly.

## 5. Usage guidance

- Let skills auto-trigger; do not invoke all of them at once.
- Every session pastes the ACK block; every task loads `docs/DOCS_MANIFEST.md`, `docs/State_Ledger.md`, `docs/EXECUTION_LOCKS_ACTIVE.md`, `docs/Constitution.md` + **only the routed `rules/*.md` file(s)** (GOV-07) — this is the token-cost design.
- Prompts are detailed and unlimited; code has no arbitrary size limits. Health comes from cohesion, tests, and review.
- For financial code, pair a generic skill with `fp-money-ledger` so locked rules win.
- External skills may suggest line-count limits; they are advisory and **overridden by GOV-05** — no arbitrary code size limits.
- `gl-recon`/`break-trace`/`roll-forward` use GL terminology — map to our `wallet_transactions`/`wallets` ledger when applying them.
- Review external skills before trusting them — they run with full agent permissions.
- Do not install more skills without a concrete need; extra auto-triggering skills add context noise.
