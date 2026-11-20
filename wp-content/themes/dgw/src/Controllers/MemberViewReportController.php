<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Services\ExcelExportService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller trang thống kê lượt xem video YouTube (kid = 2) và xuất Excel
 */
class MemberViewReportController extends BaseAdminController
{
    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký submenu Video View Statistics dưới menu member_page
     */
    protected function addMenuPage()
    {
        return add_submenu_page(
            'member_page',
            __('Video View Statistics', 'dgw'),
            __('Video View Statistics', 'dgw'),
            $this->capability,
            'member_view_report',
            [$this, 'renderPage']
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý export Excel lượt xem video (kid = 2) trước khi xuất HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        if (getParams('action') !== 'export_view_excel') {
            return;
        }

        $this->verifyNonceAction('dgw_export_view_excel');

        require_once DIR_MODEL . 'model-download-function.php';
        $model = new \Model_Download_Function();
        $views = $model->get_all_downloads(2);

        if (empty($views)) {
            wp_safe_redirect(admin_url('admin.php?page=member_view_report'));
            exit;
        }

        $headers = [
            '姓名 (Name)',
            '公司名稱 (Company)',
            'E-mail',
            '觀看影片 (Watched Video)',
            '觀看日期 (Watch Date)',
        ];

        $rows = [];
        foreach ($views as $view) {
            $rows[] = [
                (string) ($view->username ?? ''),
                (string) ($view->company ?? ''),
                (string) ($view->email ?? ''),
                (string) ($view->title ?? ''),
                date('Y-m-d H:i:s', strtotime((string) ($view->download_date ?? 'now'))),
            ];
        }

        ExcelExportService::streamXlsx($headers, $rows, 'video_view_report');
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện thống kê lượt xem video
     */
    public function renderPage(): void
    {
        require_once DIR_VIEW . 'view-member-view.php';
    }
}
