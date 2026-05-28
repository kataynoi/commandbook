<?php

namespace App\Libraries;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfWatermarkService
{
    public function addWatermark(string $inputPath, string $watermarkText): string
    {
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $pageCount = $pdf->setSourceFile($inputPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);

            $width = isset($size['width']) ? $size['width'] : $size['w'];
            $height = isset($size['height']) ? $size['height'] : $size['h'];
            $orientation = isset($size['orientation'])
                ? $size['orientation']
                : ($width > $height ? 'L' : 'P');

            $pdf->AddPage($orientation, [$width, $height]);
            $pdf->useTemplate($templateId, 0, 0, $width, $height);

            $this->drawWatermark($pdf, $width, $height, $watermarkText);
        }

        return $pdf->Output('', 'S');
    }

    private function drawWatermark(Fpdi $pdf, float $width, float $height, string $text): void
    {
        $pdf->SetFont('freeserif', 'B', 20);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetAlpha(0.35);

        $label = 'Downloader: ' . $text;
        $cx = $width / 2;

        // วาง 3 ตำแหน่งตามแนวตั้ง: บน กลาง ล่าง
        $yRatios = [0.25, 0.50, 0.75];
        foreach ($yRatios as $ratio) {
            $cy = $height * $ratio;

            $pdf->StartTransform();
            $pdf->Rotate(25, $cx, $cy);
            $pdf->SetXY($cx - 90, $cy - 6);
            $pdf->Cell(180, 12, $label, 0, 0, 'C');
            $pdf->StopTransform();
        }

        $pdf->SetAlpha(1);
    }
}
