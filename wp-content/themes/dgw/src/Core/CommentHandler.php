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
     */
    public static function renderComment($comment, $args, $depth): void
    {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
            <div id="comment-<?php comment_ID(); ?>" class="comment-body">
                <div class="comment-author"><i class="fa-solid fa-user"></i> <?php comment_author(); ?></div>
                <div class="comment-date">
                    <?php echo esc_html(get_comment_date('d/m/Y')); ?>
                </div>
                <div class="comment-text">
                    <?php comment_text(); ?>
                </div>

                <div class="comment-reply">
                    <?php
                    comment_reply_link(array_merge((array) $args, [
                        'reply_text' => __('Reply', 'dgw'),
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'] ?? 5,
                        'class'      => 'comment-reply-link reply',
                    ]));
                    ?>
                </div>
            </div>
        </li>
        <?php
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
