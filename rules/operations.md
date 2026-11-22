# rules/operations.md — OPS rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: OPS-01..11
# Does NOT contain: code rules, money policy, table inventory
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

OPS-01 | Promotion | Localhost → local verification gate → staging mirror → production sign-off | `fp-local-verification-gate`
OPS-02 | Hosting | Vercel static SPA `app.example.com`; DigitalOcean Ubuntu API+workers+MySQL+Redis `api.example.com`; R2+CDN uploads; Brevo email | infra review
OPS-03 | Release | Every deploy is a git-tagged release with notes and rollback plan | tag + notes
OPS-04 | Migrations | `php artisan migrate --force` as separate CI/release step; never at startup; no direct DB edits | CI log
OPS-05 | Workers | Restart queue workers after deploy; failed-job queue empty; Horizon healthy | `supervisorctl status`
OPS-06 | Smoke tests | Health endpoint, login, one read API call, one queued job after deploy | curl + job log
OPS-07 | Monitoring/backup | Alerts on failed jobs, 5xx, reconciliation breaks; encrypted backups + restore drill | monitoring config
OPS-08 | Ledger archive | Keep last **4 detailed entries** + queue in `docs/State_Ledger.md`; rotate older details to `docs/State_Ledger_Archive.md`; decisions never archived | archive pointer
OPS-09 | Evidence | Never claim deploy success without command output, smoke results, queue status (`verification-before-completion`) | evidence log
OPS-10 | Build queue | `docs/State_Ledger.md` holds the summary; `docs/Build_Queue_Detail.md` holds one task card per commit. Load **exactly one card** at a time — never the whole detail file | card scope = one commit
OPS-11 | Work package DONE | A work package is not DONE until its module health widget is visible and drillable in Filament (REP-09/10) | widget present + `fp-admin-report-health` check

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
