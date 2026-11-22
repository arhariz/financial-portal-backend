---
name: fp-mobile-test-track
description: Separate Flutter test track from plan Section 6 and Constitution 19e. Use when building, testing, or promoting any mobile feature. Triggers on Flutter, mobile, emulator, simulator, physical device, flavor, dev/staging/prod API, ngrok, FCM, TestFlight, Play internal testing.
---

# Mobile Test Track (separate from web/backend)

Source: `docs/plan.md` §6, `docs/Constitution.md` 19e, `docs/project_Context.md` §6.

- Flutter 3.50 + Dart 3.14 pinned with **FVM**. New `material_ui`/`cupertino_ui` packages and Swift Package Manager for iOS.
- Flavors: `dev` → localhost API, `staging` → staging API, `prod` → production API. **Dev builds must never point at production.**
- Physical devices cannot reach `localhost` directly — use Laragon's **ngrok** HTTPS tunnel for dev-flavor device testing.
- Auth: Sanctum Bearer token in `flutter_secure_storage`; no token in localStorage/plain prefs.
- Uploads (KYC docs, proofs) go to Cloudflare R2 **via the API**, never directly from the app with secrets.
- FCM push events configured in `notification_templates`; validate on staging only during tests.
- Tests: unit for pure logic, widget tests for UI states, one integration test per happy path; `flutter analyze` clean.
- Validate on emulator + simulator and list physical-device checks. Biometric login is **deferred** (not in v1).
- Mobile builds are promoted on their **own gate**, independent of web/backend sign-off. Store distribution (TestFlight / Play internal testing) only after the gate passes.
