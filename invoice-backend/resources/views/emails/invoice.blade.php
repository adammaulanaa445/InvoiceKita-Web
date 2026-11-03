<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="margin:0;padding:32px 16px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#172033;line-height:1.5;">
    @php
        $currency = strtoupper($invoice->currency ?: 'IDR');
        $formatMoney = function ($value) use ($currency) {
            $value = (float) $value;
            return $currency === 'IDR'
                ? 'Rp ' . number_format($value, 0, ',', '.')
                : $currency . ' ' . number_format($value, 2, '.', ',');
        };
    @endphp

    <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:32px;">
        <div style="font-size:15px;line-height:1.7;margin-bottom:24px;color:#334155;">
            <p style="margin:0 0 8px;">Halo {{ $invoice->to_name ?: 'Bapak/Ibu' }},</p>
            <p style="margin:0 0 8px;">Berikut invoice dari <strong>{{ $invoice->from_name ?: 'kami' }}</strong>.</p>
        </div>

        <div style="margin:0 0 28px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#ffffff;">
            <div style="padding:24px 28px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
                    <tr>
                        <td valign="top" style="vertical-align:top;">
                            @if($invoice->logo_url)
                                <img src="{{ $invoice->logo_url }}" alt="Logo {{ $invoice->from_name }}" style="display:block;max-width:160px;max-height:48px;margin:0 0 10px;">
                            @endif
                            <div style="font-size:18px;font-weight:700;color:#0f172a;">{{ $invoice->from_name ?: 'Perusahaan' }}</div>
                            @if($invoice->from_address)<div style="margin-top:4px;font-size:13px;color:#64748b;white-space:pre-line;">{{ $invoice->from_address }}</div>@endif
                            @if($invoice->from_email || $invoice->from_phone)<div style="margin-top:4px;font-size:12px;color:#64748b;">{{ $invoice->from_email }}{{ $invoice->from_email && $invoice->from_phone ? ' • ' : '' }}{{ $invoice->from_phone }}</div>@endif
                        </td>
                        <td valign="top" align="right" style="vertical-align:top;text-align:right;">
                            <div style="display:inline-block;padding:4px 10px;border-radius:999px;background:#8CFF3D;color:#000;font-size:11px;font-weight:700;letter-spacing:.05em;">INVOICE</div>
                            <div style="margin-top:8px;font-size:17px;font-weight:700;color:#0f172a;">#{{ $invoice->invoice_number }}</div>
                            <div style="margin-top:6px;font-size:12px;line-height:1.6;color:#64748b;">Tanggal: {{ optional($invoice->issue_date)->format('d M Y') ?: '-' }}<br>Jatuh tempo: {{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="padding:20px 28px;border-bottom:1px solid #f1f5f9;">
                <div style="margin-bottom:6px;font-size:11px;font-weight:700;letter-spacing:.05em;color:#94a3b8;">DITAGIHKAN KEPADA</div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;">{{ $invoice->to_name ?: '-' }}</div>
                @if($invoice->to_address)<div style="margin-top:4px;font-size:13px;color:#64748b;white-space:pre-line;">{{ $invoice->to_address }}</div>@endif
                @if($invoice->to_email)<div style="margin-top:4px;font-size:12px;color:#64748b;">{{ $invoice->to_email }}</div>@endif
            </div>

            <div style="padding:0 28px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #e2e8f0;">
                            <th align="left" style="padding:10px 8px 10px 0;font-size:11px;color:#64748b;">DESKRIPSI</th>
                            <th align="center" style="padding:10px 8px;font-size:11px;color:#64748b;">QTY</th>
                            <th align="right" style="padding:10px 8px;font-size:11px;color:#64748b;">HARGA</th>
                            <th align="right" style="padding:10px 0 10px 8px;font-size:11px;color:#64748b;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->items as $item)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:12px 8px 12px 0;font-size:13px;font-weight:500;color:#1e293b;">{{ $item->description }}</td>
                                <td align="center" style="padding:12px 8px;font-size:13px;color:#475569;">{{ $item->qty }}</td>
                                <td align="right" style="padding:12px 8px;font-size:13px;color:#475569;white-space:nowrap;">{{ $formatMoney($item->price) }}</td>
                                <td align="right" style="padding:12px 0 12px 8px;font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;">{{ $formatMoney($item->qty * $item->price) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" align="center" style="padding:24px;font-size:13px;color:#94a3b8;">Tidak ada item invoice.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding:20px 28px 24px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;">
                    <tr>
                        <td valign="top" style="vertical-align:top;padding-right:24px;">
                            @if($invoice->notes)
                                <div style="margin-bottom:6px;font-size:11px;font-weight:700;letter-spacing:.05em;color:#94a3b8;">CATATAN</div>
                                <div style="padding:12px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;font-size:12px;color:#475569;white-space:pre-line;">{{ $invoice->notes }}</div>
                            @endif
                            @if($invoice->payment_link)
                                <a href="{{ $invoice->payment_link }}" style="display:inline-block;margin-top:16px;padding:10px 18px;border-radius:999px;background:#8CFF3D;color:#000;font-size:13px;font-weight:700;text-decoration:none;">Bayar Sekarang</a>
                            @endif
                        </td>
                        <td valign="top" align="right" style="vertical-align:top;width:230px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;font-size:13px;">
                                <tr><td style="padding:4px 0;color:#64748b;">Subtotal</td><td align="right" style="padding:4px 0;font-weight:500;white-space:nowrap;">{{ $formatMoney($invoice->subtotal) }}</td></tr>
                                @if($invoice->discount_percent > 0)<tr><td style="padding:4px 0;color:#64748b;">Diskon ({{ (float) $invoice->discount_percent }}%)</td><td align="right" style="padding:4px 0;color:#dc2626;white-space:nowrap;">-{{ $formatMoney($invoice->discount) }}</td></tr>@endif
                                @if($invoice->tax_percent > 0)<tr><td style="padding:4px 0;color:#64748b;">Pajak ({{ (float) $invoice->tax_percent }}%)</td><td align="right" style="padding:4px 0;white-space:nowrap;">+{{ $formatMoney($invoice->tax) }}</td></tr>@endif
                                <tr style="border-top:2px solid #e2e8f0;"><td style="padding:10px 0 0;font-size:15px;font-weight:700;color:#0f172a;">Total</td><td align="right" style="padding:10px 0 0;font-size:17px;font-weight:800;color:#0f172a;white-space:nowrap;">{{ $formatMoney($invoice->total) }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(isset($previewHtml) && $previewHtml)
            <div style="margin:0 0 24px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#ffffff;">
                {!! $previewHtml !!}
            </div>
        @endif

        <div style="padding-top:20px;border-top:1px solid #e5e7eb;font-size:13px;line-height:1.6;color:#64748b;">
            <p style="margin:0 0 8px;"><strong style="color:#172033;">Invoice #{{ $invoice->invoice_number }}</strong></p>
            <p style="margin:0;font-size:13px;color:#64748b;">PDF invoice resmi terlampir untuk disimpan atau dicetak.</p>
        </div>
    </div>
</body>
</html>