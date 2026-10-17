@extends('mail.layout')

@section('title', 'Receipt — order '.$order->reference)

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px;color:#ffffff;">Payment received</h1>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#c7c7cd;">
        Hi {{ $order->name ?: 'there' }}, thank you for your purchase. This is your receipt for
        <strong style="color:#ffffff;">{{ $order->product?->name ?? 'your order' }}</strong>@if ($order->plan) ({{ $order->plan->name }})@endif.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#8a8a93;">Reference</td>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;font-family:monospace;">{{ $order->reference }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#8a8a93;">Product</td>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;">{{ $order->product?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#8a8a93;">Plan</td>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;">{{ $order->plan?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#8a8a93;">Payment method</td>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;">{{ $order->gateway?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#8a8a93;">Paid on</td>
            <td style="padding:10px 0;border-bottom:1px solid #26262b;color:#ffffff;text-align:right;">{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:16px 0 0;color:#ffffff;font-weight:700;">Total</td>
            <td style="padding:16px 0 0;color:#d90429;text-align:right;font-size:18px;font-weight:700;">{{ $order->formattedAmount() }}</td>
        </tr>
    </table>

    @if ($order->product?->domain)
        <p style="margin:28px 0 0;">
            <a href="https://{{ $order->product->domain }}" style="display:inline-block;padding:12px 22px;background:#d90429;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:700;font-size:14px;">
                Open {{ $order->product->name }}
            </a>
        </p>
    @endif

    <p style="margin:24px 0 0;font-size:12px;line-height:1.7;color:#8a8a93;">
        Your access is active for account <strong style="color:#c7c7cd;">{{ $order->subscriber_key }}</strong>.
        Keep this email for your records.
    </p>
@endsection
