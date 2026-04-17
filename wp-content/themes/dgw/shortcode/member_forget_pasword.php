<?php
function member_forgot_password_form() {
    if ( is_user_logged_in() ) return '';

    ob_start(); ?>
<div id="popup-forgot-password" class="dwf-wrapper" style="display:none;">
    <div class="dwf-container">
        <span class="dwf-close">&times;</span>
        <div class="dwf-header">
            <h3>Quên mật khẩu</h3>
            <p>Nhập email của bạn để nhận liên kết đặt lại mật khẩu:</p>
        </div>
        <form id="forgot_password_form_ajax" class="dwf-form">
            <div class="dwf-input-group">
                <input type="email" name="user_email" id="user_email" required placeholder="email@example.com">
            </div>
            <?php wp_nonce_field( 'ajax_forgot_nonce', 'forgot_nonce' ); ?>
            <button type="submit" id="btn-submit-forgot" class="dwf-btn-submit">Gửi yêu cầu</button>
            <div id="forgot-password-msg" class="dwf-status-msg"></div>
        </form>
    </div>
</div>>
    <?php
    return ob_get_clean();
}
add_shortcode('member_forgot_pw', 'member_forgot_password_form');