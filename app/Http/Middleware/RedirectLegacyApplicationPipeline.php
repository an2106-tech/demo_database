<?php

namespace App\Http\Middleware;

use App\Filament\Resources\Applications\ApplicationResource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyApplicationPipeline
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return redirect()->to(ApplicationResource::getUrl('kanban'));
    }
}
