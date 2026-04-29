<?php /*  Template Name: Reset Password Page */ ?>
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
        // Kiểm tra thời gian và hash
        if (time() > $user->expiry) {
            $error_message = "Liên kết đã hết hạn (quá 24 giờ).";
        } elseif (!hash_equals($user->token, hash('sha256', $token))) {
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
            <div class="one-columns" >
                <div class="row-cell">
                    <label><?php _e('New Password') ?></label>
                    <input type="password"
                        id="new-password"
                        placeholder="***********"
                        required>
                    </input>
                </div>
            </div>

            <div class="one-columns">
                <div class="row-cell">
                    <label class="title"><?php _e('Confirm Password') ?></label>
                    <input type="password"
                        id="confirm-password"
                        placeholder="***********"
                        required>
                    </input>
                </div>
            </div>

            <div class="btn-space">
                <button id="btn-reset-password" class="btn-my-style">
                    <?php _e('Submit Email') ?>
                </button>
                <p id="change-password-msg" class="msg"></p>
            </div>

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
    jQuery(document).on("click", "#btn-reset-password", function(e) {
        e.preventDefault(); // 防止按鈕預設行為

        // 💡 建議至少要傳遞舊密碼與新密碼，確保安全性
        const Password = jQuery("#new-password").val();
        const Confirm = jQuery("#confirm-password").val();
        // Sửa lỗi ở đây: Dùng json_encode để an toàn và đúng tên biến PHP
        const key = <?php echo json_encode((string)$token); ?>;
        const email = <?php echo json_encode((string)$email); ?>;

        jQuery.ajax({
            url: MyAjax.ajax_url,
            type: "POST",
            data: {
                action: "member_reset_password",
                nonce: "<?php echo wp_create_nonce('member_auth_nonce'); ?>",
                password: Password, // 傳送舊密碼
                confirm: Confirm, // 傳送新密碼
                key: key, // 傳送 key
                email: email // 傳送 email
            },
            success: function(res) {
                if (res.success) {
                    // 顯示密碼修改成功
                    jQuery("#change-password-msg")
                        .css("color", "green")
                        .text("密碼修改成功！");
                    setTimeout(function() {
                        // Thay đổi URL dưới đây bằng link trang member của bạn
                        window.location.href = "<?php echo home_url('/member'); ?>";
                    }, 2000);
                } else {
                    // 顯示錯誤訊息 (例如密碼太短、舊密碼錯誤等)
                    jQuery("#change-password-msg")
                        .css("color", "red")
                        .text(res.data.message);
                }
            }
        });
    });
</script>
<?php
get_template_part('templates/template', 'footer');
get_footer(); ?>