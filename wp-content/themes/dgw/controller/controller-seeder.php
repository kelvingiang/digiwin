<?php
/**
 * [26/10/2026] - Controller đăng ký menu Admin bên trái "Tạo Data Test (Seeder)" và điều phối luồng MVC.
 * Đảm bảo kiểm tra quyền quản trị (manage_options) và bảo mật CSRF thông qua WordPress Nonce.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once(DIR_MODEL . 'model-seeder.php');

class Controller_Seeder
{
    /**
     * @var Model_Seeder
     */
    private $model;

    /**
     * [26/10/2026] - Khởi tạo Controller và đăng ký hook admin_menu
     */
    public function __construct()
    {
        $this->model = new Model_Seeder();
        add_action('admin_menu', array($this, 'registerAdminMenu'));
        add_action('admin_init', array($this, 'autoFixScheduledDummyPosts'));
    }

    /**
     * [26/10/2026] - Tự động chuyển các bài test bị kẹt ở trạng thái 'future' sang 'publish' ngay khi vào Admin
     */
    public function autoFixScheduledDummyPosts(): void
    {
        if (current_user_can('manage_options')) {
            $this->model->publishScheduledDummyPosts();
        }
    }

    /**
     * [26/10/2026] - Thêm menu "Tạo Data Test" vào thanh menu bên trái của WP-Admin
     */
    public function registerAdminMenu(): void
    {
        add_menu_page(
            __('Tạo Data Test (Seeder)', 'dgw'),
            __('Tạo Data Test', 'dgw'),
            'manage_options',
            'dgw_data_seeder',
            array($this, 'dispatchAction'),
            'dashicons-database-add',
            99
        );
    }

    /**
     * [26/10/2026] - Điều hướng xử lý form Tạo dữ liệu mẫu, Xóa dữ liệu mẫu và render View
     */
    public function dispatchAction(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bạn không có quyền truy cập trang này.', 'dgw'));
        }

        $notice_message = '';
        $notice_type    = 'success';

        // [26/10/2026] - Xử lý submit tạo dữ liệu mẫu
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dgw_seeder_action'])) {
            check_admin_referer('dgw_seeder_nonce_action', 'dgw_seeder_nonce');

            $action_type = sanitize_text_field(wp_unslash($_POST['dgw_seeder_action']));

            if ($action_type === 'generate') {
                $params = array(
                    'count'            => isset($_POST['seeder_count']) ? (int) $_POST['seeder_count'] : 10,
                    'post_type'        => isset($_POST['seeder_post_type']) ? sanitize_key(wp_unslash($_POST['seeder_post_type'])) : 'post',
                    'custom_post_type' => isset($_POST['seeder_custom_post_type']) ? sanitize_key(wp_unslash($_POST['seeder_custom_post_type'])) : '',
                    'taxonomy'         => isset($_POST['seeder_taxonomy']) ? sanitize_key(wp_unslash($_POST['seeder_taxonomy'])) : '',
                    'custom_taxonomy'  => isset($_POST['seeder_custom_taxonomy']) ? sanitize_key(wp_unslash($_POST['seeder_custom_taxonomy'])) : '',
                    'term_mode'        => isset($_POST['seeder_term_mode']) ? sanitize_text_field(wp_unslash($_POST['seeder_term_mode'])) : 'existing',
                    'term_ids'         => isset($_POST['seeder_term_ids']) && is_array($_POST['seeder_term_ids'])
                        ? array_map('intval', wp_unslash($_POST['seeder_term_ids']))
                        : array(),
                    'custom_terms'     => isset($_POST['seeder_custom_terms']) ? sanitize_text_field(wp_unslash($_POST['seeder_custom_terms'])) : '',
                    'lang_mode'        => isset($_POST['seeder_lang_mode']) ? sanitize_text_field(wp_unslash($_POST['seeder_lang_mode'])) : 'vn',
                    'title_prefix'     => isset($_POST['seeder_title_prefix']) ? sanitize_text_field(wp_unslash($_POST['seeder_title_prefix'])) : '',
                    'custom_content'   => isset($_POST['seeder_custom_content']) ? wp_kses_post(wp_unslash($_POST['seeder_custom_content'])) : '',
                    'show_home'        => !empty($_POST['seeder_show_home']),
                    'with_thumbnail'   => !empty($_POST['seeder_with_thumbnail']),
                );

                $result = $this->model->generateDummyPosts($params);
                $notice_message = sprintf(
                    'Đã tạo thành công %d dòng dữ liệu test cho Post Type "%s"%s!',
                    (int) $result['inserted_count'],
                    esc_html($result['post_type']),
                    !empty($result['taxonomy']) ? ' (Taxonomy: ' . esc_html($result['taxonomy']) . ')' : ''
                );
            } elseif ($action_type === 'cleanup') {
                $target_pt = isset($_POST['cleanup_post_type'])
                    ? sanitize_text_field(wp_unslash($_POST['cleanup_post_type']))
                    : 'all';
                $deleted_count = $this->model->deleteDummyPosts($target_pt);
                $notice_message = sprintf(
                    'Đã dọn dẹp và xóa vĩnh viễn %d bài viết test (%s).',
                    (int) $deleted_count,
                    $target_pt === 'all' ? 'Tất cả Post Type' : esc_html($target_pt)
                );
            }
        }

        // [26/10/2026] - Chuẩn bị dữ liệu truyền xuống tầng View
        $post_types_map = $this->model->getPostTypesWithTaxonomies();
        $dummy_stats    = $this->model->getDummyCountsByPostType();

        require_once(DIR_VIEW . 'view-seeder.php');
    }
}
