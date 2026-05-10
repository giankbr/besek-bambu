<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function show(Order $order)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin;
        $isOwner = $user && $order->user_id === $user->id;

        abort_if(! $isAdmin && ! $isOwner, 404);

        return view('invoice.show', [
            'order' => $order->load('items'),
        ]);
    }
}
