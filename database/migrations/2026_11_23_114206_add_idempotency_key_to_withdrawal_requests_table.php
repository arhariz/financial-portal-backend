<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->string('idempotency_key', 64)->nullable()->after('payout_details');
            $table->unique(['user_id', 'idempotency_key'], 'withdrawal_requests_user_id_idempotency_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table): void {
            $table->dropUnique('withdrawal_requests_user_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
