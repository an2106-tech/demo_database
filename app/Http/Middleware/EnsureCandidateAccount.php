<?php

namespace App\Http\Middleware;

use App\Services\CandidateAccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidateAccount
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('candidates.login');
        }

        if (app(CandidateAccountService::class)->hasCandidateAccount($user)) {
            return $next($request);
        }

        $params = [];
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            $params['next_route'] = $routeName;
        }

        return redirect()->route('candidates.register', $params);
    }
}
