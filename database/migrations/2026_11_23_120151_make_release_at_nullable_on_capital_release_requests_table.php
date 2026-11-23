<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The 24h release timestamp only exists once an admin approves the
     * request (GOV-02: schema matches the locked D-007 semantics).
     */
    public function up(): void
    {
        Schema::table('capital_release_requests', function (Blueprint $table): void {
            $table->dateTime('release_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capital_release_requests', function (Blueprint $table): void {
            $table->dateTime('release_at')->nullable(false)->change();
        });
    }
};
