<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναφορά μαζικής αποστολής email</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:680px;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px 8px 28px;">
                            <p style="margin:0;font-size:13px;color:#64748b;letter-spacing:0.02em;">{{ config('app.name') }}</p>
                            <h1 style="margin:6px 0 0 0;font-size:20px;font-weight:600;color:#0f172a;">Αναφορά μαζικής αποστολής email</h1>
                            <p style="margin:6px 0 0 0;font-size:12px;color:#94a3b8;">Batch ID: {{ $batchId }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 4px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;width:50%;" align="center">
                                        <p style="margin:0;font-size:12px;color:#047857;text-transform:uppercase;letter-spacing:0.04em;">Επιτυχείς</p>
                                        <p style="margin:4px 0 0 0;font-size:24px;font-weight:700;color:#065f46;">{{ $sentCount }}</p>
                                    </td>
                                    <td style="width:8px;"></td>
                                    <td style="padding:14px;background:{{ $failedCount ? '#fef2f2' : '#f8fafc' }};border:1px solid {{ $failedCount ? '#fecaca' : '#e2e8f0' }};border-radius:8px;width:50%;" align="center">
                                        <p style="margin:0;font-size:12px;color:{{ $failedCount ? '#b91c1c' : '#64748b' }};text-transform:uppercase;letter-spacing:0.04em;">Αποτυχίες</p>
                                        <p style="margin:4px 0 0 0;font-size:24px;font-weight:700;color:{{ $failedCount ? '#991b1b' : '#94a3b8' }};">{{ $failedCount }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if($failedCount > 0)
                        <tr>
                            <td style="padding:20px 28px 4px 28px;">
                                <p style="margin:0 0 8px 0;font-size:13px;font-weight:600;color:#991b1b;">Δεν στάλθηκαν</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 28px 8px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #fecaca;border-radius:6px;border-collapse:separate;border-spacing:0;">
                                    <tr style="background:#fef2f2;">
                                        <th align="left" style="padding:8px 12px;font-size:12px;color:#7f1d1d;border-bottom:1px solid #fecaca;">Παραλήπτης</th>
                                        <th align="left" style="padding:8px 12px;font-size:12px;color:#7f1d1d;border-bottom:1px solid #fecaca;">Email</th>
                                        <th align="left" style="padding:8px 12px;font-size:12px;color:#7f1d1d;border-bottom:1px solid #fecaca;">Σφάλμα</th>
                                    </tr>
                                    @foreach($failedRows as $row)
                                        <tr>
                                            <td style="padding:8px 12px;font-size:13px;color:#0f172a;border-bottom:1px solid #fee2e2;">{{ $row->name ?: '—' }}</td>
                                            <td style="padding:8px 12px;font-size:13px;color:#334155;border-bottom:1px solid #fee2e2;">{{ $row->email }}</td>
                                            <td style="padding:8px 12px;font-size:12px;color:#991b1b;border-bottom:1px solid #fee2e2;">{{ \Illuminate\Support\Str::limit($row->error, 140) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if($sentCount > 0)
                        <tr>
                            <td style="padding:20px 28px 4px 28px;">
                                <p style="margin:0 0 8px 0;font-size:13px;font-weight:600;color:#065f46;">Στάλθηκαν επιτυχώς</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 28px 8px 28px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #a7f3d0;border-radius:6px;border-collapse:separate;border-spacing:0;">
                                    <tr style="background:#ecfdf5;">
                                        <th align="left" style="padding:8px 12px;font-size:12px;color:#065f46;border-bottom:1px solid #a7f3d0;">Παραλήπτης</th>
                                        <th align="left" style="padding:8px 12px;font-size:12px;color:#065f46;border-bottom:1px solid #a7f3d0;">Email</th>
                                    </tr>
                                    @foreach($sentRows as $row)
                                        <tr>
                                            <td style="padding:8px 12px;font-size:13px;color:#0f172a;border-bottom:1px solid #d1fae5;">{{ $row->name ?: '—' }}</td>
                                            <td style="padding:8px 12px;font-size:13px;color:#334155;border-bottom:1px solid #d1fae5;">{{ $row->email }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:18px 28px 26px 28px;font-size:12px;color:#94a3b8;line-height:1.55;border-top:1px solid #e2e8f0;">
                            <p style="margin:14px 0 0 0;">Αυτόματη αναφορά — δεν χρειάζεται απάντηση.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
