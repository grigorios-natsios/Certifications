@php
    $logoPath = \App\Support\Branding::logoPath();
    $logoCid  = ($logoPath && isset($message)) ? $message->embed($logoPath) : null;
    $appName  = config('app.name');
    $org      = $client->organization ?? null;
    $orgName  = $org?->name ?: $appName;
    $orgEmail = $org?->email ?: null;
    $orgPhone = is_array($org?->phones ?? null) ? ($org->phones[0] ?? null) : null;
    $orgWeb   = $org?->website_url ?: null;
    $orgWebLabel = $orgWeb ? rtrim(preg_replace('#^https?://#', '', $orgWeb), '/') : null;
    $orgFacebook  = $org?->facebook_url ?: null;
    $orgInstagram = $org?->instagram_url ?: null;
    $orgYoutube   = $org?->youtube_url ?: null;
    $hasSocials = $orgFacebook || $orgInstagram || $orgYoutube;
@endphp
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $client->full_name ?: 'Πιστοποιητικό' }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,0.04);">

                    {{-- ───── Logo bar ───── --}}
                    <tr>
                        <td align="center" style="padding:26px 28px 18px 28px;background:#ffffff;border-bottom:1px solid #f1f5f9;">
                            @if($logoCid)
                                <img src="{{ $logoCid }}" alt="{{ $orgName }}" height="44"
                                     style="display:block;height:44px;width:auto;border:0;outline:none;text-decoration:none;">
                            @else
                                <p style="margin:0;font-size:16px;font-weight:700;color:#0f172a;letter-spacing:0.01em;">{{ $orgName }}</p>
                            @endif
                        </td>
                    </tr>

                    {{-- ───── Hero (emerald gradient) ───── --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#059669 0%,#047857 55%,#134e4a 100%);padding:30px 28px;text-align:center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:14px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" valign="middle"
                                                    style="width:56px;height:56px;background:rgba(255,255,255,0.18);border-radius:14px;font-family:Arial,Helvetica,sans-serif;font-size:30px;line-height:56px;color:#ffffff;font-weight:700;">
                                                    &#10004;
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 6px 0;font-size:11px;color:#a7f3d0;letter-spacing:0.18em;font-weight:700;text-transform:uppercase;">
                                            Επικυρωμένο
                                        </p>
                                        <h1 style="margin:0;font-size:24px;line-height:1.25;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">
                                            Το πιστοποιητικό σας είναι έτοιμο
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ───── Greeting + intro ───── --}}
                    <tr>
                        <td style="padding:26px 30px 8px 30px;font-size:15px;line-height:1.6;color:#334155;">
                            @if($client->full_name)
                                <p style="margin:0 0 14px 0;">Αγαπητέ/ή <strong style="color:#0f172a;">{{ $client->full_name }}</strong>,</p>
                            @else
                                <p style="margin:0 0 14px 0;">Γεια σας,</p>
                            @endif
                            <p style="margin:0 0 8px 0;">
                                Μπορείτε να δείτε ή να κατεβάσετε το πιστοποιητικό σας πατώντας το κουμπί παρακάτω.
                            </p>
                        </td>
                    </tr>

                    {{-- ───── Primary CTA ───── --}}
                    <tr>
                        <td align="center" style="padding:18px 30px 6px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center"
                                        style="border-radius:8px;background:#059669;box-shadow:0 4px 10px rgba(5,150,105,0.25);">
                                        <a href="{{ $publicUrl }}"
                                           style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;letter-spacing:0.01em;">
                                            Άνοιγμα πιστοποιητικού
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:10px 30px 4px 30px;">
                            <a href="{{ $publicUrl }}"
                               style="display:inline-block;color:#64748b;text-decoration:none;font-size:12px;word-break:break-all;">
                                {{ $publicUrl }}
                            </a>
                        </td>
                    </tr>

                    {{-- ───── Downloads ───── --}}
                    @if(count($downloads))
                        <tr>
                            <td style="padding:24px 30px 0 30px;">
                                <p style="margin:0 0 10px 0;font-size:12px;font-weight:600;color:#475569;letter-spacing:0.04em;">
                                    Λήψη PDF
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 30px 8px 30px;">
                                @foreach($downloads as $d)
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:8px;">
                                        <tr>
                                            <td style="padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="font-size:14px;color:#0f172a;font-weight:500;">{{ $d['name'] }}</td>
                                                        <td align="right">
                                                            <a href="{{ $d['url'] }}"
                                                               style="display:inline-block;color:#047857;text-decoration:none;font-size:13px;font-weight:600;">
                                                                Λήψη PDF →
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                @endforeach
                            </td>
                        </tr>
                    @endif

                    {{-- ───── Help note ───── --}}
                    <tr>
                        <td style="padding:18px 30px 0 30px;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
                                Αν δεν λειτουργεί ο σύνδεσμος, αντιγράψτε τον στον browser σας.
                            </p>
                        </td>
                    </tr>

                    {{-- ───── Footer / org signature ───── --}}
                    <tr>
                        <td style="padding:24px 30px 26px 30px;border-top:1px solid #e2e8f0;">
                            <p style="margin:14px 0 12px 0;font-size:14px;font-weight:700;color:#0f172a;letter-spacing:-0.01em;">{{ $orgName }}</p>

                            {{-- Contact rows --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 4px 0;">
                                @if($orgEmail)
                                    <tr>
                                        <td valign="middle" style="padding:3px 8px 3px 0;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                <td align="center" valign="middle"
                                                    style="width:22px;height:22px;background:#ecfdf5;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:22px;color:#047857;">
                                                    &#9993;
                                                </td>
                                            </tr></table>
                                        </td>
                                        <td valign="middle" style="font-size:13px;color:#334155;line-height:1.5;">
                                            <a href="mailto:{{ $orgEmail }}" style="color:#334155;text-decoration:none;">{{ $orgEmail }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if($orgPhone)
                                    <tr>
                                        <td valign="middle" style="padding:3px 8px 3px 0;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                <td align="center" valign="middle"
                                                    style="width:22px;height:22px;background:#ecfdf5;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:22px;color:#047857;">
                                                    &#9742;
                                                </td>
                                            </tr></table>
                                        </td>
                                        <td valign="middle" style="font-size:13px;color:#334155;line-height:1.5;">
                                            <a href="tel:{{ preg_replace('/\s+/', '', $orgPhone) }}" style="color:#334155;text-decoration:none;">{{ $orgPhone }}</a>
                                        </td>
                                    </tr>
                                @endif
                                @if($orgWeb)
                                    <tr>
                                        <td valign="middle" style="padding:3px 8px 3px 0;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                <td align="center" valign="middle"
                                                    style="width:22px;height:22px;background:#ecfdf5;border-radius:6px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:22px;color:#047857;">
                                                    &#8997;
                                                </td>
                                            </tr></table>
                                        </td>
                                        <td valign="middle" style="font-size:13px;color:#334155;line-height:1.5;">
                                            <a href="{{ $orgWeb }}" style="color:#334155;text-decoration:none;">{{ $orgWebLabel }}</a>
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            @if($hasSocials)
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;">
                                    <tr>
                                        @if($orgFacebook)
                                            <td style="padding-right:8px;">
                                                <a href="{{ $orgFacebook }}" target="_blank" rel="noopener" style="text-decoration:none;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                        <td align="center" valign="middle"
                                                            style="width:30px;height:30px;background:#1877f2;border-radius:50%;font-family:Georgia,'Times New Roman',serif;font-size:18px;line-height:30px;color:#ffffff;font-weight:700;font-style:italic;">
                                                            f
                                                        </td>
                                                    </tr></table>
                                                </a>
                                            </td>
                                        @endif
                                        @if($orgInstagram)
                                            <td style="padding-right:8px;">
                                                <a href="{{ $orgInstagram }}" target="_blank" rel="noopener" style="text-decoration:none;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                        <td align="center" valign="middle"
                                                            style="width:30px;height:30px;background:#e1306c;background:linear-gradient(135deg,#f9ce34 0%,#ee2a7b 50%,#6228d7 100%);border-radius:50%;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:30px;color:#ffffff;font-weight:700;">
                                                            IG
                                                        </td>
                                                    </tr></table>
                                                </a>
                                            </td>
                                        @endif
                                        @if($orgYoutube)
                                            <td style="padding-right:8px;">
                                                <a href="{{ $orgYoutube }}" target="_blank" rel="noopener" style="text-decoration:none;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                                                        <td align="center" valign="middle"
                                                            style="width:30px;height:30px;background:#ff0000;border-radius:50%;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:30px;color:#ffffff;font-weight:700;">
                                                            &#9654;
                                                        </td>
                                                    </tr></table>
                                                </a>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                </table>

                <p style="margin:14px 0 0 0;font-size:11px;color:#94a3b8;">
                    {{ $appName }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
