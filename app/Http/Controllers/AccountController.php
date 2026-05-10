<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->withCount('items')
            ->get();

        return view('account.index', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function orders(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('account.orders', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 404);

        return view('account.order', [
            'order' => $order->load('items'),
        ]);
    }
}
