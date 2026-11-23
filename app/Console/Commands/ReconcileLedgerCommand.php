<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\LedgerReconciliationService;
use Illuminate\Console\Command;

class ReconcileLedgerCommand extends Command
{
    protected $signature = 'ledger:reconcile {--json : Emit the report as JSON instead of a table}';

    protected $description = 'Verify that credits minus debits equal the balance of every wallet';

    public function handle(LedgerReconciliationService $service): int
    {
        $result = $service->reconcile();

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_THROW_ON_ERROR));
        } else {
            $this->table(
                ['Wallet', 'Owner', 'Type', 'Balance', 'Expected', 'Difference', 'Status'],
                collect($result['wallets'])->map(fn (array $row): array => [
                    $row['id'],
                    $row['userId'],
                    $row['type'],
                    $row['balance'],
                    $row['expected'],
                    $row['difference'],
                    $row['inBreak'] ? 'BREAK' : 'OK',
                ])->all()
            );
            $this->line(sprintf(
                'Wallets checked: %d | Breaks: %d | Checked at: %s',
                count($result['wallets']),
                $result['breakCount'],
                $result['checkedAt']
            ));
        }

        return $result['breakCount'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
