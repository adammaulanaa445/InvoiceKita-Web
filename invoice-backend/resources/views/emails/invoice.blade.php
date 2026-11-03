<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
</head>
<body style="margin:0;padding:32px 16px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#172033;line-height:1.5;">
    <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:32px;">
        <div style="font-size:15px;line-height:1.7;margin-bottom:24px;color:#334155;">
            <p style="margin:0 0 8px;">Halo {{ $invoice->to_name ?: 'Bapak/Ibu' }},</p>
            <p style="margin:0 0 8px;">Berikut invoice dari <strong>{{ $invoice->from_name ?: 'kami' }}</strong>.</p>
        </div>

        @if(isset($previewImageBase64) && $previewImageBase64)
            {{-- Tampilkan preview sebagai gambar --}}
            <div style="margin:0 0 24px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#ffffff;">
                <img src="data:image/png;base64,{{ $previewImageBase64 }}" alt="Preview Invoice #{{ $invoice->invoice_number }}" style="display:block;max-width:100%;height:auto;border:none;margin:0;" />
            </div>
        @elseif(isset($previewHtml) && $previewHtml)
            {{-- Tampilkan preview sebagai HTML --}}
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