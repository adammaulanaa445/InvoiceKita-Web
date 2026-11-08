<html>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;color:#334155;">

    <p style="margin:0 0 16px;font-size:16px;font-weight:bold;">Halo {{ $invoice->to_name }},</p>
    <p style="margin:0 0 24px;font-size:16px;">
        Selamat datang di <strong>{{ $invoice->from_name }}</strong>. Berikut adalah detail invoice untuk {{ $invoice->to_name }} (No: #{{ $invoice->invoice_number }}).
    </p>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        Rincian invoice:<br/>
        @foreach($invoice->items as $item)
            {{ $item->description }} - {{ $item->qty }} x Rp{{ number_format((float) $item->price, 2, '.', ',') }} = Rp{{ number_format((float) $item->price * (float) $item->qty, 2, '.', ',') }}<br/>
        @endforeach
        <br/>
        Total: Rp{{ number_format($invoice->total, 2, '.', ',') }}
    </p>

    <div style="margin:0 0 24px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <img src="data:{{ $previewImageMimeType }};base64,{{ $previewImageBase64 }}" style="display:block;width:100%;height:auto;border:0;" />
    </div>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        <strong style="color:#334155;">Informasi Tagihan:</strong><br/>
        Tanggal Terbit: {{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d M Y') : '-' }}<br/>
        Jatuh Tempo: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}<br/>
        Jenis Tagihan: <strong>{{ $invoice->status ?? 'unpaid' }}</strong>
    </p>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        <strong style="color:#334155;">Detail Penerima:</strong><br/>
        Nama: {{ $invoice->to_name }}<br/>
        Alamat: {{ $invoice->to_address ?? '-' }}
    </p>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        <strong style="color:#334155;">Detail Penerbit:</strong><br/>
        Nama: {{ $invoice->from_name }}<br/>
        Alamat: {{ $invoice->from_address ?? '-' }}<br/>
        Telepon: {{ $invoice->from_phone ?? '-' }}<br/>
        Email: {{ $invoice->from_email }}
    </p>

    <div style="margin:0 0 24px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
        <img src="data:{{ $previewImageMimeType }};base64,{{ $previewImageBase64 }}" style="display:block;width:100%;height:auto;border:0;" />
    </div>

    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        <strong style="color:#334155;">Cara pembayaran:</strong><br/>
        Terima pembayaran melalui:<br/>
        Bank: {{ $invoice->payment_link ?? 'Cash on Delivery' }}<br/>
        <span style="color:#94a3b8;font-size:12px;">Silahkan transfer ke rekening di atas dan konfirmasi lewat email.</span>
    </p>

    @if($invoice->notes)
    <p style="margin:0 0 24px;font-size:14px;color:#64748b;">
        <strong style="color:#334155;">Catatan:</strong><br/>
        <span style="border-left:3px solid #9ca3af;padding-left:8px;">{{ $invoice->notes }}</span>
    </p>
    @endif

    <p style="margin:0 0 16px;font-size:12px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:8px;">
        &copy; {{ date('Y') }} {{ $invoice->from_name }}. Semua hak cipta dilindungi.
    </p>
</body>
</html>
