<?php
declare(strict_types=1);

use DGW\Controllers\CompanyInformationController;
use DGW\Controllers\LogoController;
use DGW\Controllers\MemberController;
use DGW\Controllers\MemberDownloadReportController;
use DGW\Controllers\MemberViewReportController;
use DGW\Controllers\PopupController;
use DGW\Controllers\PostTypeRegistry;
use DGW\Controllers\WebSettingController;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Bộ điều phối trung tâm khởi tạo CPT và Admin Controllers theo chuẩn PSR-4
 */
class Controller_Main
{
    private string $_controller_name = 'main_controller_options';

    /**
     * @var array<string, bool>
     */
    private array $_controller_options = [];

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo CPT trên mọi request và chỉ nạp Admin Controllers khi ở WP-Admin
     */
    public function __construct()
    {
        $defaultOption = [
            'controller_setting'         => false,
            'controller_case_study'      => true,
            'controller_resources'       => true,
            'controller_active'          => true,
            'controller_industries'      => true,
            'controller_solutions'       => true,
            'controller_services'        => true,
            'controller_information'     => true,
            'controller_slider'          => true,
            'controller_join_us'         => true,
            'controller_logo'            => true,
            'controller_popup'           => true,
            'controller_member'          => true,
            'controller_member_download' => true,
            'controller_member_view'     => true,
        ];

        $savedOptions              = get_option($this->_controller_name, $defaultOption);
        $this->_controller_options = wp_parse_args(is_array($savedOptions) ? $savedOptions : [], $defaultOption);

        // 1. Đăng ký các Custom Post Type đang bật
        PostTypeRegistry::registerEnabled($this->_controller_options);

        // 2. Chỉ khởi tạo các Admin Page Controller trong WP-Admin để tối ưu TTFB ở Frontend
        if (is_admin()) {
            $this->registerAdminControllers();
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các trang quản trị WP-Admin theo bản đồ cấu hình
     */
    private function registerAdminControllers(): void
    {
        $adminControllers = [
            'controller_popup'           => PopupController::class,
            'controller_information'     => CompanyInformationController::class,
            'controller_setting'         => WebSettingController::class,
            'controller_logo'            => LogoController::class,
            'controller_member'          => MemberController::class,
            'controller_member_download' => MemberDownloadReportController::class,
            'controller_member_view'     => MemberViewReportController::class,
        ];

        foreach ($adminControllers as $optionKey => $controllerClass) {
            if (!empty($this->_controller_options[$optionKey])) {
                new $controllerClass();
            }
        }
    }
}
