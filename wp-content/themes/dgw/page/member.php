<?php
/**
 * Template Name: Member Page
 * Description: Custom template for Member Page
 * 
 * [27/10/2026] Redesign trang member.php tương tự trang reset-password:
 * - Căn cách top 100px qua SCSS, căn giữa cân đối, loại bỏ layout giãn cách thô cứng.
 * - Thiết kế giao diện dạng Card hiện đại (bo góc 16px, shadow mềm, icon biểu trưng).
 * - Hỗ trợ Eye Toggle ẩn/hiện mật khẩu cho toàn bộ các trường mật khẩu.
 * - Nâng cấp Segmented Tabs cho Đăng nhập / Đăng ký và làm mới giao diện quản lý Profile.
 * - Tối ưu UX với trạng thái Loading spinner và thông báo phản hồi (Alert Msg) chuẩn UI.
 */

get_header();
get_template_part('templates/header');

// [27/10/2026] Lấy và bảo vệ dữ liệu thành viên từ session cookie
$data = null;
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}

$user_email      = $data ? esc_html($data->email ?? '') : '';
$user_name       = $data ? esc_attr($data->username ?? '') : '';
$user_phone      = $data ? esc_attr($data->phone ?? '') : '';
$user_company    = $data ? esc_attr($data->company ?? '') : '';
$user_tax        = $data ? esc_attr($data->tax ?? '') : '';
$user_position   = $data ? ($data->position ?? '') : '';
$user_industry   = $data ? ($data->industry ?? '') : '';
$user_department = $data ? ($data->department ?? '') : '';
?>

<!-- Tiêu đề ẩn tối ưu SEO & Screen Readers -->
<div class="page-title-h1">
    <h1><?php echo __('member login and register', 'dgw'); ?></h1> 
</div>

<div class="member-space">
    <!-- =========================================================================
         1. TRẠNG THÁI CHƯA ĐĂNG NHẬP (LOGIN & REGISTER CARDS)
         ========================================================================= -->
    <div id="ui-login-form" class="member-card member-auth-card" style="display:none">
        
        <!-- [27/10/2026] Segmented Control Tabs chuyển đổi Đăng nhập / Đăng ký -->
        <div id="tab-buttons" class="member-tabs">
            <div class="tab-btn active" data-tab="login"><?php echo __('Login', 'dgw'); ?></div>
            <div class="tab-btn" data-tab="register"><?php echo __('Register', 'dgw'); ?></div>
        </div>

        <!-- TAB 1: FORM ĐĂNG NHẬP -->
        <div id="tab-login" class="tab-content tab-login" data-page="member">
            <div class="member-card-header">
                <div class="member-card-header__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h2 class="member-card-header__title"><?php echo __('Login', 'dgw'); ?></h2>
                <p class="member-card-header__subtitle">
                    <?php echo __('Welcome back! Please enter your credentials to login', 'dgw'); ?>
                </p>
            </div>

            <!-- Email -->
            <div class="one-columns">
                <div class="row-cell">
                    <label for="login-email">
                        <?php echo __('E-mail', 'dgw'); ?> <span class="required">*</span>
                    </label>
                    <input type="email"
                        id="login-email"
                        placeholder="example@email.com"
                        autocomplete="email"
                        required />
                </div>
            </div>

            <!-- Password với Eye Toggle -->
            <div class="one-columns">
                <div class="row-cell">
                    <label for="login-password">
                        <?php echo __('Password', 'dgw'); ?> <span class="required">*</span>
                    </label>
                    <div class="member-input-wrap">
                        <input type="password"
                            id="login-password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required />
                        <button type="button"
                            class="member-toggle-pw"
                            data-target="login-password"
                            title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>"
                            aria-label="Toggle password visibility">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Nút hành động & liên kết Quên mật khẩu -->
            <div class="member-auth-actions">
                <div class="member-forgot-link">
                    <button type="button" id="btn-forget-password" class="btn-password">
                        <?php echo __('Forget Password', 'dgw'); ?>?
                    </button>
                </div>
                <button type="button" id="btn-login" class="btn-my-style">
                    <?php echo __('Login', 'dgw'); ?>
                </button>
            </div>

            <p id="login-msg" class="msg" role="alert"></p>
        </div>

        <!-- TAB 2: FORM ĐĂNG KÝ THÀNH VIÊN -->
        <div id="tab-register" class="tab-content tab-register" style="display:none">
            <div class="member-card-header">
                <div class="member-card-header__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                </div>
                <h2 class="member-card-header__title"><?php echo __('Register', 'dgw'); ?></h2>
                <p class="member-card-header__subtitle">
                    <?php echo __('Create your Digiwin member account to access resources', 'dgw'); ?>
                </p>
            </div>

            <!-- [27/10/2026] Render form đăng ký đầy đủ thông qua front-core.php -->
            <?php dgw_render_auth_form_full(); ?>
        </div>
    </div>

    <!-- =========================================================================
         2. TRẠNG THÁI ĐÃ ĐĂNG NHẬP (PROFILE & ACCOUNT MANAGEMENT CARD)
         ========================================================================= -->
    <div id="ui-logged-in" class="member-card member-profile-card logged-in" 
    style="display:none">

        <!-- [27/10/2026] Thanh thông tin tài khoản & Nút Đăng xuất -->
        <div class="member-profile-header" style="padding: 1rem;">
            <div class="member-profile-header__user">
                <div class="member-profile-header__avatar" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="member-profile-header__info">
                    <h3 class="email"><?php echo $user_email; ?></h3>
                    <span class="badge"><?php echo __('Active Member', 'dgw'); ?></span>
                </div>
            </div>

            <button id="btn-logout" class="btn-logout" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span><?php echo __('Logout', 'dgw'); ?></span>
            </button>
        </div>

        <!-- SECTION 1: ĐỔI MẬT KHẨU (SECURITY) -->
        <div class="member-section" style="padding: 1rem;">
            <h4 class="member-section__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span><?php echo __('Change Password', 'dgw'); ?></span>
            </h4>
            <p class="member-section__desc">
                <?php echo __('For security reasons, your new password must be at least 6 characters', 'dgw'); ?>
            </p>

            <div class="three-columns">
                <!-- Mật khẩu hiện tại -->
                <div class="row-cell">
                    <label for="current-password">
                        <?php echo __('Current Password', 'dgw'); ?> <span class="required">*</span>
                    </label>
                    <div class="member-input-wrap">
                        <input type="password" id="current-password" placeholder="••••••••" autocomplete="current-password" />
                        <button type="button" class="member-toggle-pw" data-target="current-password" title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mật khẩu mới -->
                <div class="row-cell">
                    <label for="chang-password">
                        <?php echo __('New Password', 'dgw'); ?> <span class="required">*</span>
                    </label>
                    <div class="member-input-wrap">
                        <input type="password" id="chang-password" placeholder="••••••••" autocomplete="new-password" />
                        <button type="button" class="member-toggle-pw" data-target="chang-password" title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Xác nhận mật khẩu mới -->
                <div class="row-cell">
                    <label for="chang-confirm-password">
                        <?php echo __('Confirm Password', 'dgw'); ?> <span class="required">*</span>
                    </label>
                    <div class="member-input-wrap">
                        <input type="password" id="chang-confirm-password" placeholder="••••••••" autocomplete="new-password" />
                        <button type="button" class="member-toggle-pw" data-target="chang-confirm-password" title="<?php esc_attr_e('Show/Hide password', 'dgw'); ?>">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="btn-space" style="text-align: center; margin-top: 6px;">
                <button id="btn-change-password" class="btn-my-style member-btn-primary" type="button">
                    <span class="member-spinner"></span>
                    <span class="btn-text"><?php echo __('Change Password', 'dgw'); ?></span>
                </button>
                <p id="change-password-msg" class="msg" role="alert"></p>
            </div>
        </div>

        <div class="member-divider"></div>

        <!-- SECTION 2: THÔNG TIN DOANH NGHIỆP & CÁ NHÂN -->
        <div class="member-section" style="padding: 1rem;">
            <h4 class="member-section__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span><?php echo __('Change Information', 'dgw'); ?></span>
            </h4>
            <p class="member-section__desc">
                <?php echo __('Update your company profile and contact details', 'dgw'); ?>
            </p>

            <div class="three-columns">
                <div class="row-cell">
                    <label for="chang-username"><?php echo __('Full Name', 'dgw'); ?></label>
                    <input type="text" id="chang-username" value="<?php echo $user_name; ?>" />
                </div>
                <div class="row-cell">
                    <label for="chang-position"><?php echo __('Position', 'dgw'); ?></label>
                    <select id="chang-position">
                        <option value=""><?php echo __('Select Position', 'dgw'); ?></option>
                        <?php
                        $positions = member_position_list();
                        foreach ($positions as $key => $value) {
                            $selected = ($user_position === $key) ? 'selected' : '';
                            echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="row-cell">
                    <label for="chang-phone"><?php echo __('Phone', 'dgw'); ?></label>
                    <input type="text" id="chang-phone" class="type-phone-more" maxlength="15" value="<?php echo $user_phone; ?>" />
                </div>
            </div>

            <div class="two-columns">
                <div class="row-cell">
                    <label for="chang-company"><?php echo __('Company Name', 'dgw'); ?></label>
                    <input type="text" id="chang-company" value="<?php echo $user_company; ?>" />
                </div>
                <div class="row-cell">
                    <label for="chang-tax"><?php echo __('Tax Number', 'dgw'); ?></label>
                    <input type="text" id="chang-tax" class="type-number" maxlength="13" value="<?php echo $user_tax; ?>" />
                </div>
            </div>

            <div class="two-columns">
                <div class="row-cell">
                    <label for="chang-industry"><?php echo __('Industry', 'dgw'); ?></label>
                    <select id="chang-industry">
                        <option value=""><?php echo __('Select Industry', 'dgw'); ?></option>
                        <?php
                        $industries = industry_sector_list();
                        foreach ($industries as $key => $value) {
                            $selected = ($user_industry === $key) ? 'selected' : '';
                            echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="row-cell">
                    <label for="chang-department"><?php echo __('Department', 'dgw'); ?></label>
                    <select id="chang-department">
                        <option value=""><?php echo __('Select Department', 'dgw'); ?></option>
                        <?php
                        $departments = department_list();
                        foreach ($departments as $key => $value) {
                            $selected = ($user_department === $key) ? 'selected' : '';
                            echo "<option value='" . esc_attr($key) . "' $selected>" . esc_html($value) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="btn-space" style="text-align: center; margin-top: 6px;">
                <button id="btn-change-info" class="btn-my-style member-btn-primary" type="button">
                    <span class="member-spinner"></span>
                    <span class="btn-text"><?php echo __('Change Information', 'dgw'); ?></span>
                </button>
                <p id="change-info-msg" class="msg" role="alert"></p>
            </div>
        </div>

    </div>
</div>

<script>
/**
 * [27/10/2026] Script xử lý tương tác UI trang Member:
 * - Hỗ trợ Eye Toggle ẩn/hiện mật khẩu cho tất cả trường password.
 * - Validate client-side và xử lý Ajax đổi mật khẩu (#btn-change-password).
 * - Xử lý Ajax cập nhật thông tin thành viên (#btn-change-info).
 * - Quản lý ràng buộc checkbox điều khoản đối với nút đăng ký (#btn-register).
 */
jQuery(document).ready(function($) {
    // 1. Toggle ẩn/hiện mật khẩu
    $(document).on('click', '.member-toggle-pw', function(e) {
        e.preventDefault();
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        const $eyeOpen = $(this).find('.eye-open');
        const $eyeClosed = $(this).find('.eye-closed');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $eyeOpen.hide();
            $eyeClosed.show();
        } else {
            $input.attr('type', 'password');
            $eyeOpen.show();
            $eyeClosed.hide();
        }
    });

    // 2. Chuyển đổi trạng thái Tab (Đăng nhập / Đăng ký) & tự động mở rộng Card khi ở Tab Đăng ký
    $(document).on('click', '.tab-btn', function() {
        if ($(this).data('tab') === 'register') {
            $('#ui-login-form').addClass('is-register');
        } else {
            $('#ui-login-form').removeClass('is-register');
        }
        // [28/10/2026] Reset thông báo đăng nhập/đăng ký khi chuyển qua lại giữa các tab
        $('#login-msg, #register-msg').removeClass('show msg--success msg--error').text('').removeAttr('style');
    });

    // Helper hiển thị message
    function setAlertMsg($elem, text, isSuccess) {
        $elem.removeClass('msg--success msg--error show')
             .addClass('show ' + (isSuccess ? 'msg--success' : 'msg--error'))
             .text(text);
    }

    // Lấy Ajax URL và Nonce linh hoạt
    const ajaxUrl = (typeof MyAjax !== 'undefined' && MyAjax.ajax_url) 
                    ? MyAjax.ajax_url 
                    : ((typeof MemberAuth !== 'undefined' && MemberAuth.ajaxurl) ? MemberAuth.ajaxurl : '<?php echo esc_url(admin_url('admin-ajax.php')); ?>');
    const authNonce = (typeof MemberAuth !== 'undefined' && MemberAuth.nonce) 
                      ? MemberAuth.nonce 
                      : '<?php echo wp_create_nonce('member_auth_nonce'); ?>';

    // 2. Xử lý đổi mật khẩu
    $(document).on('click', '#btn-change-password', function(e) {
        e.preventDefault();

        const current  = $('#current-password').val().trim();
        const password = $('#chang-password').val().trim();
        const confirm  = $('#chang-confirm-password').val().trim();
        const $msg     = $('#change-password-msg');
        const $btn     = $(this);
        const currentLang = document.documentElement.lang;
        const sendLang = (currentLang === 'zh-TW' || currentLang.indexOf('zh') === 0) ? 'cn' : 'vn';

        $msg.removeClass('show msg--success msg--error').text('');

        // Validate client-side
        if (!current || !password || !confirm) {
            setAlertMsg($msg, sendLang === 'cn' ? '請填寫完整資訊' : 'Vui lòng điền đầy đủ mật khẩu hiện tại, mật khẩu mới và xác nhận mật khẩu', false);
            return;
        }

        if (password.length < 6) {
            setAlertMsg($msg, sendLang === 'cn' ? '新密碼長度至少需要 6 個字元' : 'Mật khẩu mới phải có ít nhất 6 ký tự', false);
            return;
        }

        if (password !== confirm) {
            setAlertMsg($msg, sendLang === 'cn' ? '確認密碼不一致' : 'Mật khẩu xác nhận không khớp', false);
            return;
        }

        // Bật loading
        $btn.addClass('is-loading').prop('disabled', true);
        const origText = $btn.find('.btn-text').text();
        $btn.find('.btn-text').text(sendLang === 'cn' ? '處理中...' : 'Đang xử lý...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'member_change_password',
                nonce: authNonce,
                current: current,
                password: password,
                confirm: confirm,
                lang: sendLang
            },
            success: function(res) {
                $btn.removeClass('is-loading').prop('disabled', false).find('.btn-text').text(origText);
                if (res && res.success) {
                    setAlertMsg($msg, res.data.message || (sendLang === 'cn' ? '密碼更新成功' : 'Mật khẩu đã được cập nhật thành công'), true);
                    $('#current-password, #chang-password, #chang-confirm-password').val('');
                    setTimeout(function() {
                        $msg.removeClass('show').text('');
                    }, 4000);
                } else {
                    const errorMsg = (res && res.data && res.data.message) ? res.data.message : (sendLang === 'cn' ? '操作失敗' : 'Đổi mật khẩu thất bại');
                    setAlertMsg($msg, errorMsg, false);
                }
            },
            error: function() {
                $btn.removeClass('is-loading').prop('disabled', false).find('.btn-text').text(origText);
                setAlertMsg($msg, sendLang === 'cn' ? '系統錯誤，請稍後再試' : 'Lỗi hệ thống, vui lòng thử lại sau', false);
            }
        });
    });

    // 3. Xử lý đổi thông tin cá nhân
    $(document).on('click', '#btn-change-info', function(e) {
        e.preventDefault();

        const currentLang = document.documentElement.lang;
        const sendLang = (currentLang === 'zh-TW' || currentLang.indexOf('zh') === 0) ? 'cn' : 'vn';
        const $msg     = $('#change-info-msg');
        const $btn     = $(this);

        $msg.removeClass('show msg--success msg--error').text('');

        const username   = $('#chang-username').val().trim();
        const company    = $('#chang-company').val().trim();
        const phone      = $('#chang-phone').val().trim();
        const industry   = $('#chang-industry').val();
        const department = $('#chang-department').val();
        const position   = $('#chang-position').val();
        const tax        = $('#chang-tax').val().trim();

        // Bật loading
        $btn.addClass('is-loading').prop('disabled', true);
        const origText = $btn.find('.btn-text').text();
        $btn.find('.btn-text').text(sendLang === 'cn' ? '儲存中...' : 'Đang lưu...');

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'member_change_info',
                nonce: authNonce,
                username: username,
                company: company,
                phone: phone,
                industry: industry,
                department: department,
                position: position,
                tax: tax,
                lang: sendLang
            },
            success: function(res) {
                $btn.removeClass('is-loading').prop('disabled', false).find('.btn-text').text(origText);
                if (res && res.success) {
                    setAlertMsg($msg, res.data.message || (sendLang === 'cn' ? '資料更新成功' : 'Thông tin đã được cập nhật thành công'), true);
                    setTimeout(function() {
                        $msg.removeClass('show').text('');
                    }, 4000);
                } else {
                    const errorMsg = (res && res.data && res.data.message) ? res.data.message : (sendLang === 'cn' ? '操作失敗' : 'Cập nhật thất bại');
                    setAlertMsg($msg, errorMsg, false);
                }
            },
            error: function() {
                $btn.removeClass('is-loading').prop('disabled', false).find('.btn-text').text(origText);
                setAlertMsg($msg, sendLang === 'cn' ? '系統錯誤，請稍後再試' : 'Lỗi hệ thống, vui lòng thử lại sau', false);
            }
        });
    });

    // 4. Ràng buộc checkbox điều khoản bảo mật đối với nút đăng ký (#btn-register)
    function toggleSubmitButton() {
        const $btn = $('#btn-register');
        if ($('#chk-pri').is(':checked')) {
            $btn.prop('disabled', false).css({
                'opacity': '1',
                'cursor': 'pointer'
            });
        } else {
            $btn.prop('disabled', true).css({
                'opacity': '0.5',
                'cursor': 'not-allowed'
            });
        }
    }

    toggleSubmitButton();
    $(document).on('change', '#chk-pri', function() {
        toggleSubmitButton();
    });
});
</script>

<?php
get_template_part('templates/footer');
get_footer();
?>