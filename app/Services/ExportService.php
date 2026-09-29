<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    private const COLOR_HEADER_BG = '24252A';
    private const COLOR_HEADER_FG = 'FFFFFF';
    private const COLOR_SUBHEADER = 'E6007E';
    private const COLOR_ALT_ROW = 'F4F5F7';
    private const COLOR_BORDER = 'D1D5DB';

    public function exportBookings(
        iterable $bookings,
        array $filters = []
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pemesanan');

        $headers = [
            'No',
            'Kode Booking',
            'Nama Customer',
            'No HP',
            'Paket Sewa',
            'Tanggal & Jam Sewa',
            'Jarak (km)',
            'Ongkir Customer (Rp)',
            'Driver',
            'Kendaraan',
            'Pendapatan Kotor (Rp)',
            'Potongan Bensin (Rp)',
            'Pendapatan Driver (Rp)',
            'Dokumentasi',
            'Tanggal Pengantaran',
            'Tanggal Pickup',
            'Total Tagihan (Rp)',
            'Status Pesanan',
            'Status Pembayaran',
        ];

        $this->writeReportHeader(
            $sheet,
            'LAPORAN PEMESANAN — MAXIBOX PLAYSTATION',
            $this->buildFilterDescription($filters),
            count($headers)
        );

        $this->writeColumnHeaders(
            $sheet,
            4,
            $headers
        );

        $row = 5;
        $no = 1;

        foreach ($bookings as $booking) {
            $background = $no % 2 === 0
                ? self::COLOR_ALT_ROW
                : 'FFFFFF';

            $sheet->setCellValue(
                "A{$row}",
                $no
            );

            $sheet->setCellValue(
                "B{$row}",
                $booking->booking_code
            );

            $sheet->setCellValue(
                "C{$row}",
                $booking->customer?->full_name ?? '—'
            );

            $sheet->setCellValue(
                "D{$row}",
                $booking->customer?->phone_number ?? '—'
            );

            $sheet->setCellValue(
                "E{$row}",
                $booking->rentalPackage?->name ?? '—'
            );

            $sheet->setCellValue(
                "F{$row}",
                $this->formatDateTime(
                    $booking->rental_start_at
                )
            );

            $sheet->setCellValue(
                "G{$row}",
                (float) $booking->distance_km
            );

            $sheet->setCellValue(
                "H{$row}",
                (float) $booking->delivery_fee
            );

            $sheet->setCellValue(
                "I{$row}",
                $booking->driver?->name ?? 'Belum ditugaskan'
            );

            $sheet->setCellValue(
                "J{$row}",
                $this->vehicleLabel(
                    $booking->vehicle_type
                )
            );

            $sheet->setCellValue(
                "K{$row}",
                (float) $booking->driver_fee
            );

            $sheet->setCellValue(
                "L{$row}",
                (float) $booking->fuel_deduction
            );

            $sheet->setCellValue(
                "M{$row}",
                (float) $booking->driver_income
            );

            $sheet->setCellValue(
                "N{$row}",
                $booking->delivery_photo_path
                    ? 'Ada'
                    : 'Belum ada'
            );

            $sheet->setCellValue(
                "O{$row}",
                $this->formatDateTime(
                    $booking->delivered_at
                )
            );

            $sheet->setCellValue(
                "P{$row}",
                $this->formatDateTime(
                    $booking->picked_up_at
                )
            );

            $sheet->setCellValue(
                "Q{$row}",
                (float) $booking->total_amount
            );

            $sheet->setCellValue(
                "R{$row}",
                $booking->booking_status_label
            );

            $sheet->setCellValue(
                "S{$row}",
                $booking->payment_status_label
            );

            $sheet->getStyle("G{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            foreach (
                [
                    "H{$row}",
                    "K{$row}",
                    "L{$row}",
                    "M{$row}",
                    "Q{$row}",
                ] as $cell
            ) {
                $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }

            $sheet->getStyle("A{$row}:S{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($background);

            $sheet->getStyle("A{$row}:S{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setRGB(self::COLOR_BORDER);

            $sheet->getStyle("A{$row}:S{$row}")
                ->getAlignment()
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );

            $row++;
            $no++;
        }

        if ($no > 1) {
            $lastDataRow = $row - 1;

            $sheet->setCellValue(
                "F{$row}",
                'TOTAL'
            );

            $sheet->setCellValue(
                "H{$row}",
                "=SUM(H5:H{$lastDataRow})"
            );

            $sheet->setCellValue(
                "K{$row}",
                "=SUM(K5:K{$lastDataRow})"
            );

            $sheet->setCellValue(
                "L{$row}",
                "=SUM(L5:L{$lastDataRow})"
            );

            $sheet->setCellValue(
                "M{$row}",
                "=SUM(M5:M{$lastDataRow})"
            );

            $sheet->setCellValue(
                "Q{$row}",
                "=SUM(Q5:Q{$lastDataRow})"
            );

            foreach (
                [
                    "H{$row}",
                    "K{$row}",
                    "L{$row}",
                    "M{$row}",
                    "Q{$row}",
                ] as $cell
            ) {
                $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }

            $sheet->getStyle("F{$row}:S{$row}")
                ->getFont()
                ->setBold(true);

            $sheet->getStyle("F{$row}:S{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('FEF3C7');

            $sheet->getStyle("F{$row}:S{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setRGB(self::COLOR_BORDER);
        }

        $widths = [
            'A' => 6,
            'B' => 22,
            'C' => 28,
            'D' => 18,
            'E' => 22,
            'F' => 20,
            'G' => 13,
            'H' => 20,
            'I' => 24,
            'J' => 18,
            'K' => 26,
            'L' => 22,
            'M' => 22,
            'N' => 15,
            'O' => 20,
            'P' => 20,
            'Q' => 22,
            'R' => 22,
            'S' => 20,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension(
                $column
            )->setWidth($width);
        }

        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:S4');

        $filename =
            'pemesanan_'
            . now()->format('Ymd_His')
            . '.xlsx';

        return $this->streamResponse(
            $spreadsheet,
            $filename
        );
    }

    public function exportPayments(
        iterable $payments,
        array $filters = []
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pembayaran');

        $headers = [
            'No',
            'Kode Pembayaran',
            'Kode Booking',
            'Customer',
            'Tipe Bayar',
            'Nominal (Rp)',
            'Dibayar (Rp)',
            'Status',
            'Tanggal Dibayar',
        ];

        $this->writeReportHeader(
            $sheet,
            'LAPORAN PEMBAYARAN — MAXIBOX PLAYSTATION',
            $this->buildFilterDescription($filters),
            count($headers)
        );

        $this->writeColumnHeaders(
            $sheet,
            4,
            $headers
        );

        $row = 5;
        $no = 1;

        foreach ($payments as $payment) {
            $background = $no % 2 === 0
                ? self::COLOR_ALT_ROW
                : 'FFFFFF';

            $sheet->setCellValue(
                "A{$row}",
                $no
            );

            $sheet->setCellValue(
                "B{$row}",
                $payment->payment_code
            );

            $sheet->setCellValue(
                "C{$row}",
                $payment->booking?->booking_code ?? '—'
            );

            $sheet->setCellValue(
                "D{$row}",
                $payment->booking?->customer?->full_name ?? '—'
            );

            $sheet->setCellValue(
                "E{$row}",
                $payment->payment_type === 'initial'
                    ? 'Pembayaran Awal'
                    : 'Pelunasan'
            );

            $sheet->setCellValue(
                "F{$row}",
                (float) $payment->requested_amount
            );

            $sheet->setCellValue(
                "G{$row}",
                (float) $payment->paid_amount
            );

            $sheet->setCellValue(
                "H{$row}",
                $payment->status_label
            );

            $sheet->setCellValue(
                "I{$row}",
                $this->formatDateTime(
                    $payment->paid_at
                )
            );

            $sheet->getStyle(
                "F{$row}:G{$row}"
            )
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            $sheet->getStyle("A{$row}:I{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB($background);

            $sheet->getStyle("A{$row}:I{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setRGB(self::COLOR_BORDER);

            $sheet->getStyle("A{$row}:I{$row}")
                ->getAlignment()
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );

            $row++;
            $no++;
        }

        if ($no > 1) {
            $lastDataRow = $row - 1;

            $sheet->setCellValue(
                "E{$row}",
                'TOTAL'
            );

            $sheet->setCellValue(
                "F{$row}",
                "=SUM(F5:F{$lastDataRow})"
            );

            $sheet->setCellValue(
                "G{$row}",
                "=SUM(G5:G{$lastDataRow})"
            );

            $sheet->getStyle(
                "F{$row}:G{$row}"
            )
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            $sheet->getStyle("E{$row}:I{$row}")
                ->getFont()
                ->setBold(true);

            $sheet->getStyle("E{$row}:I{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('FEF3C7');
        }

        $widths = [
            'A' => 6,
            'B' => 28,
            'C' => 22,
            'D' => 28,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 18,
            'I' => 20,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension(
                $column
            )->setWidth($width);
        }

        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:I4');

        $filename =
            'pembayaran_'
            . now()->format('Ymd_His')
            . '.xlsx';

        return $this->streamResponse(
            $spreadsheet,
            $filename
        );
    }

    private function writeReportHeader(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $title,
        string $filterInfo,
        int $totalColumns
    ): void {
        $lastColumn =
            Coordinate::stringFromColumnIndex(
                $totalColumns
            );

        $sheet->mergeCells(
            "A1:{$lastColumn}1"
        );

        $sheet->setCellValue(
            'A1',
            $title
        );

        $sheet->getStyle('A1')
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => [
                        'rgb' =>
                        self::COLOR_HEADER_FG,
                    ],
                ],
                'fill' => [
                    'fillType' =>
                    Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' =>
                        self::COLOR_HEADER_BG,
                    ],
                ],
                'alignment' => [
                    'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,
                    'vertical' =>
                    Alignment::VERTICAL_CENTER,
                ],
            ]);

        $sheet->getRowDimension(1)
            ->setRowHeight(30);

        $sheet->mergeCells(
            "A2:{$lastColumn}2"
        );

        $sheet->setCellValue(
            'A2',
            $filterInfo
        );

        $sheet->getStyle('A2')
            ->applyFromArray([
                'font' => [
                    'italic' => true,
                    'size' => 9,
                    'color' => [
                        'rgb' => '6B7280',
                    ],
                ],
                'alignment' => [
                    'horizontal' =>
                    Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' =>
                    Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F9FAFB',
                    ],
                ],
            ]);

        $sheet->mergeCells(
            "A3:{$lastColumn}3"
        );

        $sheet->setCellValue(
            'A3',
            'Diekspor pada: '
                . now()
                ->timezone('Asia/Jakarta')
                ->format('d/m/Y H:i')
                . ' WIB'
        );

        $sheet->getStyle('A3')
            ->applyFromArray([
                'font' => [
                    'size' => 9,
                    'color' => [
                        'rgb' => '9CA3AF',
                    ],
                ],
                'alignment' => [
                    'horizontal' =>
                    Alignment::HORIZONTAL_RIGHT,
                ],
                'fill' => [
                    'fillType' =>
                    Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'F9FAFB',
                    ],
                ],
            ]);
    }

    private function writeColumnHeaders(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        int $row,
        array $headers
    ): void {
        foreach ($headers as $index => $header) {
            $column =
                Coordinate::stringFromColumnIndex(
                    $index + 1
                );

            $sheet->setCellValue(
                "{$column}{$row}",
                $header
            );
        }

        $lastColumn =
            Coordinate::stringFromColumnIndex(
                count($headers)
            );

        $sheet->getStyle(
            "A{$row}:{$lastColumn}{$row}"
        )->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'rgb' =>
                    self::COLOR_HEADER_FG,
                ],
            ],
            'fill' => [
                'fillType' =>
                Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' =>
                    self::COLOR_SUBHEADER,
                ],
            ],
            'alignment' => [
                'horizontal' =>
                Alignment::HORIZONTAL_CENTER,
                'vertical' =>
                Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                    Border::BORDER_THIN,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
            ],
        ]);

        $sheet->getRowDimension($row)
            ->setRowHeight(24);
    }

    private function buildFilterDescription(
        array $filters
    ): string {
        $parts = [];

        if (!empty($filters['search'])) {
            $parts[] =
                'Kata kunci: "'
                . $filters['search']
                . '"';
        }

        $bookingStatuses = [
            'pending_payment' =>
            'Menunggu Pembayaran',
            'delivered' =>
            'Siap Diantar',
            'assigned' =>
            'Ditugaskan',
            'on_delivery' =>
            'Sedang Diantar',
            'arrived' =>
            'Sudah Sampai',
            'completed' =>
            'Selesai',
            'expired' =>
            'Kedaluwarsa',
            'canceled' =>
            'Dibatalkan',
        ];

        $paymentStatuses = [
            'unpaid' =>
            'Belum Dibayar',
            'partial' =>
            'Sudah DP',
            'paid' =>
            'Lunas',
            'refunded' =>
            'Dikembalikan',
        ];

        if (!empty($filters['booking_status'])) {
            $parts[] =
                'Status Pesanan: '
                . (
                    $bookingStatuses[$filters['booking_status']]
                    ?? $filters['booking_status']
                );
        }

        if (!empty($filters['payment_status'])) {
            $parts[] =
                'Status Bayar: '
                . (
                    $paymentStatuses[$filters['payment_status']]
                    ?? $filters['payment_status']
                );
        }

        if (!empty($filters['status'])) {
            $parts[] =
                'Status: '
                . $filters['status'];
        }

        if (!empty($filters['package_id'])) {
            $parts[] =
                'Paket: '
                . $filters['package_id'];
        }

        if (!empty($filters['driver_id'])) {
            $parts[] =
                'Driver: '
                . $filters['driver_id'];
        }

        if (!empty($filters['date_from'])) {
            $parts[] =
                'Dari: '
                . $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $parts[] =
                'Hingga: '
                . $filters['date_to'];
        }

        return $parts === []
            ? 'Filter: Semua data'
            : 'Filter: '
            . implode(' | ', $parts);
    }

    private function vehicleLabel(
        ?string $vehicleType
    ): string {
        return match ($vehicleType) {
            'personal' =>
            'Motor Pribadi',
            'company' =>
            'Motor Kantor',
            default =>
            '—',
        };
    }

    private function formatDateTime(
        $value
    ): string {
        if (!$value) {
            return '—';
        }

        return Carbon::parse($value)
            ->timezone('Asia/Jakarta')
            ->format('d/m/Y H:i');
    }

    private function streamResponse(
        Spreadsheet $spreadsheet,
        string $filename
    ): StreamedResponse {
        $writer =
            new Xlsx($spreadsheet);

        return new StreamedResponse(
            function () use ($writer): void {
                $writer->save(
                    'php://output'
                );
            },
            200,
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                'Content-Disposition' =>
                'attachment; filename="'
                    . $filename
                    . '"',

                'Cache-Control' =>
                'max-age=0',

                'Pragma' =>
                'no-cache',

                'Expires' =>
                '0',
            ]
        );
    }
}
