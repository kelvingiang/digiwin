<?php
declare(strict_types=1);

namespace DGW\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Bộ điều phối trung tâm (Registry) quản lý đăng ký toàn bộ Custom Post Types (CPT) và Admin Controllers theo chuẩn PSR-4
 */
class PostTypeRegistry
{
    /**
     * [2026-11-20] - @author: Kelvin - Tên key option lưu cấu hình bật/tắt các phân hệ trong bảng wp_options
     */
    private const OPTION_NAME = 'main_controller_options';

    /**
     * [2026-11-20] - @author: Kelvin - Danh sách giá trị mặc định bật/tắt cho từng CPT và Admin Controller khi chưa có cấu hình lưu trong DB
     *
     * @var array<string, bool>
     */
    private const DEFAULT_OPTIONS = [
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

    /**
     * [2026-11-20] - @author: Kelvin - Điểm khởi chạy chính (được gọi từ Bootstrap::init()) theo quy trình 3 bước
     */
    public static function register(): void
    {
        // Bước 1: Đọc cấu hình từ wp_options và gộp với DEFAULT_OPTIONS để tránh thiếu key khi nâng cấp hoặc thêm controller mới
        $savedOptions = get_option(self::OPTION_NAME, self::DEFAULT_OPTIONS);
        $options      = wp_parse_args(is_array($savedOptions) ? $savedOptions : [], self::DEFAULT_OPTIONS);

        // Bước 2: Đăng ký toàn bộ Custom Post Types (chạy ở cả Frontend và Backend để WordPress nhận diện URL/Query/CPT)
        self::registerEnabled($options);

        // Bước 3: Chỉ khởi tạo các Admin Page Controller khi đang ở khu vực quản trị WP-Admin nhằm giảm tải bộ nhớ và tối ưu TTFB ở Frontend
        if (is_admin()) {
            self::registerAdminControllers($options);
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Ánh xạ key cấu hình sang class Admin Controller tương ứng và khởi tạo những phân hệ đang bật
     *
     * @param array<string, mixed> $options Mảng cấu hình bật/tắt sau khi đã merge với DEFAULT_OPTIONS
     */
    private static function registerAdminControllers(array $options): void
    {
        // Bước 3.1: Khai báo bản đồ mapping [option_key => Tên Class PSR-4 Admin Controller]
        // Khi tạo mới một Admin Controller (vd: ZaloMiniAppController), cần bổ sung key vào DEFAULT_OPTIONS và khai báo thêm vào mảng này
        $adminControllers = [
            'controller_popup'           => PopupController::class,
            'controller_information'     => CompanyInformationController::class,
            'controller_setting'         => WebSettingController::class,
            'controller_logo'            => LogoController::class,
            'controller_member'          => MemberController::class,
            'controller_member_download' => MemberDownloadReportController::class,
            'controller_member_view'     => MemberViewReportController::class,
        ];

        // Bước 3.2: Duyệt qua từng phần tử, nếu cấu hình được bật (!empty) thì khởi tạo đối tượng Controller để gắn hook admin_menu
        foreach ($adminControllers as $optionKey => $controllerClass) {
            if (!empty($options[$optionKey])) {
                new $controllerClass();
            }
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khai báo tập trung định nghĩa (metadata/columns/supports) cho toàn bộ Custom Post Types
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefinitions(): array
    {
        return [
            // Cấu hình CPT Active (Hoạt động / Sự kiện - bật show_in_rest và exclude_from_search = false để hỗ trợ Divi Visual Builder)
            'controller_active' => [
                'post_type'           => 'active',
                'name'                => 'Active',
                'singular_name'       => 'Active',
                'menu_name'           => 'Active',
                'exclude_from_search' => false,
                'has_archive'         => true,
                'menu_position'       => 6,
                'supports'            => ['thumbnail', 'editor', 'title', 'comments'],
                'show_in_rest'        => true,
            ],

            // Cấu hình CPT Case Studies (Câu chuyện thành công)
            'controller_case_study' => [
                'post_type'     => 'casestudies',
                'name'          => 'Case Studies',
                'singular_name' => 'Case Studies',
                'menu_name'     => 'Case Studies',
                'has_archive'   => true,
                'menu_position' => 8,
                'supports'      => ['editor', 'thumbnail', 'title', 'comments'],
            ],

            // Cấu hình CPT Industries (Ngành nghề - chỉ hiển thị cột Show Order và Create Date trong bảng quản trị)
            'controller_industries' => [
                'post_type'      => 'industries',
                'name'           => 'Industries',
                'singular_name'  => 'Industries',
                'menu_name'      => 'Industries',
                'has_archive'    => false,
                'menu_position'  => 5,
                'supports'       => ['thumbnail', 'editor', 'title'],
                'unset_columns'  => ['date', 'categories', 'comments'],
                'custom_columns' => [
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],

            // Cấu hình CPT Join Digiwin (Tuyển dụng & Đối tác - tùy biến hiển thị cột Language, Show Order, Create Date)
            'controller_join_us' => [
                'post_type'      => 'joinus',
                'name'           => 'Join Digiwin',
                'singular_name'  => 'Join Digiwin',
                'menu_name'      => 'Join Digiwin',
                'has_archive'    => true,
                'menu_position'  => 4,
                'supports'       => ['title', 'thumbnail', 'editor'],
                'custom_columns' => [
                    'language'    => 'Language',
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],

            // Cấu hình CPT Resources (Tài nguyên / Bài viết chuyên sâu)
            'controller_resources' => [
                'post_type'     => 'resources',
                'name'          => 'Resources',
                'singular_name' => 'Resources',
                'menu_name'     => 'Resources',
                'has_archive'   => true,
                'menu_position' => 8,
                'supports'      => ['thumbnail', 'editor', 'title', 'comments'],
            ],

            // Cấu hình CPT Services (Dịch vụ - hỗ trợ sắp xếp cột theo cả 'order' và '_metabox_order')
            'controller_services' => [
                'post_type'        => 'services',
                'name'             => 'Services',
                'singular_name'    => 'Services',
                'menu_name'        => 'Services',
                'has_archive'      => false,
                'menu_position'    => 7,
                'supports'         => ['thumbnail', 'editor', 'title'],
                'order_query_keys' => ['order', '_metabox_order'],
            ],

            // Cấu hình CPT Solutions (Giải pháp)
            'controller_solutions' => [
                'post_type'     => 'solutions',
                'name'          => 'Solutions',
                'singular_name' => 'Solutions',
                'menu_name'     => 'Solutions',
                'has_archive'   => true,
                'menu_position' => 4,
                'supports'      => ['title', 'thumbnail', 'editor', 'comments'],
            ],

            // Cấu hình CPT Slider (Banner trượt trang chủ - dùng name_suffix ghi chú kích thước chuẩn 1300 x 430)
            // [2026-11-25] - @author: Kelvin - Bổ sung cột 'img' hiển thị ảnh banner trong danh sách Slider WP-Admin
            'controller_slider' => [
                'post_type'      => 'slider',
                'name'           => 'Slider',
                'name_suffix'    => ' 1300 x 430',
                'singular_name'  => 'Slider',
                'menu_name'      => 'Slider',
                'has_archive'    => true,
                'menu_position'  => 6,
                'supports'       => ['title', 'thumbnail'],
                'unset_columns'  => ['home', 'categories', 'comments'],
                'custom_columns' => [
                    'img'         => 'Image',
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các Custom Post Type đang được bật thông qua lớp BasePostTypeController
     *
     * @param array<string, mixed> $options Mảng cấu hình bật/tắt từ main_controller_options
     */
    public static function registerEnabled(array $options): void
    {
        // Bước 2.1: Duyệt danh sách định nghĩa CPT từ getDefinitions()
        foreach (self::getDefinitions() as $optionKey => $config) {
            // Bước 2.2: Chỉ khởi tạo BasePostTypeController nếu phân hệ tương ứng đang bật (!empty)
            if (!empty($options[$optionKey])) {
                new BasePostTypeController((string) $config['post_type'], $config);
            }
        }
    }
}
