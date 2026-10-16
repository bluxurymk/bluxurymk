<?php

namespace App\Http\Middleware;

use App\Services\SalonSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the admin-edited salon settings on top of `config/salon.php`.
 */
class ApplySalonSettings
{
    public function __construct(private SalonSettings $settings) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->settings->applyToConfig();

        return $next($request);
    }
}
