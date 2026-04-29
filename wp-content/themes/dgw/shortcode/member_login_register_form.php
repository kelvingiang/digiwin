<?php

function member_login_register_form()
{
    if (is_user_logged_in()) return 'Bạn đã đăng nhập.'; ?>

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
