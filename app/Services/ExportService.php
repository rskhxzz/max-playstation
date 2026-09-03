<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    // ─── Warna tema ──────────────────────────────────────────────────────────
    private const COLOR_HEADER_BG  = '24252A';
    private const COLOR_HEADER_FG  = 'FFFFFF';
    private const COLOR_SUBHEADER  = 'E6007E';
    private const COLOR_ALT_ROW    = 'F4F5F7';
    private const COLOR_BORDER     = 'D1D5DB';

    /**
     * Export data pemesanan ke file .xlsx dan kembalikan sebagai StreamedResponse.
     */
    public function exportBookings(iterable $bookings, array $filters = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pemesanan');

        // ── Judul laporan ────────────────────────────────────────────────────
        $this->writeReportHeader(
            $sheet,
            'LAPORAN PEMESANAN — MAXIBOX PLAYSTATION',
            $this->buildFilterDescription($filters),
            9  // jumlah kolom
        );

        // ── Header kolom (baris 4) ────────────────────────────────────────────
        $headers = [
            'No', 'Kode Booking', 'Nama Customer', 'No HP', 'Paket Sewa',
            'Tanggal & Jam Sewa', 'Total Tagihan (Rp)', 'Status Pesanan', 'Status Pembayaran',
        ];
        $this->writeColumnHeaders($sheet, 4, $headers);

        // ── Data ─────────────────────────────────────────────────────────────
        $row = 5;
        $no  = 1;

        foreach ($bookings as $b) {
            $isAlt = ($no % 2 === 0);
            $bgColor = $isAlt ? self::COLOR_ALT_ROW : 'FFFFFF';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $b->booking_code);
            $sheet->setCellValue("C{$row}", $b->customer?->full_name ?? '—');
            $sheet->setCellValue("D{$row}", $b->customer?->phone_number ?? '—');
            $sheet->setCellValue("E{$row}", $b->rentalPackage?->name ?? '—');
            $sheet->setCellValue("F{$row}", $b->rental_start_at
                ? \Carbon\Carbon::parse($b->rental_start_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i')
                : '—');
            $sheet->setCellValue("G{$row}", (float) $b->total_amount);
            $sheet->setCellValue("H{$row}", $b->booking_status_label);
            $sheet->setCellValue("I{$row}", $b->payment_status_label);

            // Format angka Rupiah
            $sheet->getStyle("G{$row}")->getNumberFormat()
                ->setFormatCode('#,##0');

            // Warna baris alternating
            $sheet->getStyle("A{$row}:I{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bgColor);

            // Border tipis
            $sheet->getStyle("A{$row}:I{$row}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB(self::COLOR_BORDER);

            $sheet->getStyle("A{$row}:I{$row}")
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $row++;
            $no++;
        }

        // ── Baris total ───────────────────────────────────────────────────────
        if ($no > 1) {
            $lastDataRow = $row - 1;
            $sheet->setCellValue("F{$row}", 'TOTAL');
            $sheet->setCellValue("G{$row}", "=SUM(G5:G{$lastDataRow})");
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("F{$row}:I{$row}")
                ->getFont()->setBold(true);
            $sheet->getStyle("F{$row}:I{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FEF3C7');
        }

        // ── Auto width kolom ──────────────────────────────────────────────────
        $widths = ['A'=>6,'B'=>22,'C'=>28,'D'=>18,'E'=>20,'F'=>20,'G'=>22,'H'=>22,'I'=>20];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $filename = 'pemesanan_' . now()->format('Ymd_His') . '.xlsx';

        return $this->streamResponse($spreadsheet, $filename);
    }

    /**
     * Export data pembayaran ke file .xlsx dan kembalikan sebagai StreamedResponse.
     */
    public function exportPayments(iterable $payments, array $filters = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pembayaran');

        // ── Judul laporan ────────────────────────────────────────────────────
        $this->writeReportHeader(
            $sheet,
            'LAPORAN PEMBAYARAN — MAXIBOX PLAYSTATION',
            $this->buildFilterDescription($filters),
            9
        );

        // ── Header kolom (baris 4) ────────────────────────────────────────────
        $headers = [
            'No', 'Kode Pembayaran', 'Kode Booking', 'Customer', 'Tipe Bayar',
            'Nominal (Rp)', 'Dibayar (Rp)', 'Status', 'Tanggal Dibayar',
        ];
        $this->writeColumnHeaders($sheet, 4, $headers);

        // ── Data ─────────────────────────────────────────────────────────────
        $row      = 5;
        $no       = 1;
        $totalReq = 0;
        $totalPaid = 0;

        foreach ($payments as $p) {
            $isAlt   = ($no % 2 === 0);
            $bgColor = $isAlt ? self::COLOR_ALT_ROW : 'FFFFFF';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $p->payment_code);
            $sheet->setCellValue("C{$row}", $p->booking?->booking_code ?? '—');
            $sheet->setCellValue("D{$row}", $p->booking?->customer?->full_name ?? '—');
            $sheet->setCellValue("E{$row}", $p->payment_type === 'initial' ? 'Pembayaran Awal' : 'Pelunasan');
            $sheet->setCellValue("F{$row}", (float) $p->requested_amount);
            $sheet->setCellValue("G{$row}", (float) $p->paid_amount);
            $sheet->setCellValue("H{$row}", $p->status_label);
            $sheet->setCellValue("I{$row}", $p->paid_at
                ? \Carbon\Carbon::parse($p->paid_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i')
                : '—');

            $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle("A{$row}:I{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($bgColor);

            $sheet->getStyle("A{$row}:I{$row}")->getBorders()
                ->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB(self::COLOR_BORDER);

            $sheet->getStyle("A{$row}:I{$row}")
                ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $totalReq  += (float) $p->requested_amount;
            $totalPaid += (float) $p->paid_amount;
            $row++;
            $no++;
        }

        // ── Baris total ───────────────────────────────────────────────────────
        if ($no > 1) {
            $lastDataRow = $row - 1;
            $sheet->setCellValue("E{$row}", 'TOTAL');
            $sheet->setCellValue("F{$row}", "=SUM(F5:F{$lastDataRow})");
            $sheet->setCellValue("G{$row}", "=SUM(G5:G{$lastDataRow})");
            $sheet->getStyle("F{$row}:G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("E{$row}:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("E{$row}:I{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FEF3C7');
        }

        // ── Auto width kolom ──────────────────────────────────────────────────
        $widths = ['A'=>6,'B'=>28,'C'=>22,'D'=>28,'E'=>20,'F'=>20,'G'=>20,'H'=>16,'I'=>20];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $filename = 'pembayaran_' . now()->format('Ymd_His') . '.xlsx';

        return $this->streamResponse($spreadsheet, $filename);
    }

    // ─── Helper: tulis baris judul & info filter ──────────────────────────────

    private function writeReportHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $title, string $filterInfo, int $totalCols): void
    {
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Baris 1: Judul
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Baris 2: Keterangan filter
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $filterInfo);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
        ]);

        // Baris 3: Tanggal export
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Diekspor pada: ' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '9CA3AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
        ]);
    }

    private function writeColumnHeaders(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row, array $headers): void
    {
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $sheet->setCellValue($cellRef, $header);
            $col++;
        }

        $lastCol  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $rangeRef = "A{$row}:{$lastCol}{$row}";

        $sheet->getStyle($rangeRef)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => self::COLOR_HEADER_FG]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_SUBHEADER]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    private function buildFilterDescription(array $filters): string
    {
        $parts = [];

        if (!empty($filters['search']))         $parts[] = 'Kata kunci: "' . $filters['search'] . '"';
        if (!empty($filters['booking_status'])) $parts[] = 'Status Pesanan: ' . $filters['booking_status'];
        if (!empty($filters['payment_status'])) $parts[] = 'Status Bayar: ' . $filters['payment_status'];
        if (!empty($filters['status']))         $parts[] = 'Status: ' . $filters['status'];
        if (!empty($filters['package_id']))     $parts[] = 'Paket: ' . $filters['package_id'];
        if (!empty($filters['date_from']))      $parts[] = 'Dari: ' . $filters['date_from'];
        if (!empty($filters['date_to']))        $parts[] = 'Hingga: ' . $filters['date_to'];

        return empty($parts) ? 'Filter: Semua data' : 'Filter: ' . implode(' | ', $parts);
    }

    private function streamResponse(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
