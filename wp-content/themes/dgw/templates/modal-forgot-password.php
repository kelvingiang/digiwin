<?php
declare(strict_types=1);

/**
 * [20/11/2026] - View Template: Popup quên mật khẩu thành viên (tách HTML khỏi MemberShortcode chuẩn MVC)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="popup-forgot-password" class="dwf-wrapper" style="display:none;">
    <div class="dwf-container">
        <span class="dwf-close">&times;</span>
        <div class="dwf-header">
            <label class="forget-title"><?php esc_html_e('Forget Password', 'dgw'); ?></label>
            <p><?php esc_html_e('Enter your email address to reset your password', 'dgw'); ?></p>
        </div>
        <form id="forgot_password_form_ajax" class="dwf-form">
            <div class="one-columns">
                <div class="row-cell">
                    <input type="email" name="user_email" id="user_email" required placeholder="email@example.com">
                </div>
            </div>

            <?php wp_nonce_field('ajax_forgot_nonce', 'forgot_nonce'); ?>
            <div class="btn-space">
                <button type="submit" id="btn-submit-forgot" class="btn-my-style"><?php esc_html_e('Submit Email', 'dgw'); ?></button>
            </div>
            <div id="forgot-password-msg" class="msg"></div>
        </form>
    </div>
</div>
