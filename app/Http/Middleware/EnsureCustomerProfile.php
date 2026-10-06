<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopping routes (orders, addresses, checkout, reviews, the customer API)
 * read `$request->user()->customer`. Make sure it exists, so a staff or
 * admin-created account doesn't 500 the first time it opens one.
 */
class EnsureCustomerProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->user()?->customerProfile();

        return $next($request);
    }
}
