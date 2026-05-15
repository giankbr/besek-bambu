<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderAccessible
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if ($order instanceof Order && ! $this->canAccess($request, $order)) {
            abort(404);
        }

        return $next($request);
    }

    private function canAccess(Request $request, Order $order): bool
    {
        if ($request->hasValidSignature()) {
            return true;
        }

        $user = $request->user();

        if ($user) {
            if ($user->is_admin) {
                return true;
            }

            if ($order->user_id !== null && (int) $order->user_id === (int) $user->id) {
                return true;
            }
        }

        $accessible = session('accessible_order_numbers', []);

        return is_array($accessible) && in_array($order->number, $accessible, true);
    }
}
