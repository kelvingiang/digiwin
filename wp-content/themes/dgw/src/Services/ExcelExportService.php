<?php
declare(strict_types=1);

namespace DGW\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Service xuất Excel dùng chung (chuẩn hóa style, chống Formula Injection và dọn output buffer triệt để)
 */
class ExcelExportService
{
    /**
     * [2026-11-20] - @author: Kelvin - Xuất danh sách dữ liệu ra file .xlsx và stream trực tiếp về trình duyệt
     *
     * @param string[]             $headers      Tiêu đề các cột
     * @param array<int, array>    $rows         Mảng dữ liệu từng dòng (theo thứ tự cột)
     * @param string               $filenameBase Tên file cơ sở (không kèm ngày và đuôi .xlsx)
     * @param int[]                $stringCols   Chỉ số cột (0-based) ép kiểu chuỗi để giữ số 0 đầu (SĐT, mã số thuế...)
     */
    public static function streamXlsx(array $headers, array $rows, string $filenameBase, array $stringCols = []): void
    {
        if (!class_exists(Spreadsheet::class)) {
            $autoloadPath = get_template_directory() . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

        $colCount = count($headers);
        $lastCol  = Coordinate::stringFromColumnIndex($colCount);

        // Ghi hàng tiêu đề
        foreach ($headers as $index => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0000FF');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(60);

        // Ghi từng dòng dữ liệu kèm bảo vệ Formula Injection
        $rowIdx = 2;
        foreach ($rows as $row) {
            foreach (array_values($row) as $colIndex => $value) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $safeValue = self::neutralizeFormula((string) ($value ?? ''));

                if (in_array($colIndex, $stringCols, true)) {
                    $sheet->setCellValueExplicit($colLetter . $rowIdx, $safeValue, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue($colLetter . $rowIdx, $safeValue);
                }
            }
            $rowIdx++;
        }

        for ($i = 1; $i <= $colCount; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastCol}" . ($rowIdx - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $safeBase = sanitize_file_name($filenameBase);
        $filename = $safeBase . '_' . wp_date('Y-m-d') . '.xlsx';

        // Dọn sạch mọi tầng Output Buffer tránh làm hỏng cấu trúc file nhị phân .xlsx
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Chặn Excel Formula Injection (=, +, -, @, tab, newline) khi xuất chuỗi từ người dùng
     */
    private static function neutralizeFormula(string $value): string
    {
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
