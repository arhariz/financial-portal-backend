# docs/Admin_Observability_Spec.md — Module health widgets and the Report section
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-034)
# Source of truth for: widget catalogue, health states, drill-downs, final report sign-off
# Does NOT contain: code rules (rules/*.md), business policy (docs/EXECUTION_LOCKS_ACTIVE.md), executed state (STATE_LEDGER)
# Conflict rule: STATE_LEDGER wins for state; CONSTITUTION for code; EXECUTION_LOCKS for policy.
# Status: ACTIVE

---

## PRINCIPLE

Every implemented module MUST be visible in the Filament admin panel through a **health widget**. A work package is not DONE until its widget is present and drillable (OPS-11). Widgets are read-only by default; destructive actions are forbidden (career-ending commands). Support sees view-only widgets (AUTH-01).

## FILAMENT REPORT SECTION

Create a Filament page group **Reports** with tabs:
1. **Overview** — all module health widgets at a glance.
2. **Ledger & Wallets** — reconciliation, wallet balances, transaction drill-down.
3. **Investment & P&L** — symbols, positions, eligibility, distribution runs.
4. **Agents & Commissions** — downline, tiers, vesting, clawback.
5. **KYC & Security** — review queue, failed logins, suspensions, audit log.
6. **Money Flow** — deposits, withdrawals, capital releases, AUM, net flow.
7. **Operations** — queue health, notifications, R2, backups, exchange rate freshness.
8. **Exports** — CSV/Excel report generation with filters.

Layout is configurable per role via `dashboard_layouts` (FIL-04). Each widget links to its module's resource/action for drill-down.

## WIDGET CATALOGUE

| Widget | Module | Data source | Green | Amber | Red | Drill-down | Owner role |
|---|---|---|---|---|---|---|---|
| Ledger Reconciliation | Wallets / Ledger | `wallets` vs `wallet_transactions` | all wallets reconcile | any break < 1% of balance | any unreconciled money wallet | wallet detail + ledger rows | Finance Manager |
| Queue Health | Queues | `jobs`, `failed_jobs`, Horizon | no failed, oldest < 5m | oldest 5–30m | failed > 0 or oldest > 30m | failed job list | P&L Operator |
| P&L Distribution Status | Investment | `pnl_distributions` by `trade_date` | today's active symbols distributed | missing symbol/date | failed/reversed unresolved | distribution detail + items | P&L Operator |
| Deposit Approval Queue | Money flow | `manual_deposits` pending | none or < 24h | 24–48h | > 48h pending | approval resource | Credit Manager |
| Withdrawal & Capital Release | Wallets | `withdrawal_requests`, `capital_release_requests` | none / within 24h SLA | approaching SLA | overdue release or paid mismatch | request resource | Finance Manager |
| KYC Review Queue | KYC | `kyc_documents` pending/rejected | no backlog | pending > 48h | pending > 72h or resubmit loop | KYC review page | KYC Reviewer |
| Agent & Commission Health | MLM | `agents`, `commissions`, `commission_rules` | no overdue vesting | vesting due soon | negative base / clawback mismatch | commission resource | Commission Manager |
| Exchange Rate & Symbols | Config | `exchange_rates`, `symbols` | rate < 24h old, symbols active | stale rate | no active symbol | settings page | Admin |
| Notification Delivery | Notifications | `notifications` by status | no failed | queued > 1h | failed > threshold | notification log | Admin |
| Security & Audit | Security | `audit_logs`, `login_history` | normal activity | failed-login spike | money action without audit row | audit log | Admin |
| Storage & Backup | Operations | R2 health check, backup log | backup < 24h | backup < 48h | missing backup / R2 unreachable | ops page | Admin |
| Money Flow Summary | Reports | deposits − withdrawals, AUM | informational | net flow negative 3 days | reconciliation red | flow report | CFO / Admin |

## HEALTH STATE RULES

- **Green** — module operating within locked rules.
- **Amber** — SLA/rule at risk; must have an owner and a note in `docs/State_Ledger.md` §Known Issues.
- **Red** — a Constitution/locked-rule violation or unresolved money discrepancy. Red stops final sign-off.
- Every widget displays: state, last updated timestamp, count/metric, owner, and a `View` drill-down link.
- Support role gets view-only widgets; no approve/correct buttons.

## FINAL REPORT SECTION SIGN-OFF (end of project)

The Report section is "healthy" only when ALL of the following pass in one session:

1. All 12 widgets are present in the Reports page group, each with green state or a documented accepted amber with CTO decision.
2. Ledger reconciliation widget: zero breaks across all wallets; distribution items sum to posted P&L.
3. Queue Health: zero failed jobs, oldest pending job < 5m.
4. P&L Distribution: every active symbol has a distribution for the latest trade date (or an explicit paused reason).
5. KYC, Deposit, Withdrawal queues: no item beyond SLA.
6. Commission: no negative base, no overdue vesting, clawback exposures listed.
7. Notification delivery: zero failed events for critical templates.
8. Security: every money action in the last 24h has an audit row; no unexplained failed-login spike.
9. Storage/backup: R2 reachable, latest encrypted backup < 24h, restore drill recorded.
10. CSV/Excel exports work for all five report types with role gating.
11. Evidence captured: command output/screenshots attached to the final `docs/State_Ledger.md` entry via `fp-task-closeout`.

## VERIFICATION

Use the custom skill `fp-admin-report-health` to run this checklist. A red widget means the project is NOT complete, regardless of feature count.
