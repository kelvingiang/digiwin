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
     * [25/11/2026] - Chặn bypass khi thiếu session captcha, kiểm tra input số hợp lệ và hủy captcha sau khi dùng
     */
    public static function validateMathCaptcha(array $comment): array
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $rawAnswer = isset($_POST['math_answer']) ? trim((string) wp_unslash($_POST['math_answer'])) : '';
        if ($rawAnswer === '' || !is_numeric($rawAnswer)) {
            wp_die('請回答驗證題');
        }

        if (!isset($_SESSION['comment_captcha_answer'])) {
            wp_die('驗證碼已過期，請重新整理頁面。');
        }

        $userAns = (int) $rawAnswer;
        $realAns = (int) $_SESSION['comment_captcha_answer'];

        if ($userAns !== $realAns) {
            wp_die('計算題錯誤！請重新填寫。');
        }

        unset($_SESSION['comment_captcha_answer'], $_SESSION['comment_captcha_question']);

        return $comment;
    }
}
