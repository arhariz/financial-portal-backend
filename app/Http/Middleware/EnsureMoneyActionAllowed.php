<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\GuardService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMoneyActionAllowed
{
    public function __construct(private readonly GuardService $guard)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $this->guard->ensureCanInvest($user);

        return $next($request);
    }
}
