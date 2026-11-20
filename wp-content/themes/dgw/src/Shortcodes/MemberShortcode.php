<?php
declare(strict_types=1);

namespace DGW\Shortcodes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Shortcodes xác thực thành viên (Quên mật khẩu, Đăng nhập/Đăng ký popup)
 */
class MemberShortcode
{
    /**
     * [18/11/2026] - Đăng ký các shortcodes thành viên với WordPress
     */
    public static function register(): void
    {
        add_shortcode('member_forgot_pw', [self::class, 'forgotPasswordForm']);
        add_shortcode('member_login_register', [self::class, 'loginRegisterForm']);
    }

    /**
     * [18/11/2026] - Render form popup quên mật khẩu
     */
    public static function forgotPasswordForm(): string
    {
        ob_start();
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
        <?php
        return (string) ob_get_clean();
    }

    /**
     * [18/11/2026] - Render popup đăng nhập và đăng ký thành viên
     */
    public static function loginRegisterForm(): string
    {
        ob_start();
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
                    <?php if (function_exists('dgw_render_auth_form_full')) {
                        dgw_render_auth_form_full();
                    } ?>
                </div>
            </div>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
