# rules/documentation.md — DOC rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: DOC-01..08
# Does NOT contain: code rules, executed state, business policy
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

DOC-01 | File map | `docs/DOCS_MANIFEST.md` owns read order, owners, authority; module loading per task | manifest review
DOC-02 | Each doc | Header names Authority, Last Updated, Source of truth, Does NOT contain, Conflict rule, Status | header check
DOC-03 | Duplicates | No generated `IMPLEMENTATION-SUMMARY.md` / `FINAL-REPORT.md`; update the canonical file | repo grep
DOC-04 | Naming | kebab-case docs, snake_case DB, PascalCase PHP, camelCase TS/Dart, `fp-*` custom skills | naming review
DOC-05 | Prompts | Detailed and unlimited; `docs/Prompt_Library.md` + ACK block + rule routing | prompt review
DOC-06 | Size | No arbitrary limits on code, prompts or documents; split by topic only when hard to navigate | design review
DOC-07 | Decisions | Locked changes require a Decision ID in `docs/State_Ledger.md`; supersede, never erase | ledger citation
DOC-08 | Closeout | Update module status, snapshot, queue, last-4 entries; archive rotated details; report evidence | `fp-task-closeout`

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
