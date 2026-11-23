<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WalletType;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Resources\InternalTransferResource;
use App\Models\User;
use App\Services\InternalTransferService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class TransferController extends Controller
{
    public function __construct(private readonly InternalTransferService $transfers)
    {
    }

    public function store(StoreTransferRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        try {
            $result = $this->transfers->transfer($user, [
                'from' => WalletType::from((string) $validated['from_wallet_type']),
                'to' => WalletType::from((string) $validated['to_wallet_type']),
                'amount_usd' => (string) $validated['amount_usd'],
                'symbol_id' => isset($validated['symbol_id']) ? (int) $validated['symbol_id'] : null,
            ], $request->header('Idempotency-Key'));
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [],
                'code' => 'transfer_rejected',
            ], 422);
        }

        return response()->json([
            'data' => new InternalTransferResource($result['transfer']),
        ], $result['created'] ? 201 : 200);
    }
}
