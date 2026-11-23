<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentCreditTransferRequest;
use App\Http\Resources\AgentCreditTransferResource;
use App\Models\User;
use App\Services\AgentCreditTransferService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

final class AgentCreditTransferController extends Controller
{
    public function __construct(private readonly AgentCreditTransferService $transfers)
    {
    }

    public function store(StoreAgentCreditTransferRequest $request): JsonResponse
    {
        $agent = $request->user();

        if (! $agent instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        try {
            $result = $this->transfers->transfer($agent, [
                'investor' => User::findOrFail((int) $validated['investor_id']),
                'amount_usd' => (string) $validated['amount_usd'],
            ], $request->header('Idempotency-Key'));
        } catch (InvalidArgumentException $exception) {
            // Insufficient funds surface as a generic field error rather than
            // a stack trace (API-04/SEC-09).
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => ['amount_usd' => [$exception->getMessage()]],
                'code' => 'transfer_rejected',
            ], 422);
        }

        return response()->json([
            'data' => new AgentCreditTransferResource($result['transfer']),
        ], $result['created'] ? 201 : 200);
    }
}
