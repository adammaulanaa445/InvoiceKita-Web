<?php

namespace App\Services;

use App\Models\Invoice;
use Spatie\Browsershot\Browsershot;

class InvoiceEmailTemplateRenderer
{
    /**
     * Render preview HTML untuk email.
     * Mengembalikan body content HTML yang sudah diproses (tanpa DOCTYPE/html/body tags).
     */
    public function render(Invoice $invoice): string
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');

        if ($frontendUrl === '' || !filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('FRONTEND_URL harus berupa URL absolut yang valid.');
        }

        $printUrl = "{$frontendUrl}/print/invoice/{$invoice->id}?token=" . rawurlencode($invoice->print_token);
        $browser = Browsershot::url($printUrl)
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
            ])
            ->waitUntilNetworkIdle()
            ->waitForFunction('document.querySelector(".print-wrapper") && !document.querySelector(".print-pending")')
            ->setDelay(500);

        $chromePath = config('services.browsershot.chrome_path');
        if ($chromePath) {
            $browser->setChromePath($chromePath);
        }

        // Execute JavaScript untuk inline SEMUA computed style dari browser.
        // Ini capture hasil render Svelte PERSIS seperti yang terlihat,
        // termasuk semua Tailwind classes yang sudah di-compute jadi style.
        // Email client tidak support external CSS atau class, jadi semuanya
        // harus jadi inline style attribute.
        $html = $browser->evaluate($this->getInlineStylesScript());

        // evaluate() kembalikan array, ambil hasil dari index 0
        if (is_array($html)) {
            $html = $html[0] ?? '';
        }

        // Extract body content if full HTML document
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            return $matches[1];
        }

        return $html;
    }

    /**
     * Generate image preview of invoice as base64 PNG.
     * Returns base64 encoded image data URI (without data:image/png;base64, prefix).
     */
    public function renderImage(Invoice $invoice, int $width = null): string
    {
        $frontendUrl = rtrim((string) config('services.frontend.url'), '/');

        if ($frontendUrl === '' || !filter_var($frontendUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('FRONTEND_URL harus berupa URL absolut yang valid.');
        }

        $printUrl = "{$frontendUrl}/print/invoice/{$invoice->id}?token=" . rawurlencode($invoice->print_token);
        $browser = Browsershot::url($printUrl)
            ->addChromiumArguments([
                'no-sandbox',
                'disable-setuid-sandbox',
            ])
            ->waitUntilNetworkIdle()
            ->waitForFunction('document.querySelector(".print-wrapper") && !document.querySelector(".print-pending")')
            ->setDelay(500);

        $chromePath = config('services.browsershot.chrome_path');
        if ($chromePath) {
            $browser->setChromePath($chromePath);
        }

        // Set output width for email-friendly image
        if ($width) {
            $browser->viewPort($width, 800); // Height auto-calculated
        }

        // Return as base64 PNG
        return $browser->toBase64('png');
    }

    /**
     * JavaScript yang inline semua computed style dari setiap element.
     * Hasil: HTML email-safe dengan inline style persis seperti render Svelte.
     *
     * Filter property yang relevan untuk email (skip animation, transition, dll)
     * untuk kurangi size HTML.
     */
    private function getInlineStylesScript(): string
    {
        return <<<'JAVASCRIPT'
(() => {
  const relevantProps = new Set([
    'color', 'background', 'background-color', 'background-image',
    'background-position', 'background-size', 'background-repeat',
    'border', 'border-top', 'border-right', 'border-bottom', 'border-left',
    'border-width', 'border-style', 'border-color', 'border-radius',
    'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
    'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
    'width', 'height', 'max-width', 'max-height', 'min-width', 'min-height',
    'font-family', 'font-size', 'font-weight', 'font-style', 'line-height',
    'text-align', 'text-decoration', 'text-transform', 'letter-spacing',
    'display', 'vertical-align', 'position', 'top', 'right', 'bottom', 'left',
    'opacity', 'box-shadow', 'text-shadow', 'overflow', 'white-space',
    'word-wrap', 'word-break', 'flex', 'flex-direction', 'justify-content',
    'align-items', 'gap', 'grid', 'grid-template-columns'
  ]);

  function inlineElement(el) {
    const computed = window.getComputedStyle(el);
    const styles = [];

    for (const prop of relevantProps) {
      const value = computed.getPropertyValue(prop);
      if (value && value !== 'none' && value !== 'normal' && value !== 'auto') {
        styles.push(`${prop}:${value}`);
      }
    }

    if (styles.length > 0) {
      el.setAttribute('style', styles.join(';'));
    }
  }

  // Inline semua element di print-wrapper
  const wrapper = document.querySelector('.print-wrapper');
  if (wrapper) {
    const elements = wrapper.querySelectorAll('*');
    elements.forEach(inlineElement);
    inlineElement(wrapper);
  }

  // Remove script, link stylesheet, style tag
  document.querySelectorAll('script').forEach(s => s.remove());
  document.querySelectorAll('link[rel="stylesheet"]').forEach(l => l.remove());
  document.querySelectorAll('style').forEach(s => s.remove());

  // Add minimal style for email client compatibility
  const head = document.querySelector('head');
  if (head) {
    const emailStyle = document.createElement('style');
    emailStyle.textContent = `
      body { margin: 0; padding: 0; }
      img { max-width: 100%; height: auto; }
      table { border-collapse: collapse; }
    `;
    head.appendChild(emailStyle);
  }

  return document.documentElement.outerHTML;
})()
JAVASCRIPT;
    }
}