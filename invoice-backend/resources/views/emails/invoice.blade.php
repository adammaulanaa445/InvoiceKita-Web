<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0;padding:20px;background:#f4f6f8;font-family:Arial,sans-serif;color:#334155;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #e2e8f0;">
        <p style="margin:0 0 16px;font-size:16px;">Halo <strong>{{ $invoice->to_name }}</strong>,</p>
        <p style="margin:0 0 24px;font-size:16px;">Berikut adalah invoice tagihan dari <strong>{{ $invoice->from_name }}</strong> (No: #{{ $invoice->invoice_number }}).</p>

        <!-- Bagian ini khusus untuk menampilkan gambar preview -->
        @if(isset($previewHtml) && $previewHtml)
            {!! $previewHtml !!}
        @else
        @if(isset($previewImageBase64) && $previewImageBase64)
            <div style="margin-bottom:24px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                <img src="data:image/png;base64,{{ $previewImageBase64 }}" alt="Preview Invoice" style="display:block;width:100%;height:auto;" />
            </div>
        @endif
        @endif

        <p style="margin:0;font-size:14px;color:#64748b;">PDF resmi invoice telah terlampir pada email ini.</p>
    </div>
</body>
</html>