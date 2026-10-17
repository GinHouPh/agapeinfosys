@extends('mail.layout')

@section('title', 'Receipt — order '.$order->reference)

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px;color:#ffffff;">Payment received</h1>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#c7c7cd;">
        Hi {{ $order->name ?: 'there' }}, thank you for your purchase. This is the receipt for your
        cart order <strong style="color:#ffffff;font-family:monospace;">{{ $order->reference }}</strong>.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        @foreach ($order->items as $item)
            <tr>
                <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;">
                    {{ $item->product_name }}
                    <span style="color:#8a8a93;">— {{ $item->plan_name }}</span>
                </td>
                <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;white-space:nowrap;">
                    {{ $item->formattedUnitPrice() }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td style="padding:16px 0 0;color:#ffffff;font-weight:700;">Total</td>
            <td style="padding:16px 0 0;color:#d90429;text-align:right;font-size:18px;font-weight:700;">{{ $order->formattedAmount() }}</td>
        </tr>
    </table>

    <p style="margin:24px 0 0;font-size:12px;line-height:1.7;color:#8a8a93;">
        Payment method: {{ $order->gateway?->name ?? '—' }} &middot;
        Paid on {{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}.
        Access is active for account <strong style="color:#c7c7cd;">{{ $order->subscriber_key }}</strong>.
    </p>
@endsection
