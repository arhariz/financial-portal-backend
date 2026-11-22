# rules/security.md — SEC rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: SEC-01..09
# Does NOT contain: money policy, database structure, code style, operations
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

SEC-01 | Any action/route | Deny by default; check `docs/plan.md` §1 permission matrix with `can()` | policy test
SEC-02 | Invest / withdraw | KYC approval checked in the **service layer**, not only UI | service test
SEC-03 | Suspended account | Blocks all money actions even if other permissions allow | suspension test
SEC-04 | SPA auth | Sanctum cookie: exact stateful domains, `SESSION_DOMAIN=.example.com`, `supports_credentials: true`, CSRF cookie before POST, `withXSRFToken = true` | auth feature test
SEC-05 | Mobile auth | Bearer token in `flutter_secure_storage`; revoke on logout/suspension | token test
SEC-06 | KYC docs / proofs | Cloudflare R2 via signed server URLs; never public bucket or local disk | storage test
SEC-07 | Staff actions | `audit_logs` with actor, role, IP, user agent, changes — mandatory for money actions | audit assertions
SEC-08 | Auth/money endpoints | Rate limiting + CSRF; no bypass middleware | route test
SEC-09 | Security baseline | **OWASP ASVS Level 2** baseline; **Level 3** controls on auth, KYC documents, wallet/ledger and withdrawal paths. Generic errors to clients; no plain PII to third parties | security review + checklist
SEC-10 | Secret/dependency scanning | gitleaks pre-commit + CI, Semgrep SAST, Trivy image/dependency scan; no secret reaches git | CI green + scan report

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
