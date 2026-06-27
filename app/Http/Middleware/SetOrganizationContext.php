<?php

namespace App\Http\Middleware;

use App\Services\Organization\OrganizationContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationContext
{
    public function __construct(
        protected OrganizationContextService $organizationContextService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $organization = $this->organizationContextService->resolveForUser($user);

            if ($organization) {
                $this->organizationContextService->apply($organization);
            }
        }

        return $next($request);
    }
}
