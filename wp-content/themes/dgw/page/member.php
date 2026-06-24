<?php /*  Template Name: Member Page */ ?>
<?php get_header();
get_template_part('templates/template', 'header');

$data = null;
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}


// Sau đó test xem có đọc được không
// if (isset($_COOKIE['custom_session'])) {
//     echo "✅ Cookie read successfully!";
// } else {
//     echo "❌ Cookie not found!";
// }

?>
<div class="page-title-h1">
    <h1><?php echo __('member login and register') ?></h1> 
</div>

<div class="member-space">
    <!-- 未登入 -->
    <div id="ui-login-form" style="display:none">
        <div id="tab-buttons">
            <div class="tab-btn active" data-tab="login"><?php _e('Login', 'dgw') ?></div>
            <div class="tab-btn" data-tab="register"><?php _e('Register', 'dgw') ?></div>
        </div>

        <div id="tab-login" class="tab-content tab-login" data-page="member">

            <div class="one-columns">
                <div class="row-cell">
                    <label for="login-email" ><?php _e('E-mail') ?></label>
                    <input type="email" id="login-email" placeholder="example@email.com" />
                </div>
            </div>

            <div class="one-columns">
                <div class="row-cell">
                    <label for="login-password"><?php _e('Password', 'dgw') ?></label>
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

    <!-- 已登入 -->
    <div id="ui-logged-in" class="logged-in " style="display:none;">

        <div class="logout-space">
            <label for="btn-logout"><?php echo $data->email; ?></label>
            <button id="btn-logout" class="btn-logout">
                <?php _e('Logout', 'dgw') ?>
            </button>
        </div>
        <div class="three-columns">

            <div class="row-cell">
                <label for="current-password"><?php _e('Current Password', 'dgw'); ?></label>
                <input type="password" id="current-password" placeholder="********" />
            </div>

            <div class="row-cell">
                <label for="chang-password"><?php _e('New Password', 'dgw'); ?></label>
                <input type="password" id="chang-password" placeholder="********" />
            </div>

            <div class="row-cell">
                <label for="chang-confirm-password"><?php _e('Confirm Password', 'dgw'); ?></label>
                <input type="password" id="chang-confirm-password" placeholder="********" />
            </div>
        </div>

        <div class="btn-space">
            <button id="btn-change-password" class="btn-my-style"><?php _e('Change Password', 'dgw'); ?></button>
            <p id="change-password-msg" class="msg"></p>
        </div>

        <hr class="hr-style" style="margin-bottom: 2rem;" />

        <div class="three-columns">
            <div class="row-cell">
                <label for="chang-username"><?php _e('Full Name', 'dgw') ?></label>
                <input type="text" id="chang-username" value="<?php echo $data->username; ?>" />
            </div>
            <div class="row-cell">
                <label for="chang-position"><?php _e('Position', 'dgw') ?></label>
                <!-- <input type="text" id="chang-position" value="<?php echo $data->position; ?>" /> -->
                <select id="chang-position">
                    <option value=""><?php _e('Select Position', 'dgw') ?></option>
                    <?php
                    $positions = member_position_list();
                    foreach ($positions as $key => $value) {
                        $selected = ($data->position === $key) ? 'selected' : '';
                        echo "<option value='$key' $selected>$value</option>";
                    }
                    ?>
                </select>

            </div>
            <div class="row-cell">
                <label for="chang-phone"><?php _e('Phone', 'dgw') ?></label>
                <input type="text" id="chang-phone" class="type-phone-more" maxlength="15" value="<?php echo $data->phone; ?>" />
            </div>
        </div>

        <div class="two-columns">
            <div class="row-cell">
                <label for="chang-company"><?php _e('Company Name', 'dgw') ?></label>
                <input type="text" id="chang-company" value="<?php echo $data->company; ?>" />
            </div>
            <div class="row-cell">
                <label for="chang-tax"><?php _e('Tax Number', 'dgw') ?></label>
                <input type="text" id="chang-tax" class="type-number" maxlength="13" value="<?php echo $data->tax; ?>" />
            </div>
        </div>

        <div class="two-columns">
            <div class="row-cell">
                <label for="chang-industry"><?php _e('Industry', 'dgw') ?></label>
                <!-- <input type="text" id="chang-industry" value="<?php echo $data->industry; ?>" /> -->
                <select id="chang-industry">
                    <option value=""><?php _e('Select Industry', 'dgw') ?></option>
                    <?php
                    $industries = industry_sector_list();
                    foreach ($industries as $key => $value) {
                        $selected = ($data->industry === $key) ? 'selected' : '';
                        echo "<option value='$key' $selected>$value</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="row-cell">
                <label for="chang-department"><?php _e('Department', 'dgw') ?></label>
                <!-- <input type="text" id="chang-department" value="<?php echo $data->department; ?>" /> -->
                <select id="chang-department">
                    <option value=""><?php _e('Select Department', 'dgw') ?></option>
                    <?php
                    $departments = department_list();
                    foreach ($departments as $key => $value) {
                        $selected = ($data->department === $key) ? 'selected' : '';
                        echo "<option value='$key' $selected>$value</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="btn-space">
            <button id="btn-change-info" class="btn-my-style"><?php _e('Change Information', 'dgw') ?></button>
            <p id="change-info-msg" class="msg"></p>
        </div>

    </div>

    <script>
        jQuery(document).on("click", "#btn-change-password", function(e) {
            e.preventDefault(); // 防止按鈕預設行為

            // 💡 建議至少要傳遞舊密碼與新密碼，確保安全性
            const Current = jQuery("#current-password").val();
            const Password = jQuery("#chang-password").val();
            const Confirm = jQuery("#chang-confirm-password").val();
            const currentLang = document.documentElement.lang;
            const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

            jQuery.ajax({
                url: MyAjax.ajax_url,
                type: "POST",
                data: {
                    action: "member_change_password",
                    nonce: MemberAuth.nonce,
                    current: Current, // 傳送舊密碼
                    password: Password, // 傳送舊密碼
                    confirm: Confirm, // 傳送新密碼
                    lang: sendLang
                },
                success: function(res) {
                    if (res.success) {
                        // 顯示密碼修改成功
                        jQuery("#change-password-msg")
                            .css("color", "green")
                            .text(res.data.message);
                        // Tự động refresh trang sau 30 giây (30000 milliseconds)
                        setTimeout(function() {
                            jQuery("#change-password-msg").text('');
                            jQuery("#current-password").val('');
                            jQuery("#chang-password").val('');
                            jQuery("#chang-confirm-password").val('');
                        }, 5000);
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

            const currentLang = document.documentElement.lang;
            const sendLang = currentLang === "zh-TW" ? "cn" : "vn";

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
                    tax: Tax,
                    lang: sendLang
                },
                success: function(res) {
                    if (res.success) {
                        // 顯示資訊修改成功
                        jQuery("#change-info-msg")
                            .css("color", "green")
                            .text(res.data.message);
                        // Tự động refresh trang sau 30 giây (30000 milliseconds)
                        setTimeout(function() {
                            jQuery("#change-info-msg").text('');
                        }, 5000);
                    } else {
                        // 顯示錯誤訊息 (例如密碼太短、舊密碼錯誤等)
                        jQuery("#change-info-msg")
                            .css("color", "red")
                            .text(res.data.message);
                    }
                }
            });
        });

        jQuery(document).ready(function($) {
            // Hàm kiểm tra và đổi trạng thái nút
            function toggleSubmitButton() {
                // Tìm nút button nằm cạnh đó
                var $btn = $('.btn-space .btn-my-style');

                // Kiểm tra xem ô checkbox có đang được check không
                if ($('#chk-pri').is(':checked')) {
                    // Nếu có: Mở khóa nút
                    $btn.prop('disabled', false).css({
                        'opacity': '1',
                        'cursor': 'pointer'
                    });
                } else {
                    // Nếu không: Khóa nút lại
                    $btn.prop('disabled', true).css({
                        'opacity': '0.5',
                        'cursor': 'not-allowed'
                    });
                }
            }

            // 1. Chạy hàm kiểm tra ngay khi trang vừa load xong
            toggleSubmitButton();

            // 2. Bắt sự kiện mỗi khi người dùng click vào ô checkbox
            // (Dùng $(document).on giúp code vẫn chạy đúng ngay cả khi form được load bằng Ajax/Popup)
            $(document).on('change', '#chk-pri', function() {
                toggleSubmitButton();
            });
        });
    </script>


    <?php
    get_template_part('templates/template', 'footer');
    get_footer(); ?>