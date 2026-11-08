<html>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;color:#334155;">
    <p style="margin:0 0 16px;font-size:16px;font-weight:bold;">Halo {{ $invoice->to_name }},</p>
    <p style="margin:0 0 24px;font-size:16px;">Selamat datang di <strong>{{ $invoice->from_name }}</strong>. Berikut adalah detail invoice untuk {{ $invoice->to_name }} (No: #{{ $invoice->invoice_number }}).</p>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        Rincian invoice:<br/>
        @foreach($invoice->items as $item)
            {{ $item->description }} - {{ $item->qty }} x Rp{{ number_format((float) $item->price, 2, '.', ',') }} = Rp{{ number_format((float) $item->price * (float) $item->qty, 2, '.', ',') }}<br/>
        @endforeach
        Total: Rp{{ number_format($invoice->total, 2, '.', ',') }}
    </p>

    <div style="margin:0 0 24px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <img src="data:{{ $previewImageMimeType }};base64,{{ $previewImageBase64 }}" alt="Preview Invoice" style="display:block;width:100%;height:auto;border:0;" />
    </div>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        Tetap bayar sampai: {{ $invoice->due_date }}<br/>
        Oleh: {{ $invoice->from_name }} &mdash; {{ $invoice->from_email }}
    </p>
</body>
</html>
