<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Helpers\RequestHelper;
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
     * [25/11/2026] - Whitelist post_type đầu vào, kiểm tra taxonomy và giới hạn count/offset
     */
    public static function handleLoadMorePosts(): void
    {
        $lastId  = max(0, isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0);
        $rawPost = isset($_POST['post']) ? sanitize_text_field(wp_unslash((string) $_POST['post'])) : 'post';
        $post    = CustomPostModel::resolveAllowedPostType($rawPost);
        if ($post === null) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid post type'], 400);
        }

        $rawCate = isset($_POST['cateID']) ? sanitize_text_field(wp_unslash((string) $_POST['cateID'])) : '';
        $cateId  = is_numeric($rawCate) ? (int) $rawCate : $rawCate;
        $count   = CustomPostModel::clampCount(isset($_POST['count']) ? (int) $_POST['count'] : 5, 5);
        $cate    = isset($_POST['cate']) ? sanitize_key(wp_unslash((string) $_POST['cate'])) : '';
        if ($cate !== '' && !taxonomy_exists($cate)) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid taxonomy'], 400);
        }

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
     * [25/11/2026] - Kiểm tra Nonce, tính hợp lệ bài viết (publish) và áp dụng rate-limit theo IP/Session cho lượt xem & lượt thích
     */
    private static function validateEngagementAndCheckLimit(string $actionType, int $postId, int $ttlSeconds): bool
    {
        check_ajax_referer('my_nonce', 'nonce');

        if ($postId <= 0 || get_post_status($postId) !== 'publish') {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid Post ID'], 400);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionBucket = 'dgw_rl_' . $actionType;
            $lastHit       = isset($_SESSION[$sessionBucket][$postId]) ? (int) $_SESSION[$sessionBucket][$postId] : 0;
            if ($lastHit > 0 && (time() - $lastHit) < $ttlSeconds) {
                return true;
            }
        }

        $ipAddress    = sanitize_text_field(wp_unslash((string) ($_SERVER['REMOTE_ADDR'] ?? '')));
        $transientKey = 'dgw_rl_' . $actionType . '_' . md5($ipAddress . '|' . $postId);
        if (get_transient($transientKey) !== false) {
            return true;
        }

        set_transient($transientKey, 1, $ttlSeconds);
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['dgw_rl_' . $actionType][$postId] = time();
        }

        return false;
    }

    /**
     * [18/11/2026] - Tăng lượt xem bài viết
     * [25/11/2026] - Bổ sung kiểm tra Nonce, trạng thái publish, rate-limit 60s và gắn cookie dgw_viewed_ tránh đếm trùng khi chuyển trang
     */
    public static function handlePlusOneView(): void
    {
        $postId        = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
        $isRateLimited = self::validateEngagementAndCheckLimit('view', $postId, 60);

        $view = (int) get_post_meta($postId, '_metabox_view', true);
        if (!$isRateLimited) {
            $view++;
            update_post_meta($postId, '_metabox_view', $view);
            RequestHelper::setSecureCookie(
                'dgw_viewed_' . $postId,
                '1',
                time() + (3 * HOUR_IN_SECONDS),
                true,
                'Lax',
                (defined('COOKIEPATH') && COOKIEPATH) ? (string) COOKIEPATH : '/',
                defined('COOKIE_DOMAIN') ? (string) COOKIE_DOMAIN : ''
            );
        }

        wp_send_json([
            'status' => 'done',
            'html'   => $view,
        ]);
    }

    /**
     * [18/11/2026] - Tăng lượt thích bài viết
     * [25/11/2026] - Bổ sung kiểm tra Nonce, trạng thái publish và rate-limit 1h chống spam
     */
    public static function handlePlusOneLike(): void
    {
        $postId        = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
        $isRateLimited = self::validateEngagementAndCheckLimit('like', $postId, HOUR_IN_SECONDS);

        $currentLikes = (int) get_post_meta($postId, '_metabox_like', true);
        $newLikes     = $currentLikes;
        if (!$isRateLimited) {
            $newLikes = $currentLikes + 1;
            update_post_meta($postId, '_metabox_like', $newLikes);
        }

        wp_send_json_success([
            'status' => 'done',
            'count'  => $newLikes,
            'html'   => $newLikes,
        ]);
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
