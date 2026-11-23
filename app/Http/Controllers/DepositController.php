<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProofUrlRequest;
use App\Http\Requests\StoreManualDepositRequest;
use App\Http\Resources\ManualDepositResource;
use App\Models\User;
use App\Services\ManualDepositService;
use App\Services\ProofStorageService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class DepositController extends Controller
{
    public function __construct(
        private readonly ManualDepositService $deposits,
        private readonly ProofStorageService $proofs,
    ) {
    }

    public function store(StoreManualDepositRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        try {
            $result = $this->deposits->submit($user, [
                'amount_bdt' => (string) $validated['amount_bdt'],
                'payment_method' => (string) $validated['payment_method'],
                'proof_path' => (string) $validated['proof_path'],
            ], $request->header('Idempotency-Key'));
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [],
                'code' => 'deposit_rejected',
            ], 422);
        }

        return response()->json([
            'data' => new ManualDepositResource($result['deposit']),
        ], $result['created'] ? 201 : 200);
    }

    public function proofUrl(ProofUrlRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return response()->json([
            'data' => $this->proofs->createUpload($user),
        ]);
    }
}
