<?php
require_once(DIR_MODEL . 'model-download.php');
class Controller_Member
{
    private $model;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create'));
        $this->model = new Model_Download_Function();
    }

    public function create()
    {
        // THEM 1 NHOM MENU MOI VAO TRONG ADMIN MENU
        $page_title = __('資料下載'); // TIEU DE CUA TRANG
        $menu_title = __('資料下載');  // TEN HIEN TRONG MENU
        // CHON QUYEN TRUY CAP manage_categories DE role ADMINNITRATOR VÀ EDITOR DEU THAY DUOC
        $capability = 'manage_categories'; // QUYEN TRUY CAP DE THAY MENU NAY
        $menu_slug = 'member_page'; // TEN slug TEN DUY NHAT KO DC TRUNG VOI TRANG KHAC GAN TREN THANH DIA CHI OF MENU
        // THAM SO THU 5 GOI DEN HAM HIEN THI GIAO DIEN TRONG MENU
        $icon = PART_ICON . 'icon-setting.png';  // THAM SO THU 6 LA LINK DEN ICON DAI DIEN
        $position = 2; // VI TRI HIEN THI TRONG MENU

        add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'), $icon, $position);
    }

    /* PHAN DIEN HUONG CHO  CAC ACTION ============================ */

    public function dispatchActive()
    {

        $action = getParams('action');
        switch ($action) {
            case 'trash':
            case 'restore':
                $this->trashAction();
                break;
            case 'delete':
                $this->deleteAction();
                break;
            case 'edit':
                $this->editAction();
                break;
            case 'export_members_excel':
                $this->exportMembersExcelAction();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    public function createUrl()
    {
        echo $url = 'admin.php?page=' . getParams('page');

        if (getParams('filter_category') != '0') {
            $url .= '&filter_category=' . getParams('filter_category');
        }

        if (mb_strlen(getParams('s'))) {
            $url .= '&s=' . getParams('s');
        }
        return $url;
    }

    public function displayPage()
    {
        if (getParams('action') == -1) {
            $url = $this->createUrl();
            wp_redirect($url);
        }

        if (isPost()) {
            update_option('first_load', $_POST['txt-first-load']);
            update_option('more_load', $_POST['txt-more-load']);
        }
        require_once(DIR_VIEW . 'view-member.php');
    }

    public function editAction()
    {
        if (isPost()) {
        }
        require_once(DIR_VIEW . 'from-member.php');
    }

    public function trashAction()
    {
        $this->model->toTrash(getParams(), getParams('action'));
        require_once(DIR_VIEW . 'view-member.php');
    }


    public function deleteAction()
    {
        $this->model->toDelete(getParams());
        require_once(DIR_VIEW . 'view-member.php');
    }

    public function exportMembersExcelAction()
    {
        // Load composer autoload
        $autoload_path = get_template_directory() . '/vendor/autoload.php';
        if (file_exists($autoload_path)) {
            require_once($autoload_path);
        }

        require_once(DIR_MODEL . 'model-download.php');
        require_once(get_template_directory() . '/inc/code/code-member-dictionary.php');

        $exporter = new Model_Download();
        $data = $exporter->get_export_data();

        if ($data) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set default font to Arial
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $headers = [
                'E-mail', '姓名 (Username)', '職位 (Position)', '部門 (Department)', '公司名稱 (Company)','稅碼 (Tax)',  '聯絡電話 (Phone)', '行業 (Industry)'
            ];

            // Set headers
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            // Style header row (A1 to G1)
            $headerRange = 'A1:H1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0000FF'); // Blue background
            $sheet->getStyle($headerRange)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Set row height to 80
            $sheet->getRowDimension(1)->setRowHeight(60);

            $industry_dict = get_industry_dictionary();
            $department_dict = get_department_dictionary();
            $position_dict = get_position_dictionary();

            $rowIdx = 2;
            foreach ($data as $row) {
                $lang = !empty($row['language']) ? $row['language'] : 'vn';

                $industry_key = $row['industry'] ?? '';
                $department_key = $row['department'] ?? '';
                $position_key = $row['position'] ?? '';

                $industry_val = $industry_dict[$industry_key][$lang] ?? $industry_key;
                $department_val = $department_dict[$department_key][$lang] ?? $department_key;
                $position_val = $position_dict[$position_key][$lang] ?? $position_key;

                $sheet->setCellValue('A' . $rowIdx, $row['email']);
                $sheet->setCellValue('B' . $rowIdx, $row['username']);
                $sheet->setCellValue('C' . $rowIdx, $position_val);
                $sheet->setCellValue('D' . $rowIdx, $department_val);
                $sheet->setCellValue('E' . $rowIdx, $row['company']);
                $sheet->setCellValueExplicit('F' . $rowIdx, $row['tax'] , \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                // Use setCellValueExplicit for phone to prevent Excel from converting it to scientific notation
                $sheet->setCellValueExplicit('G' . $rowIdx, $row['phone'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue('H' . $rowIdx, $industry_val);

                $rowIdx++;
            }

            // Auto-size columns to fit content
            foreach (range('A', 'H') as $columnID) {
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
            $sheet->getStyle('A1:H' . ($rowIdx - 1))->applyFromArray($styleArray);

            $filename = "members_export_" . date("Y-m-d") . ".xlsx";

            // Xóa bỏ mọi output đã được tạo trước đó để tránh file excel bị lỗi hỏng
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Set headers for file download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }
        exit();
    }
}
