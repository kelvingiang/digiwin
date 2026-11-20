<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Services\ExcelExportService;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller trang thống kê lượt tải tài liệu (kid = 1) và xuất Excel
 */
class MemberDownloadReportController extends BaseAdminController
{
    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký submenu Download Statistics dưới menu member_page
     */
    protected function addMenuPage()
    {
        return add_submenu_page(
            'member_page',
            __('Download Statistics', 'dgw'),
            __('Download Statistics', 'dgw'),
            $this->capability,
            'member_download_report',
            [$this, 'renderPage']
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý export Excel lượt tải file (kid = 1) trước khi xuất HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        if (getParams('action') !== 'export_download_excel') {
            return;
        }

        $this->verifyNonceAction('dgw_export_download_excel');

        require_once DIR_MODEL . 'model-download-function.php';
        $model     = new \Model_Download_Function();
        $downloads = $model->get_all_downloads(1);

        if (empty($downloads)) {
            wp_safe_redirect(admin_url('admin.php?page=member_download_report'));
            exit;
        }

        $headers = [
            '姓名 (Name)',
            '公司名稱 (Company)',
            'E-mail',
            '下載資源 (Downloaded Resource)',
            '下載日期 (Download Date)',
        ];

        $rows = [];
        foreach ($downloads as $download) {
            $rows[] = [
                (string) ($download->username ?? ''),
                (string) ($download->company ?? ''),
                (string) ($download->email ?? ''),
                (string) ($download->title ?? ''),
                date('Y-m-d H:i:s', strtotime((string) ($download->download_date ?? 'now'))),
            ];
        }

        ExcelExportService::streamXlsx($headers, $rows, 'download_report');
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện thống kê lượt tải tài liệu
     */
    public function renderPage(): void
    {
        require_once DIR_VIEW . 'view-member-download.php';
    }
}
