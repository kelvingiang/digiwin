<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Models\CustomPostModel;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler xử lý các endpoint load more bài viết (bk_load_more, load_more_post, load_more_in_group)
 */
class LoadMoreAjax
{
    /**
     * [18/11/2026] - Đăng ký các AJAX hooks load more
     */
    public static function register(): void
    {
        $actions = [
            'bk_load_more'       => 'handleBkLoadMore',
            'load_more_post'     => 'handleLoadMorePost',
            'load_more_in_group' => 'handleLoadMoreInGroup',
        ];

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [self::class, $method]);
            add_action('wp_ajax_nopriv_' . $action, [self::class, $method]);
        }
    }

    /**
     * [18/11/2026] - Load thêm bài viết theo category hỗ trợ Translation Group
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     * [25/11/2026] - Whitelist post_type đầu vào, kiểm tra taxonomy và giới hạn count/offset
     */
    public static function handleBkLoadMore(): void
    {
        $lastId      = max(0, isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0);
        $rawPostType = isset($_POST['post']) ? sanitize_text_field(wp_unslash((string) $_POST['post'])) : 'post';
        $postType    = CustomPostModel::resolveAllowedPostType($rawPostType);
        if ($postType === null) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid post type'], 400);
        }

        $cateId = isset($_POST['cateID']) ? (int) $_POST['cateID'] : 0;
        $count  = CustomPostModel::clampCount(isset($_POST['count']) ? (int) $_POST['count'] : 10, 10);
        $cate   = isset($_POST['cate']) ? sanitize_key(wp_unslash((string) $_POST['cate'])) : '';
        if ($cate !== '' && !taxonomy_exists($cate)) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid taxonomy'], 400);
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $lang = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : (string) ($_SESSION['languages'] ?? 'vn');

        $wpQuery  = CustomPostModel::get_load_more_posts($postType, $cateId, $cate, $count, $lastId, $lang);
        $html     = self::renderQueryTemplates($wpQuery, $lastId, 'templates/post-item');
        $response = $html !== '' ? ['status' => 'done', 'html' => $html] : ['status' => 'empty'];

        wp_send_json($response);
    }

    /**
     * [18/11/2026] - Load thêm bài viết theo category_name
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     * [25/11/2026] - Whitelist post_type đầu vào, giới hạn count/offset và tái sử dụng View templates/post-item.php
     */
    public static function handleLoadMorePost(): void
    {
        $lastId      = max(0, isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0);
        $rawPostType = isset($_POST['post']) ? sanitize_text_field(wp_unslash((string) $_POST['post'])) : 'post';
        $postType    = CustomPostModel::resolveAllowedPostType($rawPostType);
        if ($postType === null) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid post type'], 400);
        }

        $count = CustomPostModel::clampCount(isset($_POST['count']) ? (int) $_POST['count'] : 10, 10);
        $cate  = isset($_POST['cate']) ? sanitize_text_field(wp_unslash((string) $_POST['cate'])) : '';

        $wpQuery  = CustomPostModel::get_load_more_by_category_name($postType, $cate, $count, $lastId);
        $html     = self::renderQueryTemplates($wpQuery, $lastId, 'templates/post-item');
        $response = $html !== '' ? ['status' => 'done', 'html' => $html] : ['status' => 'empty'];

        wp_send_json($response);
    }

    /**
     * [18/11/2026] - Load thêm bài viết trong cùng nhóm loại trừ bài hiện tại
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     * [25/11/2026] - Whitelist post_type (tham số cate), giới hạn offset và render qua View templates/post-group-item.php
     */
    public static function handleLoadMoreInGroup(): void
    {
        $lastId   = max(0, isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0);
        $rawCate  = isset($_POST['cate']) ? sanitize_text_field(wp_unslash((string) $_POST['cate'])) : 'post';
        $postType = CustomPostModel::resolveAllowedPostType($rawCate);
        if ($postType === null) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid post type'], 400);
        }

        $slug = isset($_POST['slug']) ? sanitize_title(wp_unslash((string) $_POST['slug'])) : '';

        $wpQuery  = CustomPostModel::get_load_more_in_group($postType, 5, $lastId + 1);
        $html     = self::renderQueryTemplates($wpQuery, $lastId, 'templates/post-group-item', $slug);
        $response = $html !== '' ? ['status' => 'done', 'html' => $html] : ['status' => 'empty'];

        wp_send_json($response);
    }

    /**
     * [25/11/2026] - Helper render danh sách bài viết qua template part chuẩn MVC (loại bỏ nối chuỗi HTML thủ công)
     */
    private static function renderQueryTemplates(WP_Query $wpQuery, int $lastId, string $templateSlug, string $excludeSlug = ''): string
    {
        if (!$wpQuery->have_posts()) {
            return '';
        }

        $html = '';
        $stt  = $lastId + 1;

        while ($wpQuery->have_posts()) {
            $wpQuery->the_post();
            if ($excludeSlug !== '' && get_post_field('post_name', get_the_ID()) === $excludeSlug) {
                continue;
            }

            ob_start();
            get_template_part($templateSlug, null, ['stt' => $stt]);
            $html .= (string) ob_get_clean();
            $stt++;
        }

        wp_reset_postdata();

        return $html;
    }
}
