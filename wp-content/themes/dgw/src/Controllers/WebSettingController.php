<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Helpers\RequestHelper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller quản lý Website Settings (bổ sung Nonce và ép kiểu số nguyên dương cho option)
 */
class WebSettingController extends BaseAdminController
{
    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu Website Settings trong WP-Admin
     */
    protected function addMenuPage()
    {
        return add_menu_page(
            __('Website Settings', 'dgw'),
            __('Website Settings', 'dgw'),
            $this->capability,
            'setting_page',
            [$this, 'renderPage'],
            PART_ICON . 'icon-setting.png',
            27
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lưu cấu hình phân trang khi submit form hợp lệ
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        if (!RequestHelper::isPost()) {
            return;
        }

        $this->verifyNonceAction('dgw_setting_save_action', 'dgw_setting_nonce');

        if (isset($_POST['txt-first-load'])) {
            update_option('first_load', absint(wp_unslash($_POST['txt-first-load'])));
        }

        if (isset($_POST['txt-more-load'])) {
            update_option('more_load', absint(wp_unslash($_POST['txt-more-load'])));
        }

        wp_safe_redirect(admin_url('admin.php?page=setting_page&msg=1'));
        exit;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện Website Settings
     */
    public function renderPage(): void
    {
        $this->renderView('view-setting.php');
    }
}
