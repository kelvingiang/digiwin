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
                <div>
                    <h3><?php _e('Register a new account', 'dgw') ?></h3>
                    <p class="popup-sub"><?php _e('Register for free to download documents') ?></p>
                </div>

                <div class="two-columns">
                    <div class="row-cell">
                        <label><?php _e('E-mail', 'dgw') ?></label>
                        <input type="email" id="reg-email" placeholder="example@email.com" />
                    </div>
                    <div class="row-cell">
                        <label><?php _e('Password', 'dgw') ?></label>
                        <input type="password" id="reg-password" placeholder="Tối thiểu 6 ký tự" />
                    </div>
                </div>

                <hr class="hr-style">

                <div class="one-columns">
                    <div class="row-cell">
                        <label><?php _e('Company', 'dgw') ?></label>
                        <input type="text" id="reg-company" />
                    </div>
                </div>
                <div class="two-columns">
                    <div class="row-cell">
                        <label><?php _e('Full Name', 'dgw') ?></label>
                        <input type="text" id="reg-username" placeholder="Nguyễn Văn A" />
                    </div>
                    <div class="row-cell">
                        <label><?php _e('Position', 'dgw') ?></label>
                        <input type="text" id="reg-position" />
                    </div>
                </div>
                <div class="two-columns">
                    <div class="row-cell">
                        <label><?php _e('Phone', 'dgw') ?></label>
                        <input type="text" id="reg-phone" class="type-phone-more" maxlength="15" />
                    </div>
                    <div class="row-cell">
                        <label><?php _e('Tax Number', 'dgw') ?></label>
                        <input type="text" id="reg-tax" class="type-number" maxlength="13"/>
                    </div>
                </div>

                <div class="two-columns">
                    <div class="row-cell">
                        <label><?php _e('Industry', 'dgw') ?></label>
                        <input type="text" id="reg-industry" />
                    </div>

                    <div class="row-cell">
                        <label><?php _e('Department', 'dgw') ?></label>
                        <input type="text" id="reg-department" />
                    </div>
                </div>
                <div class="btn-space">
                    <button id="btn-register" class="btn-my-style"><?php _e('Register', 'dgw') ?> </button>
                    <p id="register-msg" class="msg"></p>
                </div>
            </div>
        </div>
    </div>
<?php

}

add_shortcode('member_login_register', 'member_login_register_form');
