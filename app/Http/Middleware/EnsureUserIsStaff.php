<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasAnyRole([
            'Super Admin', 'Admin', 'Store Manager', 'Inventory Manager',
            'Order Manager', 'Delivery Manager', 'Delivery Agent',
        ])) {
            abort(403, 'This area is for GroceryHub staff only.');
        }

        return $next($request);
    }
}
