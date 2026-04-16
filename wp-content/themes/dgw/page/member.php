<?php /*  Template Name: Member Page */ ?>
<?php get_header();
get_template_part('templates/template', 'header');
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}

?>


<div class="member-space">

    <!-- 未登入 -->
    <div id="ui-login-form" style="display:none">

        <div id="tab-buttons">
            <button class="tab-btn active" data-tab="login"><?php _e('Login', 'dgw') ?></button>
            <button class="tab-btn" data-tab="register"><?php _e('Register', 'dgw') ?></button>
        </div>

        <div id="tab-login" class="tab-content" data-page="member">
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

    <!-- 已登入 -->
    <div id="ui-logged-in" style="display:none; margin-top: 200px;">
        <div>
            <label><?php _e('E-mail', 'dgw'); ?></label>
            <label><?php echo $data->email; ?></label>
        </div>
        <div>
            <label><?php _e('Password', 'dgw'); ?></label>
            <input type="text" id="chang-password" />
        </div>
        <div>
            <label><?php _e('Confirm Password', 'dgw'); ?></label>
            <input type="text" id="chang-confirm-password" />
        </div>
        <div>
            <button id="btn-change-password"><?php _e('Change Password', 'dgw'); ?></button>
            <p id="change-password-msg" class="msg"></p>
        </div>
        <div>
            <button id="btn-logout">登出</button>
        </div>
        <hr>

        <div class="member-row">
            <label><?php _e('User Name', 'dgw') ?></label>
            <input type="text" id="chang-username" value="<?php echo $data->username; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Company', 'dgw') ?></label>
            <input type="text" id="chang-company" value="<?php echo $data->company; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Phone', 'dgw') ?></label>
            <input type="text" id="chang-phone" value="<?php echo $data->phone; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Industry', 'dgw') ?></label>
            <input type="text" id="chang-industry" value="<?php echo $data->industry; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Department', 'dgw') ?></label>
            <input type="text" id="chang-department" value="<?php echo $data->department; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Position', 'dgw') ?></label>
            <input type="text" id="chang-position" value="<?php echo $data->position; ?>" />
        </div>
        <div class="member-row">
            <label><?php _e('Tax', 'dgw') ?></label>
            <input type="text" id="chang-tax" value="<?php echo $data->tax; ?>" />
        </div>
        <div>
            <button id="btn-change-info"><?php _e('Change Information', 'dgw') ?></button>
            <p id="change-info-msg" class="msg"></p>
        </div>

    </div>


    <script>
        jQuery(document).on("click", "#btn-change-password", function(e) {
            e.preventDefault(); // 防止按鈕預設行為

            // 💡 建議至少要傳遞舊密碼與新密碼，確保安全性
            const Password = jQuery("#chang-password").val();
            const Confirm = jQuery("#chang-confirm-password").val();

            jQuery.ajax({
                url: MyAjax.ajax_url,
                type: "POST",
                data: {
                    action: "member_change_password",
                    nonce: MemberAuth.nonce,
                    password: Password, // 傳送舊密碼
                    confirm: Confirm // 傳送新密碼
                },
                success: function(res) {
                    if (res.success) {
                        // 顯示密碼修改成功
                        jQuery("#change-password-msg")
                            .css("color", "green")
                            .text("密碼修改成功！");
                    } else {
                        // 顯示錯誤訊息 (例如密碼太短、舊密碼錯誤等)
                        jQuery("#change-password-msg")
                            .css("color", "red")
                            .text(res.data.message);
                    }
                }
            });
        });

        jQuery(document).on("click", "#btn-change-info", function(e) {
            e.preventDefault(); // 防止按鈕預設行為

            // 💡 建議至少要傳遞舊密碼與新密碼，確保安全性
            const Username = jQuery("#chang-username").val();
            const Company = jQuery("#chang-company").val();
            const Phone = jQuery("#chang-phone").val();
            const Industry = jQuery("#chang-industry").val();
            const Department = jQuery("#chang-department").val();
            const Position = jQuery("#chang-position").val();
            const Tax = jQuery("#chang-tax").val();

            jQuery.ajax({
                url: MyAjax.ajax_url,
                type: "POST",
                data: {
                    action: "member_change_info",
                    nonce: MemberAuth.nonce,
                    username: Username,
                    company: Company,
                    phone: Phone,
                    industry: Industry,
                    department: Department,
                    position: Position,     
                    tax: Tax
                },
                success: function(res) {
                    if (res.success) {
                        // 顯示資訊修改成功
                        jQuery("#change-info-msg")
                            .css("color", "green")
                            .text("資訊修改成功！");
                    } else {
                        // 顯示錯誤訊息 (例如密碼太短、舊密碼錯誤等)
                        jQuery("#change-info-msg")
                            .css("color", "red")
                            .text(res.data.message);
                    }
                }
            });
        });
    </script>


    <?php
    get_template_part('templates/template', 'footer');
    get_footer(); ?>