<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deduction_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->json('rules');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_by');
            $table->index('created_at');
        });

        /** @var list<array{name: string, rules: array<string, mixed>}> $defaults */
        $defaults = config('deduction_types.defaults');

        foreach ($defaults as $default) {
            DB::table('deduction_profiles')->insertOrIgnore([
                'name' => $default['name'],
                'rules' => json_encode($default['rules'], JSON_THROW_ON_ERROR),
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_profiles');
    }
};
