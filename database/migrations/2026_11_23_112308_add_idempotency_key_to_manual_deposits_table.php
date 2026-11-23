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
        Schema::table('manual_deposits', function (Blueprint $table): void {
            $table->string('idempotency_key', 64)->nullable()->after('proof_path');
            $table->unique(['user_id', 'idempotency_key'], 'manual_deposits_user_id_idempotency_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_deposits', function (Blueprint $table): void {
            $table->dropUnique('manual_deposits_user_id_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
