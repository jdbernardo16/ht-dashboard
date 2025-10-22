<?php

namespace App\Http\Middleware;

use App\Traits\AdministrativeAlertsTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VAMiddleware
{
    use AdministrativeAlertsTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isVA()) {
            // Trigger access violation alert
            $this->triggerAccessViolationAlert(
                $request->route()->getName() ?? $request->path(),
                $request->method(),
                'User attempted to access VA-only resource without privileges',
                [
                    'user_id' => auth()->id(),
                    'user_email' => auth()->user()?->email,
                    'user_role' => auth()->user()?->role,
                    'attempted_route' => $request->route()->getName(),
                    'attempted_path' => $request->path(),
                    'method' => $request->method(),
                ]
            );
            
            abort(403, 'Unauthorized access. Virtual Assistant privileges required.');
        }

        return $next($request);
    }
}