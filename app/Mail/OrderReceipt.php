<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Receipt for a single-plan checkout, sent once when an order is paid.
 */
class OrderReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your receipt — order '.$this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order-receipt',
            with: [
                'order' => $this->order->loadMissing(['product', 'plan', 'gateway']),
                'business' => Setting::structured('business', []),
                'contact_email' => (string) Setting::value('contact_email', 'hello@agapeinfosys.com'),
            ],
        );
    }
}
