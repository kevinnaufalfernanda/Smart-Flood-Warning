<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $alerts = $query->paginate(15);

        $totalUnread = Alert::unread()->count();
        $dangerCount = Alert::where('type', 'danger')->count();
        $warningCount = Alert::where('type', 'warning')->count();

        return view('alerts.index', compact('alerts', 'totalUnread', 'dangerCount', 'warningCount'));
    }

    public function show($id)
    {
        $alert = Alert::findOrFail($id);

        if (!$alert->is_read) {
            $alert->update(['is_read' => true]);
        }

        return view('alerts.show', compact('alert'));
    }

    public function markAsRead($id)
    {
        Alert::findOrFail($id)->update(['is_read' => true]);
        return back()->with('success', 'Alert ditandai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        Alert::unread()->update(['is_read' => true]);
        return back()->with('success', 'Semua alert ditandai sudah dibaca.');
    }
}
