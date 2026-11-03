<?php

namespace App\Services;

use App\Models\Invoice;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;

class InvoiceEmailTemplateRenderer
{
    /**
     * Mengambil screenshot invoice sebagai format PNG (Base64)
     * Tanpa data:image prefix, agar bisa langsung digunakan di email.
     */
    public function renderImage(Invoice $invoice): string
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');
        $printUrl = "{$frontendUrl}/print/invoice/{$invoice->id}?token=" . rawurlencode($invoice->print_token);

        Log::info('Rendering invoice image', ['invoice_id' => $invoice->id, 'print_url' => $printUrl]);

        try {
            $browsershot = Browsershot::url($printUrl)
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-setuid-sandbox',
                    'disable-dev-shm-usage',
                    'single-process',
                ])
                ->waitUntilNetworkIdle()
                ->waitForFunction('document.querySelector(".print-wrapper") && !document.querySelector(".print-pending")')
                ->setDelay(1500) // Delay lebih lama untuk memastikan semua asset ter-load
                ->windowSize(900, 1200)
                ->setOption('timeout', 30000); // 30 detik timeout

            $chromePath = config('services.browsershot.chrome_path');
            if ($chromePath) {
                $browsershot->setChromePath($chromePath);
            }

            $base64 = $browsershot->toBase64('png');

            Log::info('Invoice image rendered successfully', ['invoice_id' => $invoice->id, 'base64_length' => strlen($base64)]);

            return $base64;
        } catch (\Throwable $e) {
            Log::error('Gagal render invoice image', [
                'invoice_id' => $invoice->id,
                'print_url' => $printUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}