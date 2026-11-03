<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\ScheduledInvoiceEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendScheduledInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ScheduledInvoiceEmail $scheduledEmail
    ) {}

    public function handle(): void
    {
        $scheduled = $this->scheduledEmail;

        $scheduled->load('invoice.items');

        try {
            if (!$scheduled->pdf_path) {
                throw new \Exception(
                    'File PDF invoice tidak ditemukan.'
                );
            }

            if (!Storage::exists($scheduled->pdf_path)) {
                throw new \Exception(
                    'File PDF invoice tidak tersedia di storage.'
                );
            }

            $pdfData = Storage::get(
                $scheduled->pdf_path
            );

            // Generate preview image as base64
            $previewImageBase64 = '';
            try {
                $previewImageBase64 = app(\App\Services\InvoiceEmailTemplateRenderer::class)->renderImage($scheduled->invoice);
            } catch (\Throwable $e) {
                \Log::warning('Gagal generate preview image untuk scheduled invoice', [
                    'scheduled_email_id' => $scheduled->id,
                    'invoice_id' => $scheduled->invoice_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send email with preview image (base64) or null if generation failed
            Mail::to($scheduled->recipient_email)->send(
                new InvoiceMail(
                    $scheduled->invoice,
                    $pdfData,
                    $previewImageBase64 ?: null
                )
            );

            $scheduled->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

        } catch (\Throwable $e) {

            $scheduled->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            \Log::error(
                'Gagal mengirim scheduled invoice',
                [
                    'scheduled_email_id' => $scheduled->id,
                    'invoice_id' => $scheduled->invoice_id,
                    'email' => $scheduled->recipient_email,
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }
}