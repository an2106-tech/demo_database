<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployerAccount
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('auth.login', ['role' => 'employer']);
        }

        // HR/admin users can switch between candidate/employer menus. If they're currently in candidate
        // mode, keep them in candidate routes to avoid confusing mixed dashboards.
        if (in_array($user->role, ['hr', 'admin'], true) && session('client_menu_type') === 'candidate') {
            return redirect()->route('candidates.browse_job');
        }

        if (in_array($user->role, ['hr', 'admin'], true)) {
            return $next($request);
        }

        $metadata = is_array($user->metadata) ? $user->metadata : [];
        $accountTypes = is_array($metadata['account_types'] ?? null) ? $metadata['account_types'] : [];
        if (in_array('employer', $accountTypes, true)) {
            return $next($request);
        }

        return redirect()->route('home');
    }
}
