<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Inbox of submitted enquiries.
     */
    public function index(Request $request): View
    {
        $inbox = (string) $request->query('inbox', 'all');

        $messages = ContactMessage::query()
            ->when($inbox === 'unread', fn ($query) => $query->unread())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.messages.index', [
            'messages' => $messages,
            'inbox' => $inbox,
            'unreadCount' => ContactMessage::query()->unread()->count(),
        ]);
    }

    /**
     * Read a single enquiry.
     */
    public function show(ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Delete an enquiry.
     */
    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()
            ->route('admin.messages.index')
            ->with('status', 'Message deleted.');
    }
}
