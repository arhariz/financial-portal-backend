---
name: fp-auth-kyc
description: Locked auth, RBAC and KYC rules (plan Sections 1-2) — roles, permission matrix, multi-role users, Sanctum auth modes, admin-configurable verification, KYC document checklist per user type, approval workflow, suspension. Use when implementing login, registration, roles, permissions, KYC documents, approval queues, or account status.
---

# Auth, Roles & KYC (plan §§1–2 — locked)

## Roles & permissions

- Roles: Super Admin (Owner), Admin, KYC Reviewer, P&L Operator, Finance/Withdrawal Manager, Credit Manager, Commission Manager, Support, 3 Agent tiers, Investor.
- Users may hold **multiple roles**. Predefined permission sets with **per-user overrides** by Super Admin.
- Permission matrix from `docs/plan.md` §1 is locked; **Support is view-only** (no money operations).
- Agent tier changes: admin assignment + config-driven auto-promotion; admin-only link changes with audit.

## Auth

- SPA: Sanctum **cookie mode** (`app.example.com` + `api.example.com`, `supports_credentials: true`, `SESSION_DOMAIN=.example.com`, `withCredentials` + `withXSRFToken`).
- Mobile: Sanctum **Bearer tokens** stored in `flutter_secure_storage` only. Never localStorage for tokens.
- Registration: optional agent/referral code. Verification channel (email or phone) is **admin-configurable**; admin can verify manually. 2FA optional for all.

## KYC

- Required for **investors and agents**; staff/admin exempt.
- Document checklist is **admin-configurable per user type**.
- **Gates investing and withdrawal** — registration/deposit allowed before approval. Enforcement belongs in the service layer, not only UI.
- Workflow: Submit → Pending → Approve / Reject (with reason) → Resubmit.
- Admin can suspend/freeze accounts with reason; suspension blocks all transactions.

## Testing checklist

Role matrix for every endpoint, multi-role user, KYC gate on invest/withdraw, resubmission flow, suspension blocking money flows, cookie vs Bearer auth paths.
