<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceYearlySummaryExport
{
    private const CURRENCY_FORMAT = '[$Rp-421] #,##0';

    private const TITLE_FILL = 'B4C7E7';

    public function __construct(
        private readonly Collection $invoices,
        private readonly int $year,
    ) {}

    public function download(): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'invoice-summary-');
        $writer = new Xlsx($this->spreadsheet());
        $writer->save($temporaryFile);

        return $temporaryFile;
    }

    public function filename(): string
    {
        return 'monitoring-invoices-'.$this->year.'.xlsx';
    }

    private function spreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Invoices');

        $this->setColumnWidths($sheet);
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Monitoring Invoices '.$this->year);
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getStyle('A1:H1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::TITLE_FILL],
            ],
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $headers = ['Bulan', 'No', 'Tgl Pengiriman', 'NO INV', 'Untuk Pembayaran', 'Status', 'Nama PT', 'Nilai Project'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 2], $header);
        }

        $lastRow = $this->writeRows($sheet);
        $this->styleTable($sheet, $lastRow);
        $sheet->getStyle("H3:H{$lastRow}")->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle("H3:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.2)->setRight(0.2)->setBottom(0.2)->setLeft(0.2);
        $sheet->getPageSetup()->setPrintArea("A1:H{$lastRow}");

        return $spreadsheet;
    }

    private function setColumnWidths($sheet): void
    {
        foreach (['A' => 14, 'B' => 7, 'C' => 16, 'D' => 24, 'E' => 24, 'F' => 22, 'G' => 28, 'H' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function writeRows($sheet): int
    {
        $row = 3;
        $number = 1;
        $monthStart = null;
        $month = null;

        foreach ($this->invoices as $invoice) {
            $invoiceMonth = $invoice->created_at->month;
            if ($month !== null && $invoiceMonth !== $month) {
                $this->mergeMonth($sheet, $monthStart, $row - 1, $month);
                $monthStart = $row;
            } elseif ($monthStart === null) {
                $monthStart = $row;
            }
            $month = $invoiceMonth;

            $sheet->fromArray([
                null,
                $number++,
                $invoice->created_at->format('n/j/Y'),
                $invoice->invoice_number ?: '-',
                $invoice->purchaseOrder?->po_number ?: '-',
                ucfirst((string) $invoice->status),
                $invoice->recipient ?: '-',
                (float) $invoice->grand_total,
            ], null, "A{$row}");
            $row++;
        }

        if ($month !== null) {
            $this->mergeMonth($sheet, $monthStart, $row - 1, $month);
        }

        return max(2, $row - 1);
    }

    private function mergeMonth($sheet, int $startRow, int $endRow, int $month): void
    {
        if ($startRow < $endRow) {
            $sheet->mergeCells("A{$startRow}:A{$endRow}");
        }
        $sheet->setCellValue("A{$startRow}", date('F', mktime(0, 0, 0, $month, 1)));
        $sheet->getStyle("A{$startRow}:A{$endRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function styleTable($sheet, int $lastRow): void
    {
        $sheet->getStyle("A2:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '111111'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B3:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C3:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }
}
