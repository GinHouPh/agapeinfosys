<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\ShopOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Receipt for a cart checkout, sent once for the whole order.
 */
class ShopOrderReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ShopOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your receipt — order '.$this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.shop-order-receipt',
            with: [
                'order' => $this->order->loadMissing(['items', 'gateway']),
                'business' => Setting::structured('business', []),
                'contact_email' => (string) Setting::value('contact_email', 'hello@agapeinfosys.com'),
            ],
        );
    }
}
