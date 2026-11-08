<?php

namespace App\Console\Commands;

use App\Mail\InvoiceMail;
use App\Models\ScheduledInvoiceEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Browsershot\Browsershot;

class SendScheduledInvoiceEmails extends Command
{
    protected $signature = 'invoices:send-scheduled';

    protected $description = 'Cek jadwal kirim invoice yang sudah waktunya, generate PDF, kirim email, lalu hitung jadwal berikutnya.';

    public function handle(): int
    {
        $dueSchedules = ScheduledInvoiceEmail::with('invoice')
            ->where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->get();

        $this->info("Ditemukan {$dueSchedules->count()} jadwal yang harus dikirim.");

        foreach ($dueSchedules as $schedule) {
            $this->processSchedule($schedule);
        }

        return self::SUCCESS;
    }

    protected function processSchedule(ScheduledInvoiceEmail $schedule): void
    {
        $invoice = $schedule->invoice;

        if (!$invoice) {
            $schedule->update([
                'status' => 'failed',
                'last_error' => 'Invoice terkait sudah tidak ada (mungkin sudah dihapus).',
            ]);

            return;
        }

        try {
            $imageBase64 = (new InvoiceEmailTemplateRenderer())->renderImage($invoice);

            Mail::to($schedule->recipient_email)->send(
                new InvoiceMail($invoice, null, $imageBase64, 'image/png')
            );

            $schedule->update([
                'last_sent_at' => now(),
                'send_count' => $schedule->send_count + 1,
                'next_send_at' => $this->calculateNextSendAt($schedule),
                'status' => $schedule->frequency === 'once' ? 'completed' : 'active',
                'last_error' => null,
            ]);

            $this->info("Invoice #{$invoice->id} berhasil dikirim ke {$schedule->recipient_email}");
        } catch (\Throwable $e) {
            \Log::error('Gagal mengirim invoice terjadwal', [
                'schedule_id' => $schedule->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            $schedule->update([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            $this->error("Gagal mengirim invoice #{$invoice->id}: {$e->getMessage()}");
        }
    }

    protected function renderInvoiceImage($invoice): string
    {
        return (new InvoiceEmailTemplateRenderer())->renderImage($invoice);
    }

    protected function calculateNextSendAt(ScheduledInvoiceEmail $schedule): Carbon
    {
        $base = Carbon::now();

        return match ($schedule->frequency) {
            'hourly' => $base->addHour(),
            'daily' => $base->addDay(),
            'weekly' => $base->addWeek(),
            'monthly' => $base->addMonthNoOverflow(),
            default => $base,
        };
    }
}