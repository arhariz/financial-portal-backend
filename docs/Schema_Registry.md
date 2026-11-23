# Schema Registry — Financial Investments Portal

Canonical registry of database design. This is a **design contract**, not yet-implemented migrations. Any structural change must be recorded in `docs/State_Ledger.md` and reflected here.

## 1. Conventions

- `bigint unsigned` primary keys named `id`; foreign keys `<table>_id`.
- `snake_case` for all table and column names.
- Money: `DECIMAL(18,4)` for **all USD balances/amounts** (internal precision 4dp, display 2dp); `DECIMAL(12,4)` for exchange rates; `DECIMAL(10,6)` for ratios/percentages. **Never FLOAT/DOUBLE.** (MON-01, D-029)
- Rounding: proportional distributions/commissions use **largest-remainder allocation at 4dp**; leftover goes to the largest share, ties by position id. Items must sum exactly to the posted amount (MON-08).
- Timestamps: `created_at`, `updated_at` (UTC). Dates that use business rules: `DATE` columns interpreted in Asia/Dhaka.
- Soft deletes on user-facing records; **financial ledger and distribution records are append-only (never deleted, never updated)** — corrections are new compensating rows.
- Every money-moving table carries `wallet_transaction_id` linking to the immutable ledger.
- Status fields are `VARCHAR` enums with values documented below; validate at application layer + DB CHECK where practical.
- Spatie `permission` standard tables are used for RBAC (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`).

## 2. Table Registry

### Identity & access
| Table | Key columns | Notes |
|---|---|---|
| `users` | id, name, email (unique), phone, password, status (`active`/`suspended`), email_verified_at, phone_verified_at, kyc_status (`none`/`pending`/`approved`/`rejected`), referral_code_used, two_factor_secret, created_by, timestamps, softDeletes | Multi-role via Spatie. |
| `roles` | Spatie standard + slug (`super_admin`, `admin`, `kyc_reviewer`, `pnl_operator`, `finance_manager`, `credit_manager`, `commission_manager`, `support`, `agent_tier_1..3`, `investor`) | Defined in plan §1. |
| `user_profiles` | id, user_id, full_name, date_of_birth, address, city, country, id_number, timestamps | KYC data captured from admin-configurable fields. |

### KYC
| Table | Key columns | Notes |
|---|---|---|
| `kyc_document_types` | id, user_type (`investor`/`agent`), name, is_required, sort_order, active | Admin-configurable checklist. |
| `kyc_documents` | id, user_id, type_id, file_path, status (`pending`/`approved`/`rejected`), reviewed_by, reviewed_at, rejection_reason, timestamps | Rejection allows resubmit. |

### Wallets & ledger
| Table | Key columns | Notes |
|---|---|---|
| `wallets` | id, user_id, type (`cash`/`investment`/`profit`/`commission`), currency (`USD`), balance DECIMAL(18,4), timestamps | Unique (user_id, type). Balance updated only via ledger transaction. |
| `wallet_transactions` | id, uuid, wallet_id, direction (`debit`/`credit`), amount DECIMAL(18,4), balance_after DECIMAL(18,4), type (deposit, withdrawal, transfer_in, transfer_out, invest, profit_distribution, loss_adjustment, commission, role_bonus, capital_release, reversal, adjustment), reference_type, reference_id, counterparty_wallet_id, created_by, created_at | **Immutable append-only ledger.** |
| `internal_transfers` | id, user_id, from_wallet_type, to_wallet_type, amount_usd DECIMAL(18,4), symbol_id (nullable), position_id (nullable), idempotency_key (nullable, unique with user_id), created_at | Idempotent user transfer request; references the two immutable ledger rows and any new position. |

### Money operations
| Table | Key columns | Notes |
|---|---|---|
| `exchange_rates` | id, bdt_per_usd DECIMAL(12,4), effective_from, set_by, created_at | History retained. |
| `manual_deposits` | id, user_id, amount_usd, amount_bdt, exchange_rate_id, payment_method, proof_path, idempotency_key (nullable, unique with user_id), status (`pending`/`approved`/`rejected`), reviewed_by, reviewed_at, rejection_reason, wallet_transaction_id, timestamps | Manual BDT deposit; submit is idempotent (API-06), approval credits the cash wallet once. |
| `withdrawal_requests` | id, user_id, source_wallet_type (`cash`/`profit`), amount_usd, payout_method (bank/crypto), payout_details JSON, idempotency_key (nullable, unique with user_id), status (`pending`/`approved`/`rejected`/`paid`), requested_at, approved_by, approved_at, paid_at, rejection_reason, wallet_transaction_id | Admin-configurable payout methods; approval is intent-only, payment posts the single immutable debit. |
| `capital_release_requests` | id, user_id, amount_usd, idempotency_key (nullable, unique with user_id), status (`pending`/`approved`/`rejected`/`released`), requested_at, approved_by, approved_at, release_at (nullable until approval; +24h from approval), released_at, wallet_transaction_id | Investment→cash release via queued job, then separate withdrawal; `release_at` null while pending/rejected (GOV-02 D-092). |

### Investment & P&L
| Table | Key columns | Notes |
|---|---|---|
| `symbols` | id, code (unique), name, category (`metal`/`crypto`/`energy`/`forex`), status (`active`/`paused`), min_investment, created_by, timestamps | Admin CRUD. |
| `positions` | id, user_id, symbol_id, capital DECIMAL(18,4), original_amount DECIMAL(18,4), investment_date DATE, eligible_date DATE, status (`active`/`closed`), source (`cash`/`profit`/`commission`), timestamps | eligible_date computed by T+2 / weekend rule; `commission` source added for WAL-05 (D-093). |
| `pnl_distributions` | id, symbol_id, trade_date DATE, profit_loss DECIMAL(18,4) (signed), entry_by, status (`draft`/`distributed`/`reversed`), distributed_at, reversal_of_id, timestamps | One per symbol + trade date. |
| `pnl_distribution_items` | id, distribution_id, position_id, user_id, share_ratio DECIMAL(10,6), amount DECIMAL(18,4), wallet_transaction_id, created_at | Correction = new distribution with reversal_of_id, not deletion. |

### Agents & commissions
| Table | Key columns | Notes |
|---|---|---|
| `agents` | id, user_id, referral_code (unique), tier (1/2/3), upline_agent_id (nullable), status, total_sales DECIMAL(18,4), active_referrals_count, created_at | Tier 1/2/3 display labels are **Seed Investor / Growth Investor / Master Investor** (config, not code). (COM-08, D-031) |
| `referrals` | id, agent_id, referred_user_id, level, created_at | Downline edge; level = distance from referring agent. |
| `deduction_profiles` | id, name, rules JSON, created_by, timestamps | Admin-defined deductions for net commission base. Enabled by default: `loss_adjustment`, `reversal`, `admin_adjustment` (mandatory note). New types require a Decision. Defaults seeded idempotently by the migration via `config/deduction_types.php` (unique `name`). (COM-07, D-030) |
| `commission_rules` | id, event (`investment`/`profit_distribution`/`credit_purchase`), agent_tier (1/2/3), level INT, rate DECIMAL(10,6), enabled, holding_period_hours, deduction_profile_id, timestamps | Matrix tier × level. |
| `commissions` | id, agent_id, rule_id, event, source_user_id, source_reference_type, source_reference_id, base_amount DECIMAL(18,4), amount DECIMAL(18,4), status (`pending`/`vested`/`cancelled`/`reversed`), vest_at, wallet_transaction_id, timestamps | Clawback on early withdrawal. Append-only rows — corrections are reversal rows (MON-03). |

### Notifications, dashboards & audit
| Table | Key columns | Notes |
|---|---|---|
| `notification_templates` | id, `key`, channel (`email`/`sms`/`in_app`/`push`), subject, body, variables JSON, enabled | Admin-editable. |
| `notifications` | id, user_id, template_key, channel, payload JSON, status (`queued`/`sent`/`failed`), sent_at, created_at | |
| `notification_preferences` | id, user_id, channel, event, enabled | Security alerts cannot be disabled — enforced by `config/notification_events.php` (`security_alert.mandatory = true`). |
| `dashboard_layouts` | id, role_id, user_id (nullable), widgets JSON, updated_by, timestamps | Per-role default + per-user override. |
| `audit_logs` | id, actor_id, actor_role, action, subject_type, subject_id, ip, user_agent, changes JSON, created_at | All staff actions; append-only (SEC-07), no updates or deletes. |
| `login_history` | id, user_id, ip, user_agent, device, logged_in_at, logged_out_at | |

## 3. Money Flow Invariants

1. A wallet balance changes **only** by inserting a `wallet_transactions` row and updating the wallet in the same DB transaction.
2. Sum of all credits minus debits for a wallet must equal its balance (reconcilable at any time).
3. Investment capital = sum of active `positions.capital` (the `investment` wallet balance is a maintained aggregate for reconciliation).
4. No negative wallet balances; a loss debits the profit wallet first up to its available balance, then debits the remainder from position capital — each debit is an immutable `loss_adjustment` row referencing the distribution. If profit wallet + remaining capital cannot cover the loss, block the P&L entry for admin correction (D-064).
5. Reversals reference the original entry via `reversal_of_id` / `reference_id` and never delete rows.
