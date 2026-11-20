<?php
declare(strict_types=1);

namespace DGW\Core;

use DateTimeImmutable;
use Model_Download_Function;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý phiên thành viên, token tải tài liệu, nạp script và kiểm soát LiteSpeed Cache
 */
class MemberDownload
{
    /**
     * [18/11/2026] - Đăng ký hook script, footer popup và điều khiển LiteSpeed Cache
     */
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueueScripts']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueMemberAuthScript']);
        add_action('wp_footer', [self::class, 'renderFooterModals']);
        add_action('template_redirect', [self::class, 'controlLiteSpeedCache'], 5);
    }

    /**
     * [18/11/2026] - Tạo mốc thời gian hết hạn sau 24 giờ chuẩn định dạng MySQL
     */
    public static function generateExpiryTime(): string
    {
        $timezone = wp_timezone();
        $now      = new DateTimeImmutable('now', $timezone);
        return $now->modify('+24 hours')->format('Y-m-d H:i:s');
    }

    /**
     * [18/11/2026] - Kiểm tra token còn trong thời hạn hợp lệ hay không
     */
    public static function isTokenValid(string $expiryFromDb): bool
    {
        try {
            $timezone    = wp_timezone();
            $expiryTime  = new DateTimeImmutable($expiryFromDb, $timezone);
            $currentTime = new DateTimeImmutable('now', $timezone);

            return $currentTime < $expiryTime;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * [18/11/2026] - Convert link Google Drive sang link download trực tiếp
     */
    public static function convertGdriveToDownload(string $url): string
    {
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
        }
        return $url;
    }

    /**
     * [18/11/2026] - Lấy thông tin tài khoản thành viên dựa trên session key
     */
    public static function getMemberInformation(string $sessionKey)
    {
        if (!class_exists('Model_Download_Function')) {
            require_once DIR_MODEL . 'model-download-function.php';
        }
        $model = new Model_Download_Function();
        return $model->get_user_by_session($sessionKey);
    }

    /**
     * [18/11/2026] - Lấy thông tin thành viên hiện tại đang đăng nhập qua cookie
     */
    public static function getCurrentCustomUser()
    {
        if (empty($_COOKIE['custom_session'])) {
            return null;
        }

        $sessionKey = sanitize_text_field(wp_unslash($_COOKIE['custom_session']));
        if ($sessionKey === '') {
            return null;
        }

        $user = self::getMemberInformation($sessionKey);
        return $user ?: null;
    }

    /**
     * [18/11/2026] - Kiểm tra trạng thái đăng nhập của Member qua cookie session
     */
    public static function isMemberLoggedIn()
    {
        return self::getCurrentCustomUser() ?: false;
    }

    /**
     * [18/11/2026] - Đăng ký và localize script my-download.min.js
     */
    public static function enqueueScripts(): void
    {
        $downloadJsPath = get_stylesheet_directory() . '/js/my-download.min.js';
        $downloadVer    = (string) (file_exists($downloadJsPath) ? filemtime($downloadJsPath) : '1.0.0') . '.20261118v1';

        wp_enqueue_script(
            'my-script',
            get_stylesheet_directory_uri() . '/js/my-download.min.js',
            ['jquery'],
            $downloadVer,
            true
        );

        $currentUser = self::getCurrentCustomUser();

        wp_localize_script('my-script', 'MyAjax', [
            'ajax_url'     => admin_url('admin-ajax.php'),
            'nonce'        => wp_create_nonce('my_nonce'),
            'home_url'     => home_url(),
            'is_logged_in' => !empty($currentUser),
            'login_text'   => __('Login-Register', 'dgw'),
        ]);
    }

    /**
     * [18/11/2026] - Nạp JS xác thực thành viên trên trang member
     */
    public static function enqueueMemberAuthScript(): void
    {
        if (!is_page('member')) {
            return;
        }

        wp_enqueue_script('member-auth', get_template_directory_uri() . '/js/member-auth.js', ['jquery'], '1.0.0', true);
        wp_localize_script('member-auth', 'MemberAuth', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('member_auth_nonce'),
        ]);
    }

    /**
     * [18/11/2026] - Nạp modal form đăng nhập và quên mật khẩu vào footer
     */
    public static function renderFooterModals(): void
    {
        if (function_exists('member_login_register_form')) {
            echo member_login_register_form();
        }
        if (function_exists('member_forgot_password_form')) {
            echo member_forgot_password_form();
        }
    }

    /**
     * [18/11/2026] - Bỏ qua LiteSpeed Cache khi có custom_session hoặc ở các trang xác thực thành viên
     */
    public static function controlLiteSpeedCache(): void
    {
        $hasSession = !empty($_COOKIE['custom_session']);
        $isAuthPage = is_page(['member', 'reset-password', 'activate-member'])
            || is_page_template(['page/member.php', 'page/reset-password.php', 'page/activate-member.php']);

        if ($hasSession || $isAuthPage) {
            do_action('litespeed_control_set_nocache', 'DGW: Auth page or logged-in custom user');

            if (!headers_sent()) {
                header('X-LiteSpeed-Cache-Control: no-cache');
                nocache_headers();
            }
        }
    }
}
