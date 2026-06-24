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
            return redirect()->route('employers.login');
        }

        if (! $user->is_active) {
            return redirect()
                ->route('employers.login')
                ->with('status', 'Tài khoản nhà tuyển dụng chưa được kích hoạt.');
        }

        if (in_array($user->role, ['hr', 'admin', 'director', 'pm'], true)) {
            return $next($request);
        }

        return redirect()->route('employers.portal');
    }
}
