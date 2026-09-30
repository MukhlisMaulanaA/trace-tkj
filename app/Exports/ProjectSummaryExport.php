<?php

namespace App\Exports;

use App\Models\ProjectExpenditure;
use App\Models\ProjectSummary;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProjectSummaryExport
{
  private const HEADER_FILL = 'F4C7A7';
  private const TOTAL_FILL = 'FFFF00';
  private const GREEN_FILL = '00B050';
  private const BORDER_COLOR = '666666';
  private const CURRENCY_FORMAT = '[$Rp-421] #,##0';

  public function __construct(private readonly ProjectSummary $record)
  {
    $this->record->loadMissing('project');
  }

  public function download(): string
  {
    $spreadsheet = $this->spreadsheet();
    $writer = new Xlsx($spreadsheet);
    $temporaryFile = tempnam(sys_get_temp_dir(), 'project-summary-');

    $writer->save($temporaryFile);

    return $temporaryFile;
  }

  public function filename(): string
  {
    $title = $this->record->project?->nama_project
      ?: $this->record->project_name
      ?: 'project';
    $title = preg_replace('/[^A-Za-z0-9_-]+/', '-', $title) ?: 'project';

    return 'rekap-pengeluaran-' . trim($title, '-') . '.xlsx';
  }

  private function spreadsheet(): Spreadsheet
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Project');
    $this->setColumnWidths($sheet);
    $this->writeHeader($sheet);
    $this->writeMetrics($sheet);
    $this->writeMaterialTable($sheet);
    $this->writeLabourTable($sheet);
    $this->writeFooter($sheet);
    $sheet->getPageSetup()->setOrientation('landscape')->setPaperSize('9');
    $sheet->getPageMargins()->setTop(0.3)->setRight(0.3)->setBottom(0.3)->setLeft(0.3);
    $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

    return $spreadsheet;
  }

  private function setColumnWidths($sheet): void
  {
    foreach (['A' => 7, 'B' => 33, 'C' => 20, 'D' => 24, 'E' => 3, 'F' => 7, 'G' => 27, 'H' => 20, 'I' => 20] as $column => $width) {
      $sheet->getColumnDimension($column)->setWidth($width);
    }
  }

  private function writeHeader($sheet): void
  {
    $companyName = 'PT. TANJUNG KARYA JAYA';
    $companyAddress = 'Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, Rt.009 / Rw.013, Kelurahan Kebalen, Kec. Babelan - Bekasi, Jawa Barat';
    $companyPhone = '0811-1020-770 - 0856-1539-431';
    $companyEmail = 'officetkj@tanjungkaryajaya.co.id / admin@tanjungkaryajaya.co.id';

    foreach (['C1:F1', 'A2:F2', 'A3:F3', 'A4:F4', 'A5:F5', 'H1:I2', 'H3:I3', 'A7:I7'] as $range) {
      $sheet->mergeCells($range);
    }
    $sheet->setCellValue('C1', $companyName);
    $sheet->setCellValue('A2', $companyAddress);
    $sheet->setCellValue('A3', 'Telp : ' . $companyPhone);
    $sheet->setCellValue('A4', 'Email : ' . $companyEmail);
    $sheet->setCellValue('A5', 'Website : tanjungkaryajaya.co.id');
    $sheet->setCellValue('H1', 'REKAPITULASI PROJECT');
    $sheet->setCellValue('H3', 'PT. TANJUNG KARYA JAYA');
    $sheet->setCellValue('A7', 'REKAP PENGELUARAN PROJECT ' . $this->projectTitle());
    $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A2:A5')->getFont()->setSize(8)->getColor()->setARGB('FF374151');
    $sheet->getStyle('H1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('H1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('H3')->getFont()->setSize(8)->getColor()->setARGB('FF6B7280');
    $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(15);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A6:I6')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

    $logoPath = public_path('images/logo-tkj.png');
    if (is_file($logoPath)) {
      $drawing = new Drawing();
      $drawing->setName('Company logo')->setPath($logoPath)->setHeight(48)->setCoordinates('A1')->setOffsetX(2)->setOffsetY(2)->setWorksheet($sheet);
    }
  }

  private function writeMetrics($sheet): void
  {
    $metrics = [
      ['Name of PIC Project', $this->record->project?->pic ?: '-'],
      ['Number PO', $this->record->po_numbers ?: '-'],
      ['Owner', $this->record->project?->kustomer ?: $this->record->owner_customer ?: '-'],
      ['PPH ' . number_format(ProjectSummary::PPH_RATE * 100, 2) . '%', (float) $this->record->pph_amount, 'currency'],
      ['Nilai Kontrak', (float) $this->record->contract_value, 'currency'],
      ['Final Profit', (float) $this->record->final_profit, 'currency'],
      ['Final Kontrak', (float) $this->record->final_contract_value, 'currency'],
      ['Balance', (float) $this->record->balance, 'currency'],
    ];
    $columns = [['A', 'B'], ['C', 'D'], ['F', 'G'], ['H', 'I']];

    foreach ($columns as $index => [$start, $end]) {
      foreach ([0, 1] as $rowOffset) {
        $row = 9 + ($rowOffset * 2);
        $metric = $metrics[$index * 2 + $rowOffset];
        $sheet->mergeCells($start . $row . ':' . $end . $row);
        $sheet->mergeCells($start . ($row + 1) . ':' . $end . ($row + 1));
        $sheet->setCellValue($start . $row, $metric[0]);
        $sheet->setCellValue($start . ($row + 1), $metric[1]);
        $this->styleMetric($sheet, $start . $row . ':' . $end . ($row + 1));
        $sheet->getStyle($start . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::HEADER_FILL);
        if (($metric[2] ?? null) === 'currency') {
          $sheet->getStyle($start . ($row + 1))->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        }
      }
    }
  }

  private function writeMaterialTable($sheet): void
  {
    $this->writeExpenditureTable($sheet, 14, 'Material', $this->record->expenditures()->where('type', ProjectExpenditure::TYPE_MATERIAL)->orderBy('expenditure_date')->get(), ['A', 'B', 'C', 'D'], 'TOTAL MATERIAL', (float) $this->record->material_expenditure);
  }

  private function writeLabourTable($sheet): void
  {
    $sheet->mergeCells('F19:I19');
    $sheet->setCellValue('F19', 'LABOUR');
    $this->styleHeading($sheet, 'F19:I19');
    $this->writeExpenditureTable($sheet, 20, null, $this->record->expenditures()->where('type', ProjectExpenditure::TYPE_LABOUR)->orderBy('expenditure_date')->get(), ['F', 'G', 'H', 'I'], 'TOTAL LABOUR', (float) $this->record->labour_expenditure);

    foreach (['F14:G14', 'F15:G15', 'F17:G17', 'F18:G18', 'H14:I15', 'H16:I18'] as $range) {
      $sheet->mergeCells($range);
    }
    $sheet->setCellValue('F14', 'Sisa Budget');
    $sheet->setCellValue('F15', (float) $this->record->remaining_budget);
    $sheet->setCellValue('F17', 'Pengeluaran M + L');
    $sheet->setCellValue('F18', (float) $this->record->ml_expenditure);
    $sheet->setCellValue('H14', 'Profit');
    $sheet->setCellValue('H16', (float) $this->record->profit_percentage / 100);
    foreach (['F14:G15', 'F17:G18', 'H14:I18'] as $range) {
      $this->styleMetric($sheet, $range);
    }
    foreach (['F14', 'F17', 'H14'] as $cell) {
      $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::HEADER_FILL);
    }
    $sheet->getStyle('F15')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF' . self::GREEN_FILL);
    $sheet->getStyle('F15:F18')->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
    $sheet->getStyle('H16')->getNumberFormat()->setFormatCode('0%');
    $sheet->getStyle('H16')->getFont()->setBold(true)->setSize(18);
  }

  private function writeExpenditureTable($sheet, int $row, ?string $heading, $expenditures, array $columns, string $totalLabel, float $total): void
  {
    if ($heading !== null) {
      $sheet->mergeCells($columns[0] . $row . ':' . $columns[3] . $row);
      $sheet->setCellValue($columns[0] . $row, strtoupper($heading));
      $this->styleHeading($sheet, $columns[0] . $row . ':' . $columns[3] . $row);
      $row++;
    }
    $headers = ['No.', $heading === 'Material' ? 'List Material' : 'Labour', $heading === 'Material' ? 'Harga Material' : 'Nilai', $heading === 'Material' ? 'Tanggal Pembelian' : 'Periode'];
    foreach ($headers as $index => $header) {
      $sheet->setCellValue($columns[$index] . $row, $header);
    }
    $this->styleHeading($sheet, $columns[0] . $row . ':' . $columns[3] . $row);
    foreach ($expenditures as $expenditure) {
      $row++;
      $sheet->setCellValue($columns[0] . $row, $row - ($heading === null ? 20 : 15));
      $sheet->setCellValue($columns[1] . $row, $expenditure->description);
      $sheet->setCellValue($columns[2] . $row, (float) $expenditure->amount);
      $sheet->setCellValue($columns[3] . $row, $expenditure->expenditure_date?->format('d/m/Y') ?? '-');
      $sheet->getStyle($columns[2] . $row)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
    }
    if ($expenditures->isEmpty()) {
      $row++;
      $sheet->mergeCells($columns[0] . $row . ':' . $columns[3] . $row);
      $sheet->setCellValue($columns[0] . $row, 'Belum ada pengeluaran ' . strtolower($heading ?? 'labour') . '.');
    }
    $row++;
    $sheet->mergeCells($columns[0] . $row . ':' . $columns[1] . $row);
    $sheet->mergeCells($columns[2] . $row . ':' . $columns[3] . $row);
    $sheet->setCellValue($columns[0] . $row, $totalLabel);
    $sheet->setCellValue($columns[2] . $row, $total);
    $this->styleTotal($sheet, $columns[0] . $row . ':' . $columns[3] . $row);
    $sheet->getStyle($columns[2] . $row)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
  }

  private function writeFooter($sheet): void
  {
    $row = max($sheet->getHighestRow() + 2, 25);
    $sheet->mergeCells('A' . $row . ':I' . $row);
    $sheet->setCellValue('A' . $row, 'PT. TANJUNG KARYA JAYA | Perumahan Bumi Anugrah Sejahtera Blok B4 - No.3, Rt.009 / Rw.013, Kelurahan Kebalen, Kec. Babelan - Bekasi, Jawa Barat');
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $row)->getFont()->setSize(8)->getColor()->setARGB('FF4B5563');
  }

  private function styleMetric($sheet, string $range): void
  {
    $sheet->getStyle($range)->applyFromArray([
      'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    ]);
    $sheet->getStyle($range)->getFont()->setBold(true);
  }

  private function styleHeading($sheet, string $range): void
  {
    $sheet->getStyle($range)->applyFromArray([
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::HEADER_FILL]],
      'font' => ['bold' => true],
      'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
      'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    ]);
  }

  private function styleTotal($sheet, string $range): void
  {
    $sheet->getStyle($range)->applyFromArray([
      'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::TOTAL_FILL]],
      'font' => ['bold' => true],
      'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF' . self::BORDER_COLOR]]],
    ]);
  }

  private function projectTitle(): string
  {
    return $this->record->project?->nama_project ?: $this->record->project_name ?: 'PROJECT';
  }
}