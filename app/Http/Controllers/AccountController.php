<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function cancel(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 404);

        if (! in_array($order->status, ['pending'], true) || $order->payment_status === 'paid') {
            return back()->with('status', 'This order can no longer be cancelled.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'unpaid' ? 'failed' : $order->payment_status,
            ]);
        });

        return redirect()->route('account.orders.show', $order)->with('status', 'Order cancelled.');
    }
}
