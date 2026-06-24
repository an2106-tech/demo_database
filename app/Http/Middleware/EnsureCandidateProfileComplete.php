<?php

namespace App\Http\Middleware;

use App\Services\CandidateAccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidateProfileComplete
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

        $service = app(CandidateAccountService::class);
        $candidate = $service->resolveFor($user);

        if ($service->isProfileReadyForApplication($candidate)) {
            return $next($request);
        }

        return redirect()
            ->route('candidates.candidate_profile')
            ->with('status', 'Vui lòng hoàn thiện hồ sơ ứng viên trước khi ứng tuyển.')
            ->with('profile_incomplete', $service->missingApplicationProfileFields($candidate));
    }
}
