<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Helpers\RequestHelper;
use DGW\Services\ExcelExportService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller quản lý danh sách Thành viên và xuất báo cáo Excel
 */
class MemberController extends BaseAdminController
{
    private ?\Model_Download_Function $model = null;

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu Download Data trong WP-Admin
     */
    protected function addMenuPage()
    {
        return add_menu_page(
            __('Download Data', 'dgw'),
            __('Download Data', 'dgw'),
            $this->capability,
            'member_page',
            [$this, 'renderPage'],
            PART_ICON . 'icon-setting.png',
            2
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý export Excel và các thao tác thay đổi dữ liệu trước khi render HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        $action = $this->resolveListAction();
        $page   = 'member_page';

        switch ($action) {
            case 'export_members_excel':
                $this->verifyNonceAction('dgw_export_members_excel');
                $this->exportMembersExcel();
                break;

            case 'trash':
            case 'restore':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toTrash(getParams(), $action);
                RequestHelper::toBack(1);
                break;

            case 'delete':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toDelete(getParams());
                RequestHelper::toBack(1);
                break;

            case '-1':
                $this->redirectIfFilterSubmitted();
                break;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện danh sách hoặc chi tiết thành viên
     */
    public function renderPage(): void
    {
        if (getParams('action') === 'edit') {
            require_once DIR_VIEW . 'form-member.php';
            return;
        }

        require_once DIR_VIEW . 'view-member.php';
    }

    /**
     * [2026-11-20] - @author: Kelvin - Chuẩn bị dữ liệu thành viên và xuất file Excel qua ExcelExportService
     */
    private function exportMembersExcel(): void
    {
        require_once DIR_MODEL . 'model-download.php';

        $exporter = new \Model_Download();
        $data     = $exporter->get_export_data();

        if (empty($data)) {
            wp_safe_redirect(admin_url('admin.php?page=member_page'));
            exit;
        }

        $industryDict   = get_industry_dictionary();
        $departmentDict = get_department_dictionary();
        $positionDict   = get_position_dictionary();

        $headers = [
            'E-mail',
            '姓名 (Username)',
            '職位 (Position)',
            '部門 (Department)',
            '公司名稱 (Company)',
            '稅碼 (Tax)',
            '聯絡電話 (Phone)',
            '行業 (Industry)',
        ];

        $rows = [];
        foreach ($data as $row) {
            $lang          = !empty($row['language']) ? (string) $row['language'] : 'vn';
            $industryKey   = (string) ($row['industry'] ?? '');
            $departmentKey = (string) ($row['department'] ?? '');
            $positionKey   = (string) ($row['position'] ?? '');

            $rows[] = [
                (string) ($row['email'] ?? ''),
                (string) ($row['username'] ?? ''),
                (string) ($positionDict[$positionKey][$lang] ?? $positionKey),
                (string) ($departmentDict[$departmentKey][$lang] ?? $departmentKey),
                (string) ($row['company'] ?? ''),
                (string) ($row['tax'] ?? ''),
                (string) ($row['phone'] ?? ''),
                (string) ($industryDict[$industryKey][$lang] ?? $industryKey),
            ];
        }

        ExcelExportService::streamXlsx($headers, $rows, 'members_export', [5, 6]);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lazy-load Model_Download_Function khi thực sự cần thao tác DB
     */
    private function getModel(): \Model_Download_Function
    {
        if ($this->model === null) {
            require_once DIR_MODEL . 'model-download-function.php';
            $this->model = new \Model_Download_Function();
        }

        return $this->model;
    }
}
