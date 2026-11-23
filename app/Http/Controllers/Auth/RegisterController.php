<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;

class RegisterController extends \Illuminate\Routing\Controller
{
    public function __construct(private readonly RegistrationService $service)
    {
    }

    public function store(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->validated());

        return response()->json(['user' => $user->load('roles')], 201);
    }
}
