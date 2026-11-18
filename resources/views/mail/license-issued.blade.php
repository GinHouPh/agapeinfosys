@extends('mail.layout')

@section('title', 'Your Software License Key')

@section('content')
    <h1 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">
        Your {{ $license->product?->name }} License Key
    </h1>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.7;color:#b3b3ba;">
        Hello {{ $license->buyer_username ?: 'Customer' }}, thank you for your purchase. Your software license has been successfully issued and is ready for activation on your server.
    </p>

    <!-- License Key Box -->
    <div style="background:#0b0b0c;border:1px solid #d90429;border-radius:8px;padding:20px;margin-bottom:24px;text-align:center;">
        <span style="display:block;font-size:11px;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#8a8a93;margin-bottom:8px;">
            Your License Key
        </span>
        <code style="display:inline-block;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:0.05em;font-family:monospace;padding:6px 12px;background:#141416;border-radius:4px;">
            {{ $license->license_key }}
        </code>
        <span style="display:block;font-size:12px;color:#8a8a93;margin-top:8px;">
            Tier: <strong style="color:#ffffff;text-transform:uppercase;">{{ $license->tier }}</strong> &bull; Allowed Domains: <strong style="color:#ffffff;">{{ $license->max_domains }}</strong>
        </span>
    </div>

    <!-- Details Table -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;font-size:13px;line-height:1.6;">
        <tr>
            <td style="padding:8px 0;color:#8a8a93;border-bottom:1px solid #26262b;">Software Product</td>
            <td style="padding:8px 0;color:#ffffff;text-align:right;border-bottom:1px solid #26262b;font-weight:600;">
                {{ $license->product?->name }}
            </td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#8a8a93;border-bottom:1px solid #26262b;">Customer Email</td>
            <td style="padding:8px 0;color:#ffffff;text-align:right;border-bottom:1px solid #26262b;font-family:monospace;">
                {{ $license->buyer_email }}
            </td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#8a8a93;border-bottom:1px solid #26262b;">Support Expiry</td>
            <td style="padding:8px 0;color:#ffffff;text-align:right;border-bottom:1px solid #26262b;">
                {{ $license->support_until ? $license->support_until->format('d M Y') : 'Included' }}
            </td>
        </tr>
        <tr>
            <td style="padding:8px 0;color:#8a8a93;">License Duration</td>
            <td style="padding:8px 0;color:#ffffff;text-align:right;">
                {{ $license->expires_at ? $license->expires_at->format('d M Y') : 'Perpetual (No Expiration)' }}
            </td>
        </tr>
    </table>

    <!-- Activation Guide Instructions -->
    <h3 style="margin:0 0 12px;font-size:14px;font-weight:700;color:#ffffff;letter-spacing:0.05em;text-transform:uppercase;">
        Installation &amp; Activation Steps
    </h3>
    <ol style="margin:0 0 28px;padding-left:20px;font-size:13px;line-height:1.8;color:#b3b3ba;">
        <li>Upload the software files to your web hosting or server.</li>
        <li>Open your browser and navigate to <code style="color:#d90429;background:#0b0b0c;padding:2px 4px;border-radius:3px;">https://yourdomain.com/install</code>.</li>
        <li>Enter your database credentials and proceed to the License Verification step.</li>
        <li>Paste the license key shown above and complete the setup wizard.</li>
    </ol>

    <div style="text-align:center;margin-top:28px;">
        <a href="{{ route('dashboard.index') }}" style="display:inline-block;background:#d90429;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 24px;border-radius:6px;letter-spacing:0.04em;">
            Open Client Dashboard &rarr;
        </a>
    </div>
@endsection
