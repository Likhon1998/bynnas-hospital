<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(): View
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20);

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function update(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['is_read' => ! $contactMessage->is_read]);

        return back()->with('status', $contactMessage->is_read ? 'Message marked as read.' : 'Message marked as unread.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')->with('status', 'Message deleted.');
    }
}
