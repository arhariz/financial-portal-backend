<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentCreditRequest;
use App\Http\Resources\AgentCreditPurchaseResource;
use App\Models\User;
use App\Services\AgentCreditService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

final class AgentCreditController extends Controller
{
    public function __construct(private readonly AgentCreditService $credits)
    {
    }

    public function store(StoreAgentCreditRequest $request): JsonResponse
    {
        $admin = $request->user();

        if (! $admin instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        try {
            $result = $this->credits->purchase($admin, [
                'agent' => User::findOrFail((int) $validated['agent_id']),
                'amount_bdt' => (string) $validated['amount_bdt'],
            ], $request->header('Idempotency-Key'));
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [],
                'code' => 'agent_credit_rejected',
            ], 422);
        }

        return response()->json([
            'data' => new AgentCreditPurchaseResource($result['purchase']),
        ], $result['created'] ? 201 : 200);
    }
}
