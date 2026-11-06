<html>
<body style="margin:0;padding:20px;font-family:Arial,sans-serif;color:#334155;">
    <p style="margin:0 0 16px;font-size:16px;">Halo {{ $invoice->to_name }},</p>
    <p style="margin:0 0 24px;font-size:16px;">Berikut adalah invoice tagihan dari {{ $invoice->from_name }} (No: #{{ $invoice->invoice_number }}).</p>
    <p style="margin:0;font-size:14px;color:#64748b;">Gambar invoice terlampir di bawah ini.</p>
</body>
</html>
