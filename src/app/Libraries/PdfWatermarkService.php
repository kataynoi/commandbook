<?php

namespace App\Libraries;

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfWatermarkService
{
    public function addWatermark(string $inputPath, string $watermarkText): string
    {
        try {
            return $this->renderWatermarkedPdf($inputPath, $watermarkText);
        } catch (\Throwable $e) {
            // PDF compression รุ่นใหม่ (object streams) FPDI ฟรีอ่านไม่ได้
            // ลอง preprocess ด้วย qpdf แล้ว retry
            if ($this->isCompressionError($e->getMessage())) {
                $processedPath = $this->preprocessWithQpdf($inputPath);
                if ($processedPath !== null) {
                    try {
                        return $this->renderWatermarkedPdf($processedPath, $watermarkText);
                    } finally {
                        @unlink($processedPath);
                    }
                }
            }
            throw $e;
        }
    }

    private function renderWatermarkedPdf(string $inputPath, string $watermarkText): string
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
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetAlpha(0.35);

        $line1 = 'Downloader: ' . $text;
        $line2 = 'Downloaded: ' . date('Y-m-d H:i:s');
        $cx = $width / 2;

        $yRatios = [0.25, 0.50, 0.75];
        foreach ($yRatios as $ratio) {
            $cy = $height * $ratio;

            $pdf->StartTransform();
            $pdf->Rotate(25, $cx, $cy);

            // บรรทัดที่ 1: ชื่อผู้ดาวน์โหลด (font size 20) — เหนือจุดศูนย์กลาง
            $pdf->SetFont('freeserif', 'B', 20);
            $pdf->SetXY($cx - 90, $cy - 12);
            $pdf->Cell(180, 12, $line1, 0, 0, 'C');

            // บรรทัดที่ 2: วันเวลาที่ดาวน์โหลด (font size 16) — ใต้บรรทัดแรก
            $pdf->SetFont('freeserif', '', 16);
            $pdf->SetXY($cx - 90, $cy + 1);
            $pdf->Cell(180, 10, $line2, 0, 0, 'C');

            $pdf->StopTransform();
        }

        $pdf->SetAlpha(1);
    }

    private function isCompressionError(string $message): bool
    {
        return stripos($message, 'compression technique') !== false
            || stripos($message, 'cross-reference') !== false
            || stripos($message, 'not supported') !== false;
    }

    private function preprocessWithQpdf(string $inputPath): ?string
    {
        static $qpdfPath = null;
        if ($qpdfPath === null) {
            $which = trim((string) @shell_exec('command -v qpdf 2>/dev/null'));
            $qpdfPath = $which !== '' ? $which : false;
        }
        if ($qpdfPath === false) {
            log_message('error', 'qpdf not available in container; cannot preprocess PDF');
            return null;
        }

        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR
                  . uniqid('pdfwm_', true) . '.pdf';

        $cmd = sprintf(
            '%s --object-streams=disable --stream-data=uncompress %s %s 2>&1',
            escapeshellcmd($qpdfPath),
            escapeshellarg($inputPath),
            escapeshellarg($tempPath)
        );

        exec($cmd, $output, $returnCode);

        // qpdf คืน 0=ok, 3=warning-but-ok ใช้ได้ทั้งคู่
        if (($returnCode !== 0 && $returnCode !== 3) || !file_exists($tempPath)) {
            log_message('error', 'qpdf preprocessing failed (code=' . $returnCode . '): ' . implode("\n", $output));
            @unlink($tempPath);
            return null;
        }

        return $tempPath;
    }
}
