# rules/mobile.md — MOB rules
# Authority: Owner / CTO
# Last Updated: 2026-11-21 (D-028)
# Source of truth for: MOB-01..13
# Does NOT contain: backend rules, money policy, web frontend rules
# Conflict rule: STATE_LEDGER for state; CONSTITUTION for code; EXECUTION_LOCKS for business policy.
# Status: ACTIVE

---

MOB-01 | SDK | Flutter 3.50 + Dart 3.14 pinned with FVM; `flutter analyze` clean | `fvm flutter --version`
MOB-02 | iOS build | Swift Package Manager (CocoaPods trunk read-only) | iOS build
MOB-03 | UI packages | Standalone `material_ui` / `cupertino_ui`; bundled widgets deprecated | pubspec
MOB-04 | Flavors | `dev` → localhost via ngrok, `staging`, `prod`; dev never targets production | flavor config
MOB-05 | State | Riverpod 3; immutable state; dispose resources; no logic in widgets | provider test
MOB-06 | Navigation | go_router named routes + auth redirects | route test
MOB-07 | HTTP | Dio interceptors: flavor base URL, Bearer from secure storage, standardized errors | client test
MOB-08 | Models | Freezed + json_serializable; money fields are `String`, never `double` | model test
MOB-09 | Money | No arithmetic in Dart; display only API values | grep + review
MOB-10 | Token storage | `flutter_secure_storage` only; never SharedPreferences/plain files | storage test
MOB-11 | Uploads | KYC docs/proofs to R2 **via API**; no embedded credentials | upload test
MOB-12 | Push/FCM | firebase_messaging + flutterfire-configure per flavor; validate on staging only | push test
MOB-13 | Promotion | Unit + widget + integration tests, emulator/simulator + physical device; own gate independent of web | `fp-mobile-test-track`

---

*Root: `docs/Constitution.md`. State: `docs/State_Ledger.md`. Policy: `docs/EXECUTION_LOCKS_ACTIVE.md`.*
