<?php

namespace App\Http\Middleware;

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
            return redirect()->route('auth.login', ['role' => 'candidate']);
        }

        if (in_array($user->role, ['candidate', 'pm'], true)) {
            return $next($request);
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        if (in_array('candidate', $accountTypes, true)) {
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
