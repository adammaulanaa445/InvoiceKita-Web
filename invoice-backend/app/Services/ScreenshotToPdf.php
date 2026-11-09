<?php

namespace App\Services;

class ScreenshotToPdf
{
    /**
     * Convert a JPEG image binary to a minimal PDF binary.
     * The image is scaled to fit an A4 page (595.28 x 841.89 pt).
     */
    public static function convert(string $jpegData): string
    {
        // Extract JPEG dimensions from SOF marker
        $dims = self::getJpegDimensions($jpegData);
        $imgW = $dims['width'];
        $imgH = $dims['height'];

        // A4 page dimensions in points
        $pageW = 595.28;
        $pageH = 841.89;

        // Scale image to fit page width
        $scale = $pageW / $imgW;
        $drawW = $pageW;
        $drawH = $imgH * $scale;

        // If image is taller than page, scale to fit height instead
        if ($drawH > $pageH) {
            $scale = $pageH / $imgH;
            $drawH = $pageH;
            $drawW = $imgW * $scale;
        }

        // Center horizontally
        $x = ($pageW - $drawW) / 2;
        $y = 0;

        $imgLen = strlen($jpegData);

        // Build PDF objects
        $objects = [];

        // Object 1: Catalog
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // Object 2: Pages
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        // Object 3: Page
        $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$pageW} {$pageH}] /Resources << /XObject << /Im0 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";

        // Object 4: Image XObject (JPEG)
        $objects[4] = "4 0 obj\n<< /Type /XObject /Subtype /Image /Width {$imgW} /Height {$imgH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length {$imgLen} >>\nstream\n" . $jpegData . "\nendstream\nendobj\n";

        // Object 5: Content stream
        $contentStream = "q\n{$drawW} 0 0 {$drawH} {$x} {$y} cm\n/Im0 Do\nQ\n";
        $contentLen = strlen($contentStream);
        $objects[5] = "5 0 obj\n<< /Length {$contentLen} >>\nstream\n" . $contentStream . "endstream\nendobj\n";

        // Assemble PDF
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= 5; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i];
        }

        // xref table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        // trailer
        $pdf .= "trailer\n";
        $pdf .= "<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefOffset . "\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * Extract width and height from JPEG binary data.
     */
    private static function getJpegDimensions(string $data): array
    {
        $pos = 0;
        $len = strlen($data);

        // Skip SOI marker
        if (substr($data, 0, 2) !== "\xFF\xD8") {
            throw new \RuntimeException('Invalid JPEG data');
        }
        $pos = 2;

        while ($pos < $len) {
            // Find next marker
            if (ord($data[$pos]) !== 0xFF) {
                $pos++;
                continue;
            }

            // Skip padding
            while ($pos < $len && ord($data[$pos]) === 0xFF) {
                $pos++;
            }

            if ($pos >= $len) {
                break;
            }

            $marker = ord($data[$pos]);
            $pos++;

            // SOF markers: 0xC0-0xCF except 0xC4 (DHT), 0xC8 (JPG), 0xCC (DAC)
            if (($marker >= 0xC0 && $marker <= 0xCF) && $marker !== 0xC4 && $marker !== 0xC8 && $marker !== 0xCC) {
                // SOF marker found
                $pos += 3; // skip length (2 bytes) + precision (1 byte)
                $height = (ord($data[$pos]) << 8) | ord($data[$pos + 1]);
                $pos += 2;
                $width = (ord($data[$pos]) << 8) | ord($data[$pos + 1]);
                return ['width' => $width, 'height' => $height];
            }

            // Skip segment
            if ($marker === 0xD8 || $marker === 0xD9 || $marker === 0x01) {
                // SOI, EOI, TEM - no length
                continue;
            }

            if ($pos + 2 > $len) {
                break;
            }

            $segLen = (ord($data[$pos]) << 8) | ord($data[$pos + 1]);
            $pos += $segLen;
        }

        throw new \RuntimeException('Could not find JPEG dimensions');
    }
}