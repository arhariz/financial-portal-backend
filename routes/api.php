<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AgentCreditController;
use App\Http\Controllers\CapitalReleaseController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [RegisterController::class, 'store'])->name('api.v1.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.login');
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
    Route::middleware('auth:sanctum')->post('/deposits/proof-url', [DepositController::class, 'proofUrl'])->name('api.v1.deposits.proof-url');
    Route::middleware('auth:sanctum')->post('/deposits', [DepositController::class, 'store'])->name('api.v1.deposits.store');
    Route::middleware('auth:sanctum')->post('/withdrawals', [WithdrawalController::class, 'store'])->name('api.v1.withdrawals.store');
    Route::middleware('auth:sanctum')->post('/capital-releases', [CapitalReleaseController::class, 'store'])->name('api.v1.capital-releases.store');
    Route::middleware('auth:sanctum')->post('/transfers', [TransferController::class, 'store'])->name('api.v1.transfers.store');
    Route::middleware('auth:sanctum')->post('/agent-credits', [AgentCreditController::class, 'store'])->name('api.v1.agent-credits.store');
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => app()->environment(),
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'timestamp' => now()->toIso8601String(),
    ]))->name('api.v1.health');

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    })->name('api.v1.user');
});
