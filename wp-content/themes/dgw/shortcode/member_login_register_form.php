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

            <div id="tab-login" class="tab-content">
                <h3><?php _e('Login') ?></h3>
                <p class="popup-sub"><?php _e('Welcome back!') ?></p>
                <label><?php _e('E-mail') ?></label>
                <input type="email" id="login-email" placeholder="example@email.com" />
                <label><?php _e('Password', 'dgw') ?></label>
                <input type="password" id="login-password" placeholder="••••••••" />
                <div class="btn-space">
                    <button id="btn-login"><?php _e('Login', 'dgw') ?></button>
                    <button id="btn-forget-password"><?php _e('Forget Password', 'dgw') ?></button>
                </div>
                <p id="login-msg" class="msg"></p>
            </div>

            <div id="tab-register" class="tab-content tab-register" style="display:none">
                <div>
                    <h3><?php _e('Register a new account', 'dgw') ?></h3>
                    <p class="popup-sub"><?php _e('Register for free to download documents') ?></p>
                </div>

                <div class="register-row">
                    <div>
                        <label><?php _e('E-mail', 'dgw') ?></label>
                        <input type="email" id="reg-email" placeholder="example@email.com" />
                    </div>
                    <div>
                        <label><?php _e('Password', 'dgw') ?></label>
                        <input type="password" id="reg-password" placeholder="Tối thiểu 6 ký tự" />
                    </div>
                </div>

                <hr class="hr-style">

                <div>
                    <label><?php _e('Company', 'dgw') ?></label>
                    <input type="text" id="reg-company" />
                </div>
                <div class="register-row">
                    <div>
                        <label><?php _e('Full Name', 'dgw') ?>
                        </label>
                        <input type="text" id="reg-username" placeholder="Nguyễn Văn A" />
                    </div>
                    <div>
                        <label><?php _e('Position', 'dgw') ?></label>
                        <input type="text" id="reg-position" />
                    </div>
                </div>
                <div class="register-row">
                    <div>
                        <label><?php _e('Phone', 'dgw') ?></label>
                        <input type="text" id="reg-phone" />
                    </div>
                    <div>
                        <label><?php _e('Tax Number', 'dgw') ?></label>
                        <input type="text" id="reg-tax" />
                    </div>
                </div>

                <div class="register-row">
                    <div>
                        <label><?php _e('Industry', 'dgw') ?></label>
                        <input type="text" id="reg-industry" />
                    </div>

                    <div>
                        <label><?php _e('Department', 'dgw') ?></label>
                        <input type="text" id="reg-department" />
                    </div>
                </div>
                <button id="btn-register"><?php _e('Register', 'dgw') ?> </button>
                <p id="register-msg" class="msg"></p>
            </div>
        </div>
    </div>
    <?php 

}

add_shortcode('member_login_register', 'member_login_register_form');