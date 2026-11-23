<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * DB-04 gap surfaced by the WP2-T11 schema guard suite: created_by is a FK and must be indexed.
     */
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table): void {
            $table->index('created_by', 'wallet_transactions_created_by_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table): void {
            $table->dropIndex('wallet_transactions_created_by_index');
        });
    }
};
