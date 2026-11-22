# rules/frontend.md — FE rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: FE-01..10
# Does NOT contain: backend rules, money policy, deployment procedure
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

FE-01 | Stack | React 19.3 + Vite 8 + TypeScript 7 + Tailwind 4 on Vercel; `vercel.json` SPA rewrite; Node 22+ | `npm run build`
FE-02 | Structure | `src/app`, `src/features/<domain>`, `src/components/ui`, `src/lib`, `src/types`; no cross-feature raw imports | import review
FE-03 | TypeScript | Strict mode; no `any`, no non-null assertions on API data; TS6 alias for ESLint until 7.1 | `tsc --noEmit`
FE-04 | Server state | TanStack Query for all server data; namespaced query keys; invalidate on mutation | hook review
FE-05 | Client state | Zustand only for token/session + ephemeral UI; never stores money as numbers | store review
FE-06 | Money | Decimal **strings** only; no `parseFloat`, no arithmetic on balances/P&L/commissions | grep + test
FE-07 | HTTP | Axios `baseURL=VITE_API_URL`, `withCredentials=true`, `withXSRFToken=true`; CSRF cookie before stateful POST | request test
FE-08 | UI patterns | RHF + Zod forms; TanStack Table server-side; Recharts; loading/empty/error/denied states | UI test
FE-09 | Accessibility | Semantic HTML, keyboard nav, WCAG 2.2 AA contrast | a11y audit
FE-10 | Secrets/build | `VITE_` public only (API URL); `tsc --noEmit` + build + ESLint clean in CI | CI green

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
