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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone')->nullable()->after('email');
            $table->string('status', 20)->default('active')->index()->after('password');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('kyc_status', 20)->default('none')->index()->after('status');
            $table->string('referral_code_used', 64)->nullable()->after('kyc_status');
            $table->text('two_factor_secret')->nullable()->after('referral_code_used');
            $table->foreignId('created_by')->nullable()->after('two_factor_secret');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone',
                'status',
                'phone_verified_at',
                'kyc_status',
                'referral_code_used',
                'two_factor_secret',
                'created_by',
            ]);
        });
    }
};
