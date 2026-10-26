<?php

namespace App\Services\Documents;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyExcelHeader
{
    public const HEADER_END_ROW = 7;

    public function render(Worksheet $sheet, string $title, ?string $subtitle = null, ?string $documentHeading = null): void
    {
        $company = config('company');

        foreach (['B1:F1', 'B2:F2', 'B3:F3', 'B4:F4', 'B5:F5', 'H1:I2', 'H3:I3'] as $range) {
            $sheet->mergeCells($range);
        }

        $sheet->setCellValue('B1', $company['name']);
        $sheet->setCellValue('B2', $company['address']);
        $sheet->setCellValue('B3', 'Telp : ' . $company['phones']);
        $sheet->setCellValue('B4', 'Email : ' . $company['emails']);
        $sheet->setCellValue('B5', 'Website : ' . $company['website']);
        $sheet->setCellValue('H1', $title);
        $sheet->setCellValue('H3', $subtitle ?: $company['name']);

        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B1:F5')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('B2:F5')->getFont()->setSize(8)->getColor()->setARGB('FF374151');
        $sheet->getStyle('H1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('H1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H3')->getFont()->setSize(8)->getColor()->setARGB('FF6B7280');
        $sheet->getStyle('A6:I6')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        foreach ([1 => 30, 2 => 15, 3 => 15, 4 => 15, 5 => 15, 6 => 8] as $row => $height) {
            $sheet->getRowDimension($row)->setRowHeight($height);
        }

        if ($documentHeading !== null) {
            $sheet->mergeCells('A7:I7');
            $sheet->setCellValue('A7', $documentHeading);
            $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(15);
            $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(7)->setRowHeight(26);
        }

        $logoPath = public_path($company['logo']);
        if (is_file($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Company logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(72);
            $drawing->setCoordinates('A2');
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sheet);
        }
    }
}
