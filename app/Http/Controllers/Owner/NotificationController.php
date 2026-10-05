<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->paginate(15);

        return view('owner.notifications.index', compact('notifications'));
    }

    public function read(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification->getKey());
        $notification->markAsRead();

        return back();
    }
}
