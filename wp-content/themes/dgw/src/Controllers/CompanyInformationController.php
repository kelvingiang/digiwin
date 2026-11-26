<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Helpers\RequestHelper;
use DGW\Models\CompanyInfoModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller quản lý Thông tin công ty (bổ sung Nonce, sanitize input theo loại dữ liệu và PRG redirect)
 */
class CompanyInformationController extends BaseAdminController
{
    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu Company Information trong WP-Admin
     */
    protected function addMenuPage()
    {
        return add_menu_page(
            __('Company Information', 'dgw'),
            __('Company Information', 'dgw'),
            $this->capability,
            'information_page',
            [$this, 'renderPage'],
            PART_ICON . 'icon-setting.png',
            26
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra Nonce, sanitize và lưu thông tin công ty trước khi render HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        if (!RequestHelper::isPost()) {
            return;
        }

        $this->verifyNonceAction('dgw_info_save_action', 'dgw_info_nonce');
        $postData = wp_unslash($_POST);

        $textFields = [
            'txt-phone'            => '_info_phone',
            'txt-fax'              => '_info_fax',
            'txt-years-experience' => '_info_years_experience',
            'txt-project-clients'  => '_info_project_clients',
            'txt-product-solution' => '_info_product_solution',
            'txt-name-cn'          => '_info_name_cn',
            'txt-address-cn'       => '_info_address_cn',
            'txt-name-vn'          => '_info_name_vn',
            'txt-address-vn'       => '_info_address_vn',
        ];

        // [25/11/2026] - Gom cập nhật thông tin công ty qua CompanyInfoModel để tự động xóa Transient cache
        $updates = [];

        foreach ($textFields as $inputKey => $metaKey) {
            if (isset($postData[$inputKey])) {
                $updates[$metaKey] = sanitize_text_field((string) $postData[$inputKey]);
            }
        }

        if (isset($postData['txt-email'])) {
            $updates['_info_email'] = sanitize_email((string) $postData['txt-email']);
        }

        $richFields = [
            'txt-summary-cn'   => '_info_summary_cn',
            'txt-operating-cn' => '_info_operating_cn',
            'txt-location-cn'  => '_info_location_cn',
            'txt-summary-vn'   => '_info_summary_vn',
            'txt-operating-vn' => '_info_operating_vn',
            'txt-location-vn'  => '_info_location_vn',
        ];

        foreach ($richFields as $inputKey => $metaKey) {
            if (isset($postData[$inputKey])) {
                $updates[$metaKey] = wp_kses_post((string) $postData[$inputKey]);
            }
        }

        if (!empty($updates)) {
            CompanyInfoModel::updateMany($updates);
        }

        wp_safe_redirect(admin_url('admin.php?page=information_page&msg=1'));
        exit;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện chỉnh sửa thông tin công ty
     */
    public function renderPage(): void
    {
        $this->renderView('view-information.php');
    }
}
