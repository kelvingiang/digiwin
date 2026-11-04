<?php
/**
 * [03/11/2026] - Controller quản lý trang thống kê lượt xem video YouTube (kid = 2) và xuất báo cáo Excel
 */
class Controller_Member_View_Report
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'Create'));
    }

    // [03/11/2026] - Tạo submenu thống kê lượt xem video trong menu Member
    public function Create()
    {
        $parent_slug = 'member_page';
        $page_title  = __('Video View Statistics', 'dgw');
        $menu_title  = __('Video View Statistics', 'dgw');
        $capability  = 'manage_categories';
        $menu_slug   = 'member_view_report';
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'));
    }

    // [03/11/2026] - Điều hướng action hiển thị giao diện hoặc xuất file Excel
    public function dispatchActive()
    {
        $action = getParams('action');
        switch ($action) {
            case 'export_view_excel':
                $this->exportViewExcelAction();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    // [03/11/2026] - Hiển thị view thống kê lượt xem video (kid = 2)
    public function displayPage()
    {
        require_once(DIR_VIEW . 'view-member-view.php');
    }

    // [03/11/2026] - Xuất báo cáo Excel danh sách thành viên xem video YouTube (kid = 2)
    public function exportViewExcelAction()
    {
        // Load composer autoload
        $autoload_path = get_template_directory() . '/vendor/autoload.php';
        if (file_exists($autoload_path)) {
            require_once($autoload_path);
        }

        require_once(DIR_MODEL . 'model-download-function.php');
        $model = new Model_Download_Function();
        // Chỉ lấy dữ liệu xem video YouTube (kid = 2)
        $views = $model->get_all_downloads(2);

        if (!empty($views)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            // Set default font to Arial
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $headers = [
                '姓名 (Name)',
                '公司名稱 (Company)',
                'E-mail',
                '觀看影片 (Watched Video)',
                '觀看日期 (Watch Date)'
            ];

            // Set headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Style header row (A1 to E1)
            $headerRange = 'A1:E1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0000FF');
            $sheet->getStyle($headerRange)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getRowDimension(1)->setRowHeight(60);

            $rowIdx = 2;
            foreach ($views as $view) {
                $sheet->setCellValue('A' . $rowIdx, $view->username);
                $sheet->setCellValue('B' . $rowIdx, $view->company);
                $sheet->setCellValue('C' . $rowIdx, $view->email);
                $sheet->setCellValue('D' . $rowIdx, $view->title);
                $sheet->setCellValue('E' . $rowIdx, date('Y-m-d H:i:s', strtotime($view->download_date)));
                $rowIdx++;
            }

            // Auto-size columns to fit content
            foreach (range('A', 'E') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Add borders to all cells
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A1:E' . ($rowIdx - 1))->applyFromArray($styleArray);

            $filename = "video_view_report_" . date("Y-m-d") . ".xlsx";

            // Xóa bỏ mọi output đã được tạo trước đó
            if (ob_get_length()) {
                ob_clean();
            }

            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        } else {
            wp_redirect(admin_url('admin.php?page=member_view_report'));
        }
        exit();
    }
}
