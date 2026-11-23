<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WithdrawalSourceWalletType;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Http\Resources\WithdrawalResource;
use App\Models\User;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;

final class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals)
    {
    }

    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        $result = $this->withdrawals->request($user, [
            'amount_usd' => (string) $validated['amount_usd'],
            'source_wallet_type' => WithdrawalSourceWalletType::from((string) $validated['source_wallet_type']),
            'payout_method' => (string) $validated['payout_method'],
            'payout_details' => (array) $validated['payout_details'],
        ], $request->header('Idempotency-Key'));

        return response()->json([
            'data' => new WithdrawalResource($result['withdrawal']),
        ], $result['created'] ? 201 : 200);
    }
}
