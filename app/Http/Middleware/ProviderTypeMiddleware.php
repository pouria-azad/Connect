<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ProviderTypeService;

class ProviderTypeMiddleware
{
    protected $providerTypeService;

    public function __construct(ProviderTypeService $providerTypeService)
    {
        $this->providerTypeService = $providerTypeService;
    }

    public function handle(Request $request, Closure $next, string $type = null)
    {
        if (!$this->providerTypeService->validate($type)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
} 