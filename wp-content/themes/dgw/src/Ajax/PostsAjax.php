<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Models\CustomPostModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler tải thêm bài viết, tăng lượt xem, tăng lượt thích và kiểm tra captcha toán học
 */
class PostsAjax
{
    /**
     * [18/11/2026] - Đăng ký AJAX hooks bài viết và tương tác
     */
    public static function register(): void
    {
        add_action('init', [self::class, 'ensureAjaxSession']);

        $actions = [
            'load_more_posts'    => 'handleLoadMorePosts',
            'plus_one_view'      => 'handlePlusOneView',
            'plus_one_like'      => 'handlePlusOneLike',
            'check_math_captcha' => 'handleCheckMathCaptcha',
        ];

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [self::class, $method]);
            add_action('wp_ajax_nopriv_' . $action, [self::class, $method]);
        }
    }

    /**
     * [18/11/2026] - Đảm bảo session sẵn sàng cho AJAX nếu header chưa flush
     */
    public static function ensureAjaxSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * [18/11/2026] - Load thêm bài viết hỗ trợ Translation Group
     * [20/11/2026] - Chuyển truy vấn sang CustomPostModel và render qua View templates/post-item.php chuẩn MVC & LCP
     */
    public static function handleLoadMorePosts(): void
    {
        $lastId  = isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0;
        $post    = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
        $rawCate = isset($_POST['cateID']) ? sanitize_text_field(wp_unslash($_POST['cateID'])) : '';
        $cateId  = is_numeric($rawCate) ? (int) $rawCate : $rawCate;
        $count   = isset($_POST['count']) ? (int) $_POST['count'] : 5;
        $cate    = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

        $wpQuery = CustomPostModel::get_load_more_posts($post, $cateId, $cate, $count, $lastId);

        if ($wpQuery->have_posts()) {
            $html = '';
            $stt  = $lastId + 1;

            while ($wpQuery->have_posts()) {
                $wpQuery->the_post();
                ob_start();
                get_template_part('templates/post-item', null, ['stt' => $stt]);
                $html .= (string) ob_get_clean();
                $stt++;
            }

            wp_reset_postdata();

            wp_send_json([
                'status' => 'done',
                'html'   => $html,
            ]);
        } else {
            wp_send_json(['status' => 'empty']);
        }

        wp_die();
    }

    /**
     * [18/11/2026] - Tăng lượt xem bài viết
     */
    public static function handlePlusOneView(): void
    {
        $postId = isset($_POST['postID']) ? (int) $_POST['postID'] : 0;
        $view   = (int) get_post_meta($postId, '_metabox_view', true);
        update_post_meta($postId, '_metabox_view', $view + 1);
        wp_send_json([
            'status' => 'done',
            'html'   => $view + 1,
        ]);
    }

    /**
     * [18/11/2026] - Tăng lượt thích bài viết
     */
    public static function handlePlusOneLike(): void
    {
        $postId = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
        if ($postId <= 0 || !get_post_status($postId)) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid Post ID']);
            wp_die();
        }

        $currentLikes = (int) get_post_meta($postId, '_metabox_like', true);
        $newLikes     = $currentLikes + 1;
        update_post_meta($postId, '_metabox_like', $newLikes);

        wp_send_json_success([
            'status' => 'done',
            'count'  => $newLikes,
            'html'   => $newLikes,
        ]);
        wp_die();
    }

    /**
     * [18/11/2026] - Kiểm tra captcha toán học qua AJAX
     * [25/11/2026] - Chặn bypass null === null và yêu cầu dữ liệu số hợp lệ từ client/session
     */
    public static function handleCheckMathCaptcha(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $rawAnswer = isset($_POST['answer']) ? trim((string) wp_unslash($_POST['answer'])) : '';
        $userAns   = ($rawAnswer !== '' && is_numeric($rawAnswer)) ? (int) $rawAnswer : null;
        $realAns   = isset($_SESSION['comment_captcha_answer']) ? (int) $_SESSION['comment_captcha_answer'] : null;

        if ($userAns !== null && $realAns !== null && $userAns === $realAns) {
            wp_send_json(['status' => 'ok']);
        }

        wp_send_json(['status' => 'fail']);
    }
}
