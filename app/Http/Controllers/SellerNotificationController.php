<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('seller.notifications.index', compact('notifications'));
    }
}
