<?php
declare(strict_types=1);

namespace DGW\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Cấu hình SMTP cho PHPMailer và thông báo bình luận mới cho quản trị viên
 */
class Mailer
{
    /**
     * [18/11/2026] - Đăng ký hook SMTP, comment notification và endpoint test mail cho admin
     */
    public static function register(): void
    {
        add_action('phpmailer_init', [self::class, 'configureSmtp']);
        add_action('comment_post', [self::class, 'notifyAdminsOnComment'], 10, 2);
        add_action('init', [self::class, 'handleAdminMailTest']);
    }

    /**
     * [18/11/2026] - Thiết lập thông số SMTP từ các hằng số cấu hình
     */
    public static function configureSmtp($phpmailer): void
    {
        if (!defined('SMTP_HOST') || !SMTP_HOST) {
            return;
        }

        $phpmailer->isSMTP();
        $phpmailer->Host       = SMTP_HOST;
        $phpmailer->SMTPAuth   = SMTP_AUTH;
        $phpmailer->Port       = SMTP_PORT;
        $phpmailer->Username   = SMTP_USERNAME;
        $phpmailer->Password   = SMTP_PASSWORD;
        $phpmailer->SMTPSecure = SMTP_SECURE;

        if (defined('SMTP_FROM_EMAIL') && SMTP_FROM_EMAIL) {
            $phpmailer->From = SMTP_FROM_EMAIL;
        }

        if (defined('SMTP_FROM_NAME') && SMTP_FROM_NAME) {
            $phpmailer->FromName = SMTP_FROM_NAME;
        }
    }

    /**
     * [18/11/2026] - Gửi email cho quản trị viên khi có bình luận được duyệt
     */
    public static function notifyAdminsOnComment($commentId, $commentApproved): void
    {
        if ($commentApproved != 1) {
            return;
        }

        $comment = get_comment($commentId);
        $post    = $comment ? get_post($comment->comment_post_ID) : null;

        if (!$comment || !$post) {
            return;
        }

        $admins = get_users([
            'role'   => 'administrator',
            'fields' => ['user_email'],
        ]);

        if (empty($admins)) {
            return;
        }

        $emails  = wp_list_pluck($admins, 'user_email');
        $subject = '網站有新的留言';
        $message = "文章標題: " . get_the_title($post->ID) . "\n";
        $message .= "留言作者: " . $comment->comment_author . "\n";
        $message .= "留言內容:\n" . $comment->comment_content . "\n\n";
        $message .= "檢視留言: " . get_comment_link($commentId) . "\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        ];

        $result = wp_mail($emails, $subject, $message, $headers);
        if (!$result) {
            global $phpmailer;
            if (isset($phpmailer->ErrorInfo)) {
                error_log('PHPMailer Error: ' . $phpmailer->ErrorInfo);
            }
        }
    }

    /**
     * [18/11/2026] - Cho phép quản trị viên kiểm tra cấu hình SMTP qua ?testmail=1
     */
    public static function handleAdminMailTest(): void
    {
        if (isset($_GET['testmail']) && current_user_can('manage_options')) {
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
            ];

            $result = wp_mail(
                'giaminh0265@gmail.com',
                'Test SMTP - ' . date('Y-m-d H:i:s'),
                '<h2>SMTP 测试邮件</h2><p>发送时间: ' . date('Y-m-d H:i:s') . '</p><p>网站: ' . esc_url(home_url()) . '</p>',
                $headers
            );

            echo $result ? '<h3>✅ 邮件发送成功！</h3>' : '<h3>❌ 邮件发送失败</h3>';
            exit;
        }
    }
}
