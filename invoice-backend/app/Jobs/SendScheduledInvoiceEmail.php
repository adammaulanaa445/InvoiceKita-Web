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
            $imageBase64 = (new InvoiceEmailTemplateRenderer())->renderImage($scheduled->invoice);

            // Send email with preview HTML and PDF attachment
            // Image is generated as PNG by InvoiceEmailTemplateRenderer
            Mail::to($scheduled->recipient_email)->send(
                new InvoiceMail(
                    $scheduled->invoice,
                    null,
                    $imageBase64,
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