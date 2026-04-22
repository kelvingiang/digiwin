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
    echo '<pre>';
    print_r($user);
    echo '</pre>';
    if ($user) {


        if (!hash_equals($user->token, hash('sha256', $token))) {
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
<div>
    <?php pageImg($post->ID); ?>
</div>
<div class="container-fluid">
    <div class="reset-password-page">
        <div class="reset-password-form">
    
            <div>
                <button id="btn-active-member" class="btn-resetpassword">Active Account</button>
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
                    jQuery("#change-password-msg")
                        .css("color", "green")
                        .text("密碼修改成功！");
                    setTimeout(function() {
                        // Thay đổi URL dưới đây bằng link trang member của bạn
                      //window.location.href = "<?php //echo home_url('/member'); ?>";
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