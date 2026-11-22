# Financial Investments Portal — Backend

Laravel 13 + Filament 5 backend for the company-managed, forex-style investment portal.
Returns are staff-entered daily P&L (no live trading). All money is stored as `DECIMAL(18,4)`.

## Stack
- PHP 8.3, Laravel 13.43, Filament 5.11, Spatie Permission + Activitylog
- MySQL 8.4 (`financial_portal`), Redis 8.2 (queue/session/cache)
- Local URL: `http://forexbd.test`

## Local setup
```powershell
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
npm install
npm run build
php artisan serve
```

## Verification (run before claiming any change is done)
```powershell
php artisan test
php vendor/bin/phpstan analyse --memory-limit=2G
php vendor/bin/pint --test
php artisan permissions:sync --check
```

## Governance
- `docs/DOCS_MANIFEST.md` — file map and read order
- `docs/State_Ledger.md` — current snapshot, build queue, decisions
- `docs/Constitution.md` — ACK block, GOV gates, forbidden commands
- `rules/*.md` — immutable rule codes (MON, WAL, PNL, COM, SEC, TST, OPS, REP, FE, MOB, DOC)

**Never run** `migrate:fresh`, `db:wipe`, bare `db:seed`, or `composer update` against dev/production.
Every feature ships an idempotent seeder; invoke only as `php artisan db:seed --class=<Name>Seeder`.

## Repository notes
- Private repository; owner: `arhariz` (`arhariz.com@gmail.com`).
- Branch protection and secret scanning require GitHub Pro or a public repo — pending owner decision.
