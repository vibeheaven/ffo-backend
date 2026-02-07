<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if ($input) {
            array_walk_recursive($input, function (&$value) {
                if (is_string($value)) {
                    // Strip PHP and HTML tags
                    $value = strip_tags($value);
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }
}
