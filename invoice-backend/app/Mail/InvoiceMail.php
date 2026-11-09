<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $pdfData = null,
        public ?string $imageBase64 = null,
        public ?string $imageMimeType = 'image/png',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-mail',
            with: [
                'invoice' => $this->invoice,
            ],
            text: $this->buildTextBody(),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->pdfData,
                'Invoice-' . $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }

    private function buildTextBody(): string
    {
        $invoice = $this->invoice;
        $invoice->load('items');

        $lines = [];
        $lines[] = 'Halo ' . $invoice->to_name . ',';
        $lines[] = '';
        $lines[] = 'Berikut adalah invoice tagihan dari ' . $invoice->from_name . ' (No: #' . $invoice->invoice_number . ').';
        $lines[] = '';
        $lines[] = '--- Rincian Invoice ---';
        $lines[] = 'No.      Deskripsi                                   Qty    Harga       Total';
        $lines[] = str_repeat('-', 72);
        $subtotal = 0.0;
        foreach ($invoice->items as $item) {
            $lineTotal = $item->price * $item->qty;
            $subtotal += $lineTotal;
            $lines[] = sprintf('%-9s %-45s %4d %10.2f %10.2f', '', $item->description, $item->qty, $item->price, $lineTotal);
        }
        $lines[] = str_repeat('-', 72);
        $lines[] = 'Subtotal: ' . number_format($subtotal, 2);
        $lines[] = 'Diskon (' . $invoice->discount_percent . '%): ' . number_format($subtotal * $invoice->discount_percent / 100, 2);
        $lines[] = 'Pajak (' . $invoice->tax_percent . '%): ' . number_format($subtotal * $invoice->tax_percent / 100, 2);
        $total = $subtotal - ($subtotal * $invoice->discount_percent / 100) + ($subtotal * $invoice->tax_percent / 100);
        $lines[] = 'Total: ' . number_format($total, 2) . ' ' . $invoice->currency;
        $lines[] = '';
        $lines[] = 'Tanggal terbit: ' . $invoice->issue_date;
        $lines[] = 'Tetap bayar sampai: ' . $invoice->due_date;
        $lines[] = '';
        $lines[] = 'Dikeluarkan oleh: ' . $invoice->from_name;
        $lines[] = 'Alamat: ' . $invoice->from_address;
        $lines[] = 'Email: ' . $invoice->from_email;
        $lines[] = 'Telepon: ' . $invoice->from_phone;
        $lines[] = '';
        $lines[] = 'Untuk: ' . $invoice->to_name;
        $lines[] = 'Alamat: ' . $invoice->to_address;
        $lines[] = 'Email: ' . $invoice->to_email;
        $lines[] = '';
        $notes = $invoice->notes;
        if ($notes !== null && $notes !== '') {
            $lines[] = 'Catatan: ' . $notes;
        }
        $lines[] = '';
        $lines[] = 'Gambar invoice terlampir di bawah ini.';
        return implode(PHP_EOL, $lines);
    }
}