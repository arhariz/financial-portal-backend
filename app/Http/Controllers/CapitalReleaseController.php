<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCapitalReleaseRequest;
use App\Http\Resources\CapitalReleaseResource;
use App\Models\User;
use App\Services\CapitalReleaseService;
use Illuminate\Http\JsonResponse;

final class CapitalReleaseController extends Controller
{
    public function __construct(private readonly CapitalReleaseService $releases)
    {
    }

    public function store(StoreCapitalReleaseRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        $result = $this->releases->request($user, [
            'amount_usd' => (string) $validated['amount_usd'],
        ], $request->header('Idempotency-Key'));

        return response()->json([
            'data' => new CapitalReleaseResource($result['release']),
        ], $result['created'] ? 201 : 200);
    }
}
