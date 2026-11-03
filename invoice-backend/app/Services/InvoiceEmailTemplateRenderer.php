<?php

namespace App\Services;

use App\Models\Invoice;
use Spatie\Browsershot\Browsershot;

class InvoiceEmailTemplateRenderer
{
    /**
     * Mengambil screenshot invoice sebagai format PNG (Base64)
     */
    public function renderImage(Invoice $invoice): string
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');
        $printUrl = "{$frontendUrl}/print/invoice/{$invoice->id}?token=" . rawurlencode($invoice->print_token);

        $browsershot = Browsershot::url($printUrl)
            ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox'])
            ->waitUntilNetworkIdle()
            ->waitForFunction('document.querySelector(".print-wrapper") && !document.querySelector(".print-pending")')
            ->setDelay(800)
            ->windowSize(800, 1200);

        $chromePath = config('services.browsershot.chrome_path');
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }

        // Ambil screenshot sebagai base64 string
        return $browsershot->toBase64('png');
    }
}