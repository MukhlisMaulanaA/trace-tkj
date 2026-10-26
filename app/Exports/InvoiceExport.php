<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Services\Documents\CompanyExcelHeader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvoiceExport
{
    private const BORDER_COLOR = '111827';
    private const CURRENCY_FORMAT = '[$Rp-421] #,##0';

    public function __construct(private readonly Invoice $record)
    {
        $this->record->load(['purchaseOrder.project', 'items']);
    }

    public function download(): string
    {
        $writer = new Xlsx($this->spreadsheet());
        $temporaryFile = tempnam(sys_get_temp_dir(), 'invoice-');
        $writer->save($temporaryFile);

        return $temporaryFile;
    }

    public function filename(): string
    {
        $code = preg_replace('/[^A-Za-z0-9_-]+/', '-', $this->record->invoice_code ?: 'invoice') ?: 'invoice';

        return 'invoice-' . trim($code, '-') . '.xlsx';
    }

    private function spreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');
        $this->setColumnWidths($sheet);
        $this->writeHeader($sheet);
        $this->writeMeta($sheet);
        $lastRow = $this->writeItems($sheet);
        $lastRow = $this->writeSummary($sheet, $lastRow);
        $this->writeFooter($sheet, $lastRow + 2);
        $sheet->getPageSetup()->setOrientation('portrait')->setPaperSize('9');
        $sheet->getPageMargins()->setTop(0.3)->setRight(0.3)->setBottom(0.3)->setLeft(0.3);
        $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, CompanyExcelHeader::HEADER_END_ROW);
        $sheet->getPageSetup()->setPrintArea('A1:I' . $sheet->getHighestRow());
        $sheet->freezePane('A8');

        return $spreadsheet;
    }

    private function setColumnWidths($sheet): void
    {
        foreach (['A' => 11, 'B' => 12, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 34, 'G' => 11, 'H' => 18, 'I' => 20] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function writeHeader($sheet): void
    {
        (new CompanyExcelHeader())->render($sheet, 'INVOICE', config('company.name'));
        $sheet->getRowDimension(7)->setRowHeight(8);
    }

    private function writeMeta($sheet): void
    {
        $rows = [
            ['To', $this->record->recipient ?: '-'],
            ['Invoice Number', $this->record->invoice_number ?: '-'],
            ['Date', $this->formatDate($this->record->invoice_date)],
            ['Purchase Order', $this->record->purchaseOrder?->po_number ?: '-'],
            ['Project', $this->record->project_name ?: '-'],
            ['Payment', ucwords(str_replace('_', ' ', (string) $this->record->payment_type))],
        ];

        foreach ($rows as $index => [$label, $value]) {
            $row = 8 + $index;
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->mergeCells("D{$row}:I{$row}");
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("C{$row}", ':');
            $sheet->setCellValue("D{$row}", $value);
            $sheet->getRowDimension($row)->setRowHeight(15);
        }

        $sheet->getStyle('A8:I13')->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'font' => ['size' => 10],
        ]);
        $sheet->getStyle('A8:B13')->getFont()->setBold(true);
        $sheet->getStyle('C8:C13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private function writeItems($sheet): int
    {
        $headerRow = 15;
        $sheet->mergeCells("B{$headerRow}:F{$headerRow}");
        $headers = [
            'A' => 'No',
            'B' => 'Description',
            'G' => 'QTY',
            'H' => 'Unit Price (Rp)',
            'I' => 'Amount (Rp)',
        ];
        foreach ($headers as $column => $value) {
            $sheet->setCellValue($column . $headerRow, $value);
        }
        $this->styleTableHeader($sheet, "A{$headerRow}:I{$headerRow}");

        $row = $headerRow;
        foreach ($this->record->items as $item) {
            $row++;
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", $item->item_no);
            $sheet->setCellValue("B{$row}", $item->description);
            $sheet->setCellValue("G{$row}", (float) $item->quantity);
            $sheet->setCellValue("H{$row}", (float) $item->unit_price);
            $sheet->setCellValue("I{$row}", (float) $item->amount);
            $sheet->getRowDimension($row)->setRowHeight(42);
        }

        if ($this->record->items->isEmpty()) {
            $row++;
            $sheet->mergeCells("B{$row}:F{$row}");
            $sheet->mergeCells("G{$row}:I{$row}");
            $sheet->setCellValue("B{$row}", 'Belum ada item invoice.');
            $sheet->getRowDimension($row)->setRowHeight(42);
        }

        $this->styleTableBody($sheet, "A{$headerRow}:I{$row}");
        $sheet->getStyle("A{$headerRow}:I{$row}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A{$headerRow}:G{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$headerRow}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$headerRow}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H{$headerRow}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("H16:I{$row}")->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);

        return $row;
    }

    private function writeSummary($sheet, int $row): int
    {
        $row++;
        $this->writeSummaryRow($sheet, $row, 'SUB. TOTAL', (float) $this->record->subtotal);

        if ($this->record->discount_enabled) {
            $row++;
            $this->writeSummaryRow($sheet, $row, 'DISCOUNT', (float) $this->record->discount_amount, ':', $this->percentage($this->record->discount_percent));
        }

        $subtotalAfterDiscount = max(0, (float) $this->record->subtotal - (float) $this->record->discount_amount);
        $row++;
        $this->writeSummaryRow($sheet, $row, 'TOTAL', $subtotalAfterDiscount);

        $row++;
        $this->writeSummaryRow(
            $sheet,
            $row,
            (string) $this->record->payment_type,
            (float) $this->record->payment_progress_amount,
            ':',
            (float) $this->record->payment_progress > 0 ? $this->percentage($this->record->payment_progress) : '-',
        );

        if ($this->record->dpp_enabled) {
            $row++;
            $this->writeSummaryRow($sheet, $row, 'DPP 11/12', (float) $this->record->dpp_amount, ':');
        }

        if ($this->record->ppn_enabled) {
            $row++;
            $ppnRate = $this->record->dpp_enabled ? 12 : 11;
            $this->writeSummaryRow($sheet, $row, 'PPN', (float) $this->record->ppn_amount, ':', $ppnRate . '%');
        }

        $row++;
        $this->writeSummaryRow($sheet, $row, 'GRAND TOTAL (IDR)', (float) $this->record->grand_total, null, null, true);

        return $row;
    }

    private function writeSummaryRow($sheet, int $row, string $label, float $amount, ?string $separator = null, ?string $rate = null, bool $grandTotal = false): void
    {
        $sheet->mergeCells("B{$row}:F{$row}");
        $sheet->setCellValue("B{$row}", $label);
        $sheet->setCellValue("G{$row}", $separator);
        $sheet->setCellValue("H{$row}", $rate);
        $sheet->setCellValue("I{$row}", $amount);
        $sheet->getRowDimension($row)->setRowHeight(22);
        $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        if ($grandTotal) {
            $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
        }
    }

    private function writeFooter($sheet, int $row): void
    {
        $bank = $this->bankDetails($this->record->payment_method);
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->mergeCells("F{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", 'PAYMENT TO THE FOLLOWING ACCOUNT :');
        $sheet->setCellValue("F{$row}", 'Bekasi, ' . $this->formatDate(now()));
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$row}:F{$row}")->getFont()->setBold(true);

        foreach ([
            ['BANK', $bank['bank']],
            ['ACCOUNT NO', $bank['account']],
            ['ACCOUNT NAME', $bank['name']],
        ] as $index => [$label, $value]) {
            $footerRow = $row + $index + 1;
            $sheet->mergeCells("A{$footerRow}:B{$footerRow}");
            $sheet->mergeCells("C{$footerRow}:E{$footerRow}");
            $sheet->setCellValue("A{$footerRow}", $label);
            $sheet->setCellValue("C{$footerRow}", ': ' . $value);
        }

        $signatureRow = $row + 5;
        $sheet->mergeCells("F{$signatureRow}:I{$signatureRow}");
        $sheet->setCellValue("F{$signatureRow}", '( ILHAM JAWAZ )');
        $sheet->getStyle("F{$signatureRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F{$signatureRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$signatureRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
    }

    private function styleTableHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(15)->setRowHeight(28);
    }

    private function styleTableBody($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FF' . self::BORDER_COLOR);
    }

    private function percentage(float|string|null $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%';
    }

    private function formatDate($value): string
    {
        return $value ? \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y') : '-';
    }

    private function bankDetails(?string $method): array
    {
        return match ($method) {
            'bca_ilham' => ['bank' => 'Bank BCA (ILHAM JAWAZ)', 'account' => '521-139-9741', 'name' => 'ILHAM JAWAZ'],
            'bca_rohiman' => ['bank' => 'Bank BCA (ROHIMAN)', 'account' => '741-053-9483', 'name' => 'ROHIMAN'],
            default => ['bank' => 'Bank Mandiri', 'account' => '156-00-2075315-0', 'name' => 'PT. TANJUNG KARYA JAYA'],
        };
    }
}
