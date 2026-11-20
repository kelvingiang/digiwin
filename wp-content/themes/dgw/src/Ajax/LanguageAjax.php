<?php
declare(strict_types=1);

namespace DGW\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler chuyển đổi ngôn ngữ qua Cookie site_lang
 */
class LanguageAjax
{
    /**
     * [18/11/2026] - Đăng ký AJAX action đổi ngôn ngữ
     */
    public static function register(): void
    {
        add_action('wp_ajax_change_languages', [self::class, 'handleChangeLanguages']);
        add_action('wp_ajax_nopriv_change_languages', [self::class, 'handleChangeLanguages']);
    }

    /**
     * [18/11/2026] - Lưu cookie site_lang ('cn' hoặc 'vn')
     */
    public static function handleChangeLanguages(): void
    {
        $response = ['status' => 'error'];

        if (!empty($_POST['type'])) {
            $type = sanitize_text_field(wp_unslash($_POST['type']));
            $lang = ($type === 'cn') ? 'cn' : 'vn';

            setcookie(
                'site_lang',
                $lang,
                time() + YEAR_IN_SECONDS,
                '/'
            );

            $_COOKIE['site_lang'] = $lang;

            $response = [
                'status'       => 'ok',
                'current_lang' => $lang,
            ];
        }

        wp_send_json($response);
    }
}
