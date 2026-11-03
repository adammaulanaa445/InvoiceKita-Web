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
        public string $pdfData,
        public ?string $previewData = null, // Bisa berupa HTML preview atau base64 image
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        $previewData = trim((string) $this->previewData);

        // Kalau preview data ada dan cukup panjang
        if ($previewData !== '' && strlen($previewData) > 100) {
            \Log::info('Using preview data with Blade template', [
                'invoice_id' => $this->invoice->id,
                'preview_length' => strlen($previewData),
            ]);

            // Deteksi apakah previewData adalah base64 image atau HTML
            $isImage = $this->isBase64Image($previewData);

            if ($isImage) {
                return new Content(
                    view: 'emails.invoice',
                    with: [
                        'invoice' => $this->invoice->load('items'),
                        'previewImageBase64' => $previewData,
                    ],
                );
            }

            return new Content(
                view: 'emails.invoice',
                with: [
                    'invoice' => $this->invoice->load('items'),
                    'previewHtml' => $previewData,
                ],
            );
        }

        // Fallback: render dari Blade template email bawaan tanpa preview
        \Log::info('Using Blade email template fallback (no preview)', [
            'invoice_id' => $this->invoice->id,
        ]);
        return new Content(
            view: 'emails.invoice',
            with: ['invoice' => $this->invoice->load('items')],
        );
    }

    /**
     * Check if the preview data is a base64 image (starts with data:image/)
     */
    protected function isBase64Image(string $data): bool
    {
        return strpos($data, 'data:image/') === 0;
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

    /**
     * Sanitasi HTML preview dari frontend.
     * Frontend sudah mengirim dokumen HTML lengkap (<!DOCTYPE html>)
     * dengan inline computed style, tanpa class Tailwind, tanpa oklch.
     * Di sini:
     * 1. Anti-XSS: buang script, event handler, javascript: URL.
     *    CATATAN: <style> dari frontend DIPERTAHANKAN karena berisi
     *    CSS anti-link Gmail yang penting.
     * 2. Pastikan CSS anti-link Gmail (color:inherit) sudah ada.
     * 3. Sisipkan &zwnj; di tengah pola email agar tidak jadi link biru.
     */
    public function wrapPreviewHtml(string $html): string
    {
        // Anti-XSS: buang script dan event handler.
        // JANGAN buang <style> — CSS anti-link Gmail ada di sana.
        $clean = (string) preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $clean = (string) preg_replace('/<link\b[^>]*>/i', '', $clean);
        $clean = (string) preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);
        $clean = (string) preg_replace('/(href|src)\s*=\s*(["\'"])\s*javascript:[^"\']*\2/i', '$1="#"', $clean);
        $clean = trim($clean);

        // Lapisan anti auto-link Gmail: sisipkan zero-width-non-joiner (&zwnj;)
        // di tengah pola email agar Gmail tidak membuat link biru otomatis.
        $clean = (string) preg_replace(
            '/([A-Za-z0-9._%+-]+)@([A-Za-z0-9.-]+\.[A-Za-z]{2,})/',
            '$1&zwnj;@$2',
            $clean
        );

        \Log::info('wrapPreviewHtml processing', [
            'has_html_tag' => preg_match('/<html[\s>]/i', $clean) === 1,
            'clean_length' => strlen($clean),
        ]);

        // Ekstrak <body> content jika ada HTML lengkap
        if (preg_match('/<html[\s>]/i', $clean) === 1 && preg_match('/<body[^>]*>(.*?)<\/body>/is', $clean, $matches)) {
            $bodyContent = $matches[1];

            // Pastikan CSS anti-link Gmail sudah ada.
            $antiLinkCss = 'body,div,p,td,th,span{color:inherit!important}'
                . 'a,a:visited,a:hover,a:active{color:inherit!important;text-decoration:none!important}'
                . 'u + #body a,.gmail a,span.MsoHyperlink{color:inherit!important;text-decoration:none!important}';

            if (stripos($clean, 'color:inherit') === false) {
                if (preg_match('/<style[^>]*>/i', $clean)) {
                    // Sisipkan CSS di awal <style> yang sudah ada
                    $clean = (string) preg_replace(
                        '/(<style[^>]*>)/i',
                        '$1' . $antiLinkCss,
                        $clean,
                        1
                    );
                } else {
                    // Tidak ada <style> → sisipkan sebelum </head>
                    $clean = (string) str_ireplace(
                        '</head>',
                        '<style>' . $antiLinkCss . '</style></head>',
                        $clean
                    );
                }
            }

            return $bodyContent;
        }

        // Fallback: HTML belum lengkap → kembalikan asalinannya
        return $clean;
    }
}