<?php
/**
 * Date: 2026-08-24
 * Template Name: Reset Password Page
 * Description: Custom template for Reset Password Page
 */

use phpseclib3\File\ASN1\Maps\Time;

get_header();
get_template_part('templates/template', 'header');

$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
$token = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
// 2. Kiểm tra tính hợp lệ của Token ngay khi load trang
$is_valid = false;
$error_message = '';
$user_id = 0;


if ($email && $token) {

    require_once get_template_directory() . '/model/model-download-function.php';
    $model_download = new Model_Download_Function();
    $user = $model_download->get_user_by_email($email);

    if ($user) {
        // Kiểm tra thời gian và hash
        if (time() > $user->expiry) {
            $error_message = __("The link has expired. Please request a new code", "dgw");
        } elseif (!hash_equals($user->token,  hash('sha256', $token))) {
            $error_message = __("Invalid verification code", "dgw");
        } else {
            $is_valid = true;
            $user_id = $user->ID;
        }
    } else {
        $error_message = __("The user does not exist.", "dgw");
    }
} else {
    $error_message = __("Invalid request.", "dgw");
}
?>

<div class="container-fluid">
    <div class="dgw-activate">
        <main class="dgw-activate-main">
            <?php if ($is_valid) : ?>
                <div class="reset-password-form">
                    <div class="one-columns">
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
            <?php else : ?>
                <!-- TRƯỜNG HỢP 2: TOKEN SAI HOẶC HẾT HẠN -> ẨN FORM & HIỆN LỖI -->
                <div class="dgw-error-box">
                    <h3><?php _e('Cannot Reset Password', 'dgw'); ?></h3>
                    <p style="color: #c53030; font-weight: 500; margin: 0;">
                        <?php echo esc_html($error_message); ?>
                    </p>
                    <!-- Bạn có thể thêm 1 nút "Yêu cầu lại" dẫn về trang Quên mật khẩu ở đây -->
                    <a href="<?php echo home_url('/member'); ?>" class="btn-my-style"
                        style="margin-top: 38px; display: inline-block;">
                        <?php _e('Request a new code', 'dgw'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </main>
        <aside class="dgw-active-sidebar">
            <?php get_template_part('templates/template', 'side_cases'); ?>
            <?php get_template_part('templates/template', 'side_active'); ?>
        </aside>
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
        const currentLang = document.documentElement.lang;
        const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

        jQuery.ajax({
            url: MyAjax.ajax_url,
            type: "POST",
            data: {
                action: "member_reset_password",
                nonce: "<?php echo wp_create_nonce('member_auth_nonce'); ?>",
                password: Password, // 傳送舊密碼
                confirm: Confirm, // 傳送新密碼
                key: key, // 傳送 key
                email: email, // 傳送 email
                lang: sendLang
            },
            success: function(res) {
                if (res.success) {
                    // 顯示密碼修改成功
                    jQuery("#change-password-msg")
                        .css("color", "green")
                        .text(res.data.message);
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