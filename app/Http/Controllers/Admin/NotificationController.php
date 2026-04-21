<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index');
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'trigger_event' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'channels' => 'required|array',
        ]);

        \App\Models\NotificationTemplate::create($request->all());

        return redirect()->back()->with('success', 'Notification template created successfully.');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'event' => 'required|string',
        ]);

        $user = \App\Models\User::find($request->user_id);
        $service = app(\App\Services\NotificationService::class);
        $service->notify($request->event, $user, ['test' => 'data']);

        return redirect()->back()->with('success', 'Test notification sent.');
    }
}
