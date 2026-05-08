<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $client->full_name ?: 'Πιστοποιητικό' }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px 8px 28px;">
                            <p style="margin:0;font-size:13px;color:#64748b;letter-spacing:0.02em;">{{ config('app.name') }}</p>
                            <h1 style="margin:6px 0 0 0;font-size:20px;font-weight:600;color:#0f172a;">Το πιστοποιητικό σας είναι έτοιμο</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 28px 8px 28px;font-size:15px;line-height:1.55;color:#334155;">
                            @if($client->full_name)
                                <p style="margin:0 0 12px 0;">Αγαπητέ/ή <strong>{{ $client->full_name }}</strong>,</p>
                            @else
                                <p style="margin:0 0 12px 0;">Γεια σας,</p>
                            @endif
                            <p style="margin:0 0 16px 0;">
                                Μπορείτε να δείτε ή να κατεβάσετε το πιστοποιητικό σας χρησιμοποιώντας τους παρακάτω συνδέσμους.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 6px 0;font-size:12px;color:#64748b;text-transform:none;">Σελίδα προβολής</p>
                                        <a href="{{ $publicUrl }}" style="display:inline-block;color:#2563eb;text-decoration:none;font-size:14px;word-break:break-all;">{{ $publicUrl }}</a>
                                        <div style="margin-top:12px;">
                                            <a href="{{ $publicUrl }}"
                                               style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-size:14px;font-weight:500;padding:10px 18px;border-radius:6px;">
                                                Άνοιγμα σελίδας πιστοποιητικού
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if(count($downloads))
                        <tr>
                            <td style="padding:20px 28px 4px 28px;">
                                <p style="margin:0 0 10px 0;font-size:13px;font-weight:600;color:#0f172a;">Λήψη PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 28px 8px 28px;">
                                @foreach($downloads as $d)
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:8px;">
                                        <tr>
                                            <td style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:6px;">
                                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="font-size:14px;color:#0f172a;">{{ $d['name'] }}</td>
                                                        <td align="right">
                                                            <a href="{{ $d['url'] }}"
                                                               style="display:inline-block;color:#2563eb;text-decoration:none;font-size:13px;font-weight:500;">
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

                    <tr>
                        <td style="padding:18px 28px 26px 28px;font-size:13px;color:#64748b;line-height:1.55;border-top:1px solid #e2e8f0;margin-top:16px;">
                            <p style="margin:18px 0 0 0;">Αν αντιμετωπίσετε πρόβλημα με τους συνδέσμους, αντιγράψτε και επικολλήστε τους στον browser σας.</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:14px 0 0 0;font-size:11px;color:#94a3b8;">
                    {{ config('app.name') }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
