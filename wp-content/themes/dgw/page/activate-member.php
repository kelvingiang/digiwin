<?php /* Template Name: Activate Member Page */ ?>
<?php
get_header();
get_template_part('templates/template', 'header');

// Sử dụng filter_input để đảm bảo an toàn dữ liệu đầu vào (PHP 8.2)
$email = sanitize_email(wp_unslash($_GET['email'] ?? ''));
$token = sanitize_key(wp_unslash($_GET['key'] ?? ''));

$is_valid      = false;
$error_message = '';
$user_data     = null;

if ($email && $token) {
    // Chỉ nạp model khi cần thiết để tối ưu bộ nhớ
    require_once get_template_directory() . '/model/model-download-function.php';
    $model_download = new Model_Download_Function();
    $user_data      = $model_download->get_user_by_email($email);
    // Debug: Hiển thị token gốc và hashed_token để kiểm tra
    if ($user_data) {
        $db_code      = trim((string)$user_data->active_code);
        $hashed_token = hash('sha256', $token);

        // hash_equals chống Timing Attack
        if (hash_equals($db_code, $hashed_token)) {
            $is_valid = true;
        } else {
            $error_message = __('Invalid verification code', 'dgw');
        }
    } else {
        $error_message = __('Account does not exist', 'dgw');
    }
} else {
    $error_message = __('Invalid request', 'dgw');
}
?>

<div class="container-fluid">
    <div class="dgw-activate">
        <main class="dgw-activate-main">
            <?php if ($is_valid && $user_data && (int)$user_data->status === 0) : ?>
                <!-- Truyền dữ liệu qua data-attributes để tránh lỗi Syntax JS -->
                <div id="dgw-auto-activate-trigger"
                    data-params="<?php echo esc_attr(json_encode([
                                        'email'    => $email,
                                        'key'      => $token,
                                        'nonce'    => wp_create_nonce('member_auth_nonce'),
                                        'redirect' => home_url('/member/')
                                    ])); ?>">

                    <div class="dgw-loading-state">
                        <div class="dgw-loader"></div>
                        <h2 id="dgw-status-msg">
                            <?php _e('Account verification is in progress, please wait...', 'dgw'); ?>
                        </h2>
                    </div>
                </div>
            <?php else : ?>
                <?php if ($is_valid && $user_data && (int)$user_data->status !== 0) : ?>
                    <div class="dgw-status-box">
                        <p class="msg msg--info"><?php _e('This account has already been activated', 'dgw'); ?></p>
                        <a href="<?php echo esc_url(home_url('/member/')); ?>" class="btn-dgw">
                            <?php _e('Go to member page', 'dgw'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="dgw-error-box">
                        <h3><?php echo esc_html($error_message); ?></h3>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <aside class="dgw-active-sidebar">
            <?php get_template_part('templates/template', 'side_cases'); ?>
            <?php get_template_part('templates/template', 'side_active'); ?>
        </aside>
    </div>
</div>

<script>
    /**
     * Sử dụng ES6 và xử lý dữ liệu qua JSON để tránh lỗi Unexpected token
     */
    document.addEventListener('DOMContentLoaded', () => {
        const trigger = document.getElementById('dgw-auto-activate-trigger');
        const msgBox = document.getElementById('dgw-status-msg');
        const currentLang = document.documentElement.lang;
        const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

        if (!trigger || !msgBox) return;

        try {
            // Parse dữ liệu từ data-attribute
            const config = JSON.parse(trigger.dataset.params);

            const activateAccount = async () => {
                jQuery.ajax({
                    url: MyAjax.ajax_url,
                    type: "POST",
                    dataType: "json",
                    data: {
                        action: "member_active_account",
                        nonce: config.nonce,
                        key: config.key,
                        email: config.email,
                        lang: sendLang
                    },
                    success: (res) => {
                        if (res.success) {
                            msgBox.innerHTML = `<span class="txt-success"><?php _e('Account activated successfully!', 'dgw'); ?></span>`;
                            setTimeout(() => {
                                window.location.href = config.redirect;
                            }, 3000);
                        } else {
                            msgBox.innerHTML = `<span class="txt-error">${res.data || '<?php _e('Failed to activate account', 'dgw'); ?>'}</span>`;
                        }
                    },
                    error: () => {
                        msgBox.innerHTML = `<span class="txt-error"><?php _e('Server connection error', 'dgw'); ?></span>`;
                    }
                });
            };

            activateAccount();

        } catch (e) {
            console.error("DGW Activation Error:", e);
        }
    });
</script>

<?php
get_template_part('templates/template', 'footer');
get_footer();
?>