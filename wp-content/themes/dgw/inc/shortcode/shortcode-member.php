<?php
// 29/06/2026: Gộp 2 file shortcode (member_forget_password_form và member_login_register_form) vào chung 1 file để tối ưu.

function member_forgot_password_form()
{
    ob_start(); ?>
    <div id="popup-forgot-password" class="dwf-wrapper" style="display:none;">
        <div class="dwf-container">
            <span class="dwf-close">&times;</span>
            <div class="dwf-header">
                <h3><?php _e('Forget Password') ?></h3>
                <p><?php _e('Enter your email address to reset your password') ?></p>
            </div>
            <form id="forgot_password_form_ajax" class="dwf-form">
                <div class="one-columns">
                    <div class="row-cell">
                        <input type="email" name="user_email" id="user_email" required placeholder="email@example.com">
                    </div>
                </div>

                <?php wp_nonce_field('ajax_forgot_nonce', 'forgot_nonce'); ?>
                <div class="btn-space">
                    <button type="submit" id="btn-submit-forgot" class="btn-my-style"><?php _e('Submit Email') ?></button>
                </div>
                <div id="forgot-password-msg" class="msg"></div>
            </form>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('member_forgot_pw', 'member_forgot_password_form');


function member_login_register_form()
{
   // if (is_user_logged_in()) return 'Bạn đã đăng nhập.'; ?>

    <div id="auth-popup-overlay">
        <div id="auth-popup-box">
            <button id="auth-popup-close">✕</button>

            <div class="popup-logo"></div>

            <div id="tab-buttons">
                <button class="tab-btn active" data-tab="login"><?php _e('Login', 'dgw') ?></button>
                <button class="tab-btn" data-tab="register"><?php _e('Register', 'dgw') ?></button>
            </div>

            <div id="tab-login" class="tab-content tab-login">
                <div class="one-columns">
                    <div class="row-cell">
                        <label><?php _e('E-mail') ?></label>
                        <input type="email" id="login-email" placeholder="example@email.com" />
                    </div>
                </div>
                <div class="one-columns">
                    <div class="row-cell">
                        <label><?php _e('Password', 'dgw') ?></label>
                        <input type="password" id="login-password" placeholder="••••••••" />
                    </div>
                </div>

                <div class="btn-two-columns">
                    <div class="btn-space">
                        <button id="btn-login" class="btn-my-style"><?php _e('Login', 'dgw') ?></button>
                    </div>
                    <div class="btn-space">
                        <button id="btn-forget-password" class="btn-my-style btn-password"><?php _e('Forget Password', 'dgw') ?></button>
                    </div>
                </div>
                <p id="login-msg" class="msg"></p>
            </div>

            <div id="tab-register" class="tab-content tab-register" style="display:none">
                <!-- PHAN DANG KY REGISTER================================================================= -->
                <?php dgw_render_auth_form_full() ?>
            </div>
        </div>
    </div>
<?php
}
add_shortcode('member_login_register', 'member_login_register_form');
