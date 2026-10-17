<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\ShopOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Alerts every administrator that a checkout was paid.
 *
 * Handles both the single-plan Order and the cart ShopOrder so one alert is
 * sent per payment regardless of the front door used.
 */
class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order|ShopOrder $order) {}

    /**
     * Queue the alert to every admin account.
     */
    public static function sendToAdmins(Order|ShopOrder $order): void
    {
        $admins = User::query()->where('is_admin', true)->get();

        if ($admins->isNotEmpty()) {
            NotificationFacade::send($admins, (new self($order))->afterCommit());
        }
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New paid order '.$this->order->reference)
            ->greeting('New paid order')
            ->line($this->order->paymentDescription())
            ->line('Amount: '.$this->order->formattedAmount())
            ->line('Customer: '.($this->order->name ?: ($this->order->email ?: 'Guest')))
            ->action('View in admin', $this->adminUrl())
            ->line('Reference: '.$this->order->reference);
    }

    private function adminUrl(): string
    {
        return $this->order instanceof ShopOrder
            ? route('admin.shop-orders.show', $this->order)
            : route('admin.orders.show', $this->order);
    }
}
