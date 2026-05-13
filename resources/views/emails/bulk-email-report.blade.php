@php
    $logoPath = \App\Support\Branding::logoPath();
    $logoCid  = ($logoPath && isset($message)) ? $message->embed($logoPath) : null;
    $appName  = config('app.name');
    $totalCount = $sentCount + $failedCount;
@endphp
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>Αναφορά αποστολών</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:680px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,0.04);">

                    {{-- ───── Logo bar ───── --}}
                    <tr>
                        <td align="center" style="padding:26px 28px 18px 28px;background:#ffffff;border-bottom:1px solid #f1f5f9;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" height="44"
                                     style="display:block;height:44px;width:auto;border:0;outline:none;text-decoration:none;">
                            @else
                                <p style="margin:0;font-size:16px;font-weight:700;color:#0f172a;letter-spacing:0.01em;">{{ $appName }}</p>
                            @endif
                        </td>
                    </tr>

                    {{-- ───── Hero (emerald gradient) ───── --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#059669 0%,#047857 55%,#134e4a 100%);padding:26px 28px;">
                            <p style="margin:0 0 4px 0;font-size:11px;color:#a7f3d0;letter-spacing:0.18em;font-weight:700;text-transform:uppercase;">
                                Αναφορά
                            </p>
                            <h1 style="margin:0;font-size:22px;line-height:1.25;font-weight:700;color:#ffffff;letter-spacing:-0.01em;">
                                Μαζική αποστολή πιστοποιητικών
                            </h1>
                            <p style="margin:6px 0 0 0;font-size:12px;color:#d1fae5;">
                                Batch ID: <span style="font-family:'SFMono-Regular',Consolas,Menlo,monospace;">{{ $batchId }}</span>
                            </p>
                        </td>
                    </tr>

                    {{-- ───── Stats ───── --}}
                    <tr>
                        <td style="padding:22px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:16px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;width:50%;" align="center">
                                        <p style="margin:0;font-size:11px;color:#047857;letter-spacing:0.04em;font-weight:600;">Επιτυχείς</p>
                                        <p style="margin:6px 0 0 0;font-size:28px;font-weight:700;color:#065f46;line-height:1;">{{ $sentCount }}</p>
                                    </td>
                                    <td style="width:10px;"></td>
                                    <td style="padding:16px;background:{{ $failedCount ? '#fef2f2' : '#f8fafc' }};border:1px solid {{ $failedCount ? '#fecaca' : '#e2e8f0' }};border-radius:10px;width:50%;" align="center">
                                        <p style="margin:0;font-size:11px;color:{{ $failedCount ? '#b91c1c' : '#64748b' }};letter-spacing:0.04em;font-weight:600;">Αποτυχίες</p>
                                        <p style="margin:6px 0 0 0;font-size:28px;font-weight:700;color:{{ $failedCount ? '#991b1b' : '#94a3b8' }};line-height:1;">{{ $failedCount }}</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:12px 0 0 0;font-size:12px;color:#64748b;text-align:center;">
                                Σύνολο: <strong style="color:#0f172a;">{{ $totalCount }}</strong> {{ $totalCount === 1 ? 'παραλήπτης' : 'παραλήπτες' }}
                            </p>
                        </td>
                    </tr>

                    @if($failedCount > 0)
                        <tr>
                            <td style="padding:22px 28px 4px 28px;">
                                <p style="margin:0 0 10px 0;font-size:13px;font-weight:700;color:#991b1b;">Δεν στάλθηκαν</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 28px 8px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #fecaca;border-radius:8px;border-collapse:separate;border-spacing:0;overflow:hidden;">
                                    <tr style="background:#fef2f2;">
                                        <th align="left" style="padding:10px 12px;font-size:11px;color:#7f1d1d;font-weight:600;letter-spacing:0.03em;border-bottom:1px solid #fecaca;">Παραλήπτης</th>
                                        <th align="left" style="padding:10px 12px;font-size:11px;color:#7f1d1d;font-weight:600;letter-spacing:0.03em;border-bottom:1px solid #fecaca;">Email</th>
                                        <th align="left" style="padding:10px 12px;font-size:11px;color:#7f1d1d;font-weight:600;letter-spacing:0.03em;border-bottom:1px solid #fecaca;">Σφάλμα</th>
                                    </tr>
                                    @foreach($failedRows as $row)
                                        <tr>
                                            <td style="padding:9px 12px;font-size:13px;color:#0f172a;border-bottom:1px solid #fee2e2;">{{ $row->name ?: '—' }}</td>
                                            <td style="padding:9px 12px;font-size:13px;color:#334155;border-bottom:1px solid #fee2e2;">{{ $row->email }}</td>
                                            <td style="padding:9px 12px;font-size:12px;color:#991b1b;border-bottom:1px solid #fee2e2;">{{ \Illuminate\Support\Str::limit($row->error, 140) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if($sentCount > 0)
                        <tr>
                            <td style="padding:22px 28px 4px 28px;">
                                <p style="margin:0 0 10px 0;font-size:13px;font-weight:700;color:#065f46;">Στάλθηκαν επιτυχώς</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 28px 8px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #a7f3d0;border-radius:8px;border-collapse:separate;border-spacing:0;overflow:hidden;">
                                    <tr style="background:#ecfdf5;">
                                        <th align="left" style="padding:10px 12px;font-size:11px;color:#065f46;font-weight:600;letter-spacing:0.03em;border-bottom:1px solid #a7f3d0;">Παραλήπτης</th>
                                        <th align="left" style="padding:10px 12px;font-size:11px;color:#065f46;font-weight:600;letter-spacing:0.03em;border-bottom:1px solid #a7f3d0;">Email</th>
                                    </tr>
                                    @foreach($sentRows as $row)
                                        <tr>
                                            <td style="padding:9px 12px;font-size:13px;color:#0f172a;border-bottom:1px solid #d1fae5;">{{ $row->name ?: '—' }}</td>
                                            <td style="padding:9px 12px;font-size:13px;color:#334155;border-bottom:1px solid #d1fae5;">{{ $row->email }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:22px 28px 26px 28px;font-size:12px;color:#94a3b8;line-height:1.6;border-top:1px solid #e2e8f0;">
                            <p style="margin:14px 0 0 0;">Αυτόματη αναφορά — δεν χρειάζεται απάντηση.</p>
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
