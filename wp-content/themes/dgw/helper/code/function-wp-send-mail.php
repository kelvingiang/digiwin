<?php 
ob_start();

/**
 * 使用 phpmailer_init 動作鉤子來配置 SMTP
 * 所有的 wp_mail() 都將透過這些 SMTP 設置發送
 */
add_action('phpmailer_init', 'custom_phpmailer_smtp_config');
function custom_phpmailer_smtp_config($phpmailer)
{
    if (!defined('SMTP_HOST') || !SMTP_HOST) return;

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

// khi có comment post sẽ send mail đển cái user có quyết quản lý  ==============================================================================
add_action('comment_post', function ($comment_id, $comment_approved) {
    
    // 记录日志
    error_log("评论提交钩子触发 - 评论ID: $comment_id, 状态: $comment_approved");
    
    if ($comment_approved != 1) {
        error_log("评论未批准，跳过邮件发送");
        return;
    }

    $comment = get_comment($comment_id);
    $post    = get_post($comment->comment_post_ID);
    
    if (!$comment || !$post) {
        error_log("无法获取评论或文章数据");
        return;
    }

    $admins = get_users([
        'role'   => 'administrator',
        'fields' => ['user_email']
    ]);

    if (empty($admins)) {
        error_log("未找到管理员邮箱");
        return;
    }

    $emails = wp_list_pluck($admins, 'user_email');
    error_log("准备发送邮件给管理员: " . implode(', ', $emails));

    $subject = '網站有新的留言';
    $message  = "文章標題: " . get_the_title($post->ID) . "\n";
    $message .= "留言作者: " . $comment->comment_author . "\n";
    $message .= "留言內容:\n" . $comment->comment_content . "\n\n";
    $message .= "檢視留言: " . get_comment_link($comment_id) . "\n";

    // 添加邮件头
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>'
    );

    $result = wp_mail($emails, $subject, $message, $headers);
    
    if ($result) {
        error_log("评论通知邮件发送成功");
    } else {
        error_log("评论通知邮件发送失败");
        global $phpmailer;
        if (isset($phpmailer->ErrorInfo)) {
            error_log("PHPMailer 错误: " . $phpmailer->ErrorInfo);
        }
    }

}, 10, 2);


// test thiết lập SMTP có thành công chưa ============================================

add_action('init', function() {
    // http://localhost/digiwin/?testmail=1 link test
    if (isset($_GET['testmail']) && current_user_can('manage_options')) {
        
        // 设置邮件头确保发件人正确
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>'
        );
        
        $result = wp_mail(
            'giaminh0265@gmail.com', 
            'Test SMTP - ' . date('Y-m-d H:i:s'), 
            '
            <h2>SMTP 测试邮件</h2>
            <p>发送时间: ' . date('Y-m-d H:i:s') . '</p>
            <p>网站: ' . home_url() . '</p>
            <p>如果收到此邮件，说明 SMTP 配置成功！</p>
            ',
            $headers
        );
        
        if ($result) {
            echo "<h3>✅ 邮件发送成功！</h3>";
            echo "<p>请检查收件箱（包括垃圾邮件文件夹）</p>";
        } else {
            echo "<h3>❌ 邮件发送失败</h3>";
            
            global $phpmailer;
            if (isset($phpmailer)) {
                echo "<pre>PHPMailer 错误信息: ";
                var_dump($phpmailer->ErrorInfo);
                echo "</pre>";
            }
        }
        exit;
    }
});