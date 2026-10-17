<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title>@yield('title', $business['brand_name'] ?? config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background:#0b0b0c;font-family:Arial,Helvetica,sans-serif;color:#e5e5e5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b0b0c;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#141416;border:1px solid #26262b;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;border-bottom:1px solid #26262b;">
                            <span style="font-size:18px;font-weight:700;letter-spacing:0.08em;color:#ffffff;">
                                {{ $business['brand_name'] ?? config('app.name') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;border-top:1px solid #26262b;font-size:12px;line-height:1.7;color:#8a8a93;">
                            @if (! empty($business['legal_name']))
                                <p style="margin:0 0 4px;">{{ $business['legal_name'] }}</p>
                            @endif
                            @if (! empty($business['tax_number']))
                                <p style="margin:0 0 4px;">Tax / GST: {{ $business['tax_number'] }}</p>
                            @endif
                            @if (! empty($business['registration_number']))
                                <p style="margin:0 0 4px;">Registration: {{ $business['registration_number'] }}</p>
                            @endif
                            <p style="margin:0;">
                                Questions? Email
                                <a href="mailto:{{ $contact_email }}" style="color:#d90429;text-decoration:none;">{{ $contact_email }}</a>.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
