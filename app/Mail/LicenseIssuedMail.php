<?php

namespace App\Mail;

use App\Models\License;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseIssuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly License $license) {}

    public function envelope(): Envelope
    {
        $productName = $this->license->product?->name ?? 'Software';

        return new Envelope(
            subject: "Your {$productName} License Key — Agape Central Licensing",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.license-issued',
            with: [
                'license' => $this->license->loadMissing('product.latestRelease'),
                'business' => Setting::structured('business', []),
                'contact_email' => (string) Setting::value('contact_email', 'hello@agapeinfosys.com'),
            ],
        );
    }
}
