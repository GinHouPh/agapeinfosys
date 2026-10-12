<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Enquiry form.
     */
    public function create(): View
    {
        return view('contact');
    }

    /**
     * Persist an enquiry and confirm submission.
     */
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return redirect()
            ->route('contact.create')
            ->with('status', 'Thank you — your message has landed. Our team will reply within one business day.');
    }
}
