<?php /*  Template Name: Active Member Page */ ?>
<?php get_header();
get_template_part('templates/template', 'header');

$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
$token = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
// 2. Kiểm tra tính hợp lệ của Token ngay khi load trang
$is_valid = false;
$error_message = '';
$user_id = 0;


if ($email && $token) {

    require_once get_template_directory() . '/model/model-download.php';
    $model_download = new Model_Download();
    $user = $model_download->get_user_by_email($email);

    if ($user) {
        if (!hash_equals($user->active_code,  $token)) {
            $error_message = "Mã xác thực không hợp lệ.";
        } else {
            $is_valid = true;
            $user_id = $user->ID;
        }
    } else {
        $error_message = "Người dùng không tồn tại.";
    }
} else {
    $error_message = "Yêu cầu không hợp lệ.";
}
?>
<div class="container-fluid">
    <div class="reset-password-page">
        <div class="reset-password-form">
            <?php if ($is_valid && isset($user) && $user->status == 0) : ?>
                <div>
                    <?php if ($user->status == 0): ?>
                        <button id="btn-active-member" class="btn-resetpassword">Active Account</button>
                        <label id="active-msg" class="msg"></label>
                    <?php else: ?>
                        <label class="msg" ><?php _e('tai khoang da dc kich hoat', 'dgw'); ?></label>
                    <?php endif ?>
                </div>
            <?php else: ?>
                <div>
                    <label class="msg-error"><?php echo esc_html($error_message);?></label>
                </div>
            <?php endif ?>
        </div>
        <div>
            <div class="">
                <?php get_template_part('templates/template', 'side_cases'); ?>
                <?php get_template_part('templates/template', 'side_active'); ?>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).on("click", "#btn-active-member", function(e) {
        e.preventDefault(); // 防止按鈕預設行為

        // Sửa lỗi ở đây: Dùng json_encode để an toàn và đúng tên biến PHP
        const key = <?php echo json_encode((string)$token); ?>;
        const email = <?php echo json_encode((string)$email); ?>;

        jQuery.ajax({
            url: MyAjax.ajax_url,
            type: "POST",
            data: {
                action: "member_active_account",
                nonce: "<?php echo wp_create_nonce('member_auth_nonce'); ?>",
                key: key, // 傳送 key
                email: email // 傳送 email
            },
            success: function(res) {
                if (res.success) {
                    // 顯示密碼修改成功
                    jQuery("#active-msg")
                        .css("color", "green")
                        .text("您的賬號已激活成功！即張轉到登入介面");
                    jQuery("#btn-active-member").prop("disabled", true);
                    setTimeout(function() {
                        // Thay đổi URL dưới đây bằng link trang member của bạn
                         window.location.href = "<?php echo home_url('/member'); ?>";
                    }, 1000);
                } else {
                    // 顯示錯誤訊息 (例如密碼太短、舊密碼錯誤等)
                    jQuery("#active-msg")
                        .css("color", "red")
                        .text('Có lỗi xảy ra, vui lòng thử lại.');
                }
            }
        });
    });
</script>
<?php
get_template_part('templates/template', 'footer');
get_footer(); ?>