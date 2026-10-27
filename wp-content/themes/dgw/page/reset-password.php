<?php
/**
 * Template Name: Reset Password Page
 * Description: Custom template for Reset Password Page
 * 
 * [27/10/2026] Redesign lại trang reset-password:
 * - Khối .dgw-activate-main cách top 100px theo yêu cầu, không căn giữa dọc màn hình.
 * - Giao diện Form đặt lại mật khẩu dạng Card hiện đại, bảo mật, UX mượt mà với tính năng ẩn/hiện mật khẩu.
 * - Hiển thị trạng thái Loading và xử lý Ajax bảo mật bằng Prepared Statements & Nonce.
 */

get_header();
get_template_part('templates/header');

// [27/10/2026] Sanitize và xác thực tham số token và email từ URL
$email = isset($_GET['email']) ? sanitize_email(wp_unslash($_GET['email'])) : '';
$token = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';

$is_valid      = false;
$error_message = '';
$user_id       = 0;

if ($email && $token) {
    require_once get_template_directory() . '/model/model-download-function.php';
    $model_download = new Model_Download_Function();
    $user           = $model_download->get_user_by_email($email);

    if ($user) {
        // [27/10/2026] Kiểm tra thời hạn hiệu lực của mã (24 giờ) và so sánh chuỗi hash bảo mật
        if (time() > (int)$user->expiry) {
            $error_message = __("The link has expired. Please request a new code", "dgw");
        } elseif (!hash_equals((string)$user->token, hash('sha256', $token))) {
            $error_message = __("Invalid verification code", "dgw");
        } else {
            $is_valid = true;
            $user_id  = (int)$user->ID;
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
        <!-- [27/10/2026] .dgw-activate-main được căn cách top 100px qua SCSS, loại bỏ căn giữa dọc -->
        <main class="dgw-activate-main">
            <?php if ($is_valid) : ?>
                <!-- [27/10/2026] Redesign Card Form Đặt lại mật khẩu hiện đại, trực quan -->
                <div class="reset-password-card reset-password-form">
                    <div class="reset-password-card__header">
                        <div class="reset-password-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <h1 class="reset-password-card__title"><?php echo __('Change Password', 'dgw'); ?></h1>
                        <p class="reset-password-card__subtitle">
                            <?php echo __('Enter your new password below to update your account.', 'dgw'); ?>
                        </p>
                    </div>

                    <form id="form-reset-password" onsubmit="return false;" novalidate>
                        <!-- Mật khẩu mới -->
                        <div class="reset-password-card__field">
                            <label class="reset-password-card__label" for="new-password">
                                <?php echo __('New Password', 'dgw'); ?> <span class="required">*</span>
                            </label>
                            <div class="reset-password-card__input-wrap">
                                <input type="password"
                                    id="new-password"
                                    name="new_password"
                                    class="reset-password-card__input"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    required />
                                <button type="button"
                                    class="reset-password-card__toggle-pw"
                                    data-target="new-password"
                                    title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>"
                                    aria-label="Toggle password visibility">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                            <span class="reset-password-card__hint">
                                <?php echo __('Minimum 6 characters', 'dgw'); ?>
                            </span>
                        </div>

                        <!-- Xác nhận mật khẩu mới -->
                        <div class="reset-password-card__field">
                            <label class="reset-password-card__label" for="confirm-password">
                                <?php echo __('Confirm Password', 'dgw'); ?> <span class="required">*</span>
                            </label>
                            <div class="reset-password-card__input-wrap">
                                <input type="password"
                                    id="confirm-password"
                                    name="confirm_password"
                                    class="reset-password-card__input"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    required />
                                <button type="button"
                                    class="reset-password-card__toggle-pw"
                                    data-target="confirm-password"
                                    title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>"
                                    aria-label="Toggle password visibility">
                                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Nút gửi & thông báo kết quả -->
                        <button type="submit" id="btn-reset-password" class="reset-password-card__submit btn-my-style">
                            <span class="reset-password-card__spinner"></span>
                            <span class="btn-text"><?php echo __('Change Password', 'dgw'); ?></span>
                        </button>

                        <div id="change-password-msg" class="reset-password-card__msg" role="alert"></div>
                    </form>
                </div>
            <?php else : ?>
                <!-- [27/10/2026] Hiển thị lỗi khi token không hợp lệ hoặc đã hết hạn với Card tinh tế -->
                <div class="dgw-error-box">
                    <div class="dgw-error-box__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <h3><?php echo __('Cannot Reset Password', 'dgw'); ?></h3>
                    <p><?php echo esc_html($error_message); ?></p>
                    <a href="<?php echo esc_url(home_url('/member')); ?>" class="btn-my-style btn-retry">
                        <?php echo __('Request a new code', 'dgw'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </main>

        <aside class="dgw-active-sidebar">
            <?php get_template_part('templates/side-cases'); ?>
            <?php get_template_part('templates/side-active'); ?>
        </aside>
    </div>
</div>

<script>
/**
 * [27/10/2026] Script xử lý tương tác UI và gửi Ajax đặt lại mật khẩu:
 * - Hỗ trợ ẩn/hiện mật khẩu (Password visibility toggle).
 * - Validate form phía client trước khi gửi request (kiểm tra độ dài, khớp mật khẩu).
 * - Quản lý trạng thái loading và tự động chuyển hướng khi cập nhật thành công.
 */
jQuery(document).ready(function($) {
    // 1. Toggle ẩn/hiện mật khẩu
    $(document).on('click', '.reset-password-card__toggle-pw', function(e) {
        e.preventDefault();
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        const $eyeOpen = $(this).find('.eye-open');
        const $eyeClosed = $(this).find('.eye-closed');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $eyeOpen.hide();
            $eyeClosed.show();
        } else {
            $input.attr('type', 'password');
            $eyeOpen.show();
            $eyeClosed.hide();
        }
    });

    // 2. Xử lý submit form đặt lại mật khẩu
    const $form = $('#form-reset-password');
    const $btn = $('#btn-reset-password');
    const $card = $('.reset-password-card');
    const $msg = $('#change-password-msg');

    function showMessage(text, isSuccess) {
        $msg.removeClass('reset-password-card__msg--success reset-password-card__msg--error show')
            .addClass('show ' + (isSuccess ? 'reset-password-card__msg--success' : 'reset-password-card__msg--error'))
            .text(text);
    }

    function hideMessage() {
        $msg.removeClass('show reset-password-card__msg--success reset-password-card__msg--error').text('');
    }

    $(document).on('click', '#btn-reset-password', function(e) {
        e.preventDefault();

        const password = $('#new-password').val().trim();
        const confirm  = $('#confirm-password').val().trim();
        const key      = <?php echo json_encode((string)$token); ?>;
        const email    = <?php echo json_encode((string)$email); ?>;
        const currentLang = document.documentElement.lang;
        const sendLang = (currentLang === 'zh-TW' || currentLang.indexOf('zh') === 0) ? 'cn' : 'vi';

        hideMessage();

        // Validate client-side
        if (!password || !confirm) {
            showMessage(sendLang === 'cn' ? '請填寫完整資訊' : 'Vui lòng điền đầy đủ mật khẩu mới và xác nhận mật khẩu', false);
            return;
        }

        if (password.length < 6) {
            showMessage(sendLang === 'cn' ? '密碼長度至少需要 6 個字元' : 'Mật khẩu phải có ít nhất 6 ký tự', false);
            return;
        }

        if (password !== confirm) {
            showMessage(sendLang === 'cn' ? '確認密碼不一致' : 'Mật khẩu xác nhận không khớp', false);
            return;
        }

        // Bật trạng thái loading
        $card.addClass('is-loading');
        $btn.prop('disabled', true);
        const originalBtnText = $btn.find('.btn-text').text();
        $btn.find('.btn-text').text(sendLang === 'cn' ? '處理中...' : 'Đang xử lý...');

        const ajaxUrl = (typeof MyAjax !== 'undefined' && MyAjax.ajax_url) ? MyAjax.ajax_url : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'member_reset_password',
                nonce: '<?php echo wp_create_nonce('member_auth_nonce'); ?>',
                password: password,
                confirm: confirm,
                key: key,
                email: email,
                lang: sendLang
            },
            success: function(res) {
                if (res && res.success) {
                    showMessage(res.data.message || (sendLang === 'cn' ? '密碼已成功更新' : 'Mật khẩu đã được cập nhật thành công'), true);
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(home_url('/member')); ?>';
                    }, 1500);
                } else {
                    $card.removeClass('is-loading');
                    $btn.prop('disabled', false).find('.btn-text').text(originalBtnText);
                    const errorMsg = (res && res.data && res.data.message) ? res.data.message : (sendLang === 'cn' ? '操作失敗，請稍後再試' : 'Thất bại, vui lòng thử lại sau');
                    showMessage(errorMsg, false);
                }
            },
            error: function() {
                $card.removeClass('is-loading');
                $btn.prop('disabled', false).find('.btn-text').text(originalBtnText);
                showMessage(sendLang === 'cn' ? '系統錯誤，請稍後再試' : 'Lỗi hệ thống, vui lòng thử lại sau', false);
            }
        });
    });
});
</script>

<?php
get_template_part('templates/footer');
get_footer();
?>