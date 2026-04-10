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

        // HR/admin users can switch between candidate/employer menus. If they're currently in employer
        // mode, keep them in employer routes to avoid confusing mixed dashboards.
        if (in_array($user->role, ['hr', 'admin'], true) && session('client_menu_type') === 'employer') {
            return redirect()->route('employers.dashboard');
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
