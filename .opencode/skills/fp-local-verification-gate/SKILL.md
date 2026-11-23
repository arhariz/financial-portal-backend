---
name: fp-local-verification-gate
description: Mandatory localhost-first verification gate from plan Section 6 and Constitution 19d. Use before any staging or production deployment, and when asked to verify, test, or sign off the complete system. Triggers on deploy, release, verify, sign-off, staging, production, fresh migration, ledger reconciliation, end-to-end money test.
---

# Local Verification Gate (mandatory before deploy)

Source: `docs/plan.md` §6, `docs/Constitution.md` 19d. **Never deploy before all gates pass.**

## Gates

1. **Fresh database:** drop/recreate local MySQL DB, run all migrations from scratch, run seeders. No reliance on an old DB state.
2. **Automated suite green (exact commands):**
   - `php artisan test` — backend feature/unit tests, ledger reconciliation, distribution, commissions, withdrawals.
   - `php vendor/bin/phpstan analyse --memory-limit=2G` — **level 6 + Larastan, zero baseline**, stop on any error.
   - `php vendor/bin/pint --test` — PSR-12 check (no auto-fix in the gate).
   - `npx tsc --noEmit` + `npx eslint . --max-warnings 0` + `npm run build`.
   - `fvm flutter analyze` + `fvm flutter test` on the dev flavor.
   - `gitleaks detect` + `trivy fs .` (or image scan) + Semgrep — no secrets/CVEs.
3. **Manual money-path E2E with synthetic data only:**
   - manual deposit submission + admin approval → cash wallet
   - invest → position created with correct eligibility date
   - staff enters symbol P&L + Distribute → profit to profit wallet / loss to profit wallet first, then position capital (D-064)
   - profit transfer → new position; profit withdrawal
   - capital release request → approval → 24h → cash wallet → separate withdrawal
   - agent credit purchase → cash + commission bonus; in-app credit transfer to investor
   - MLM commission vesting + clawback on early withdrawal
   - KYC submit → reviewer approve → invest/withdraw unlock
4. **Ledger reconciliation:** for every wallet, `sum(credits) − sum(debits) = balance`. Distribution items sum exactly to entered P&L.
5. **Cleanliness:** no production credentials, no real KYC documents, no mobile build pointing at production, no outstanding P0/P1 defects.

Report PASS/FAIL per gate with evidence. Staging mirrors production with isolated DB/Redis/R2 bucket/Brevo test sender. Production migrations run as an explicit CI/release step; never direct DB edits.
