<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\ScheduledInvoiceEmail;
use App\Services\ScreenshotToPdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
            // Generate screenshot (JPEG) from print URL - matches frontend html2canvas approach
            $frontendUrl = rtrim((string) config('services.frontend.url'), '/');
            $printUrl = "{$frontendUrl}/print/invoice/{$scheduled->invoice->id}?token=" . rawurlencode($scheduled->invoice->print_token);

            $jpegData = \Spatie\Browsershot\Browsershot::url($printUrl)
                ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox', 'disable-dev-shm-usage', 'single-process'])
                ->emulateMedia('screen')
                ->showBackground()
                ->windowSize(800, 1200)
                ->waitUntilNetworkIdle()
                ->setDelay(1500)
                ->screenshot('jpeg', 95);

            // Convert screenshot to PDF (embeds image in A4 PDF, same as frontend jsPDF)
            $pdfData = ScreenshotToPdf::convert($jpegData);

            // Send email with HTML preview and PDF attachment
            // Image is not included - user wants text and PDF only
            Mail::to($scheduled->recipient_email)->send(
                new InvoiceMail(
                    $scheduled->invoice,
                    $pdfData,
                    null,
                    'image/png'
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