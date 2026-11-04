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
            $pdfPath = $this->generatePdf($invoice);
            $pdfData = file_get_contents($pdfPath);

            Mail::to($schedule->recipient_email)->send(
                new InvoiceMail($invoice, $pdfData)
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

    protected function generatePdf($invoice): string
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');

        if ($frontendUrl === '' || !filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('FRONTEND_URL harus berupa URL absolut yang valid.');
        }

        $printUrl = "{$frontendUrl}/print/invoice/{$invoice->id}?token=" . rawurlencode($invoice->print_token);

        $fileName = "scheduled-invoices/invoice-{$invoice->id}-" . now()->timestamp . '.pdf';
        $fullPath = storage_path("app/public/{$fileName}");

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $browsershot = Browsershot::url($printUrl)
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
            ])
            ->waitUntilNetworkIdle()
            // Tunggu sampai Svelte selesai render komponen template.
            // waitUntilNetworkIdle() saja tidak cukup karena Svelte butuh
            // beberapa saat untuk mount komponen setelah fetch data selesai.
            ->waitForFunction('document.querySelector(".print-wrapper") && !document.querySelector(".print-pending")')
            ->setDelay(500) // buffer tambahan 500ms agar CSS/font selesai dimuat
            ->format('A4')
            ->showBackground();

        $chromePath = config('services.browsershot.chrome_path');

        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        $browsershot->save($fullPath);

        return $fullPath;
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