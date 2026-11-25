<?php
declare(strict_types=1);

/**
 * [20/11/2026] - View Template: Popup đăng nhập & đăng ký thành viên (tách HTML khỏi MemberShortcode chuẩn MVC)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="auth-popup-overlay">
    <div id="auth-popup-box">
        <button type="button" id="auth-popup-close" aria-label="<?php esc_attr_e('Close', 'dgw'); ?>">✕</button>

        <div class="popup-logo"></div>

        <div id="tab-buttons">
            <button class="tab-btn active" data-tab="login"><?php esc_html_e('Login', 'dgw'); ?></button>
            <button class="tab-btn" data-tab="register"><?php esc_html_e('Register', 'dgw'); ?></button>
        </div>

        <div id="tab-login" class="tab-content tab-login">
            <div class="one-columns">
                <div class="row-cell">
                    <label><?php esc_html_e('E-mail', 'dgw'); ?></label>
                    <input type="email" id="login-email" placeholder="example@email.com" />
                </div>
            </div>
            <div class="one-columns">
                <div class="row-cell">
                    <label><?php esc_html_e('Password', 'dgw'); ?></label>
                    <input type="password" id="login-password" placeholder="••••••••" />
                </div>
            </div>

            <div class="btn-two-columns">
                <div class="btn-space">
                    <button id="btn-login" class="btn-my-style"><?php esc_html_e('Login', 'dgw'); ?></button>
                </div>
                <div class="btn-space">
                    <button id="btn-forget-password" class="btn-my-style btn-password"><?php esc_html_e('Forget Password', 'dgw'); ?></button>
                </div>
            </div>
            <p id="login-msg" class="msg"></p>
        </div>

        <div id="tab-register" class="tab-content tab-register" style="display:none">
            <?php
            if (function_exists('dgw_render_auth_form_full')) {
                dgw_render_auth_form_full();
            }
            ?>
        </div>
    </div>
</div>
