<?php
// require_once(DIR_MODEL . 'model_check_in_setting.php');
class Controller_Member_Download_Report
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'Create'));
    }

    // PHAN TAO MENU CON TRONG MENU CHA CUNG LA POST TYPE
    public function Create()
    {
        $parent_slug = 'member_page';
        $page_title = __('下載統計');
        $menu_title = __('下載統計');
        $capability = 'manage_categories';
        $menu_slug = 'member_download_report';
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'));
    }

    public function dispatchActive()
    {
        //        echo __METHOD__;
        $action = getParams('action');
        switch ($action) {
            case 'export_download_excel':
                $this->exportDownloadExcelAction();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    public function displayPage()
    {
        require_once(DIR_VIEW . 'view-member-download.php');
    }

    public function exportDownloadExcelAction()
    {
        // Load composer autoload
        $autoload_path = get_template_directory() . '/vendor/autoload.php';
        if (file_exists($autoload_path)) {
            require_once($autoload_path);
        }

        require_once(DIR_MODEL . 'model-download-function.php');
        $model = new Model_Download_Function();
        $downloads = $model->get_all_downloads();

        if (!empty($downloads)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set default font to Arial
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $headers = [
                '姓名 (Name)', '公司名稱 (Company)', 'E-mail', '下載資源 (Downloaded Resource)', '下載日期 (Download Date)'
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
                ->getStartColor()->setARGB('FF0000FF'); // Blue background
            $sheet->getStyle($headerRange)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Set row height to 80
            $sheet->getRowDimension(1)->setRowHeight(60);

            $rowIdx = 2;
            foreach ($downloads as $download) {
                $sheet->setCellValue('A' . $rowIdx, $download->username);
                $sheet->setCellValue('B' . $rowIdx, $download->company);
                $sheet->setCellValue('C' . $rowIdx, $download->email);
                $sheet->setCellValue('D' . $rowIdx, $download->title);
                $sheet->setCellValue('E' . $rowIdx, date('Y-m-d H:i:s', strtotime($download->download_date)));
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
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A1:E' . ($rowIdx - 1))->applyFromArray($styleArray);

            $filename = "download_report_" . date("Y-m-d") . ".xlsx";

            // Xóa bỏ mọi output đã được tạo trước đó
            if (ob_get_length()) ob_clean();

            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        } else {
            // Nếu không có dữ liệu thì quay lại trang
            wp_redirect(admin_url('admin.php?page=member_download_report'));
        }
        exit();
    }
}
