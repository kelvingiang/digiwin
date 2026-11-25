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
     * [20/11/2026] - Tách HTML sang View templates/modal-forgot-password.php chuẩn MVC
     */
    public static function forgotPasswordForm(): string
    {
        ob_start();
        get_template_part('templates/modal-forgot-password');
        return (string) ob_get_clean();
    }

    /**
     * [18/11/2026] - Render popup đăng nhập và đăng ký thành viên
     * [20/11/2026] - Tách HTML sang View templates/modal-auth.php chuẩn MVC
     */
    public static function loginRegisterForm(): string
    {
        ob_start();
        get_template_part('templates/modal-auth');
        return (string) ob_get_clean();
    }
}
