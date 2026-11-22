<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Permissions\Manifest;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync {--check : Report drift only and exit non-zero when out of sync}';

    protected $description = 'Create roles and permissions from config/role_matrix.php and assign them';

    public function handle(): int
    {
        if ($this->option('check')) {
            $issues = Manifest::check();

            if ($issues !== []) {
                $this->error('RBAC drift detected:');
                foreach ($issues as $issue) {
                    $this->line('  - '.$issue);
                }

                return self::FAILURE;
            }

            $this->info('RBAC in sync: roles and permissions match config/role_matrix.php');

            return self::SUCCESS;
        }

        $result = Manifest::sync();

        $this->info(sprintf(
            'permissions:sync done - %d permissions, %d roles, %d permissions created, %d roles created, %d assignment sets updated',
            count(Manifest::permissions()),
            count(Manifest::roles()),
            $result['permissions_created'],
            $result['roles_created'],
            $result['assignments_updated'],
        ));

        return self::SUCCESS;
    }
}
