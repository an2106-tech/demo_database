<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\CandidateAccountService;
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
            return redirect()->route('auth.login', ['role' => 'candidate']);
        }

        if (app(CandidateAccountService::class)->hasCandidateAccount($user)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $params = ['role' => 'candidate'];
        if (is_string($routeName) && $routeName !== '') {
            $params['next_route'] = $routeName;
        }

        return redirect()->route('auth.sign_up', $params);
    }
}
