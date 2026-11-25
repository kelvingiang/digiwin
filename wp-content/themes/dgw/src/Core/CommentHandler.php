<?php
declare(strict_types=1);

namespace DGW\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Xử lý giao diện bình luận tùy biến và xác thực captcha toán học
 */
class CommentHandler
{
    /**
     * [18/11/2026] - Đăng ký hook kiểm tra comment-reply và captcha bình luận
     */
    public static function register(): void
    {
        add_action('wp_footer', [self::class, 'checkCommentReplyScript']);
        add_filter('preprocess_comment', [self::class, 'validateMathCaptcha']);
    }

    /**
     * [18/11/2026] - Render một bình luận trong danh sách bình luận
     * [20/11/2026] - Tách HTML sang View templates/comment-item.php chuẩn MVC
     */
    public static function renderComment($comment, $args, $depth): void
    {
        $GLOBALS['comment'] = $comment;
        get_template_part('templates/comment-item', null, [
            'comment_args' => (array) $args,
            'depth'        => (int) $depth,
        ]);
    }

    /**
     * [18/11/2026] - Ghi chú trạng thái script comment-reply ở footer
     */
    public static function checkCommentReplyScript(): void
    {
        if (wp_script_is('comment-reply', 'enqueued')) {
            echo '<!-- comment-reply.js 已載入 -->';
        } else {
            echo '<!-- comment-reply.js 未載入 -->';
        }
    }

    /**
     * [18/11/2026] - Kiểm tra kết quả captcha toán học khi gửi bình luận
     */
    public static function validateMathCaptcha(array $comment): array
    {
        if (!isset($_POST['math_answer'])) {
            wp_die('請回答驗證題');
        }

        $userAns = (int) $_POST['math_answer'];
        $realAns = (int) ($_SESSION['comment_captcha_answer'] ?? 0);

        if ($userAns !== $realAns) {
            wp_die('計算題錯誤！請重新填寫。');
        }

        return $comment;
    }
}
