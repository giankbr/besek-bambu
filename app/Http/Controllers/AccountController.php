<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountProfileRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function profile()
    {
        return view('account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('account.profile')
            ->with('status', 'Profil berhasil diperbarui.');
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

    public function track(Order $order, ShippingService $shipping)
    {
        abort_if($order->user_id !== Auth::id(), 404);

        $tracking = [];
        $error = null;

        if (! $order->hasTracking()) {
            $error = 'No tracking number is set for this order yet.';
        } else {
            try {
                $client = $shipping->rajaOngkirClient();
                if (! $client->isConfigured()) {
                    $error = 'Live tracking is unavailable right now.';
                } else {
                    $tracking = $client->trackWaybill(
                        $order->tracking_number,
                        $order->shipping_courier,
                        $order->customer_phone,
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Customer tracking failed', [
                    'order' => $order->number,
                    'error' => $e->getMessage(),
                ]);
                $error = $e->getMessage();
            }
        }

        return view('account.track', [
            'order' => $order,
            'tracking' => $tracking,
            'error' => $error,
        ]);
    }
}
