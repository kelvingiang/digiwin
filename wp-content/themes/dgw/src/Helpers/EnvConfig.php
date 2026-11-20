<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Khởi tạo hằng số đường dẫn theme, nạp biến môi trường .env và cấu hình SMTP
 */
class EnvConfig
{
    /**
     * [18/11/2026] - Định nghĩa hằng số đường dẫn/URL theme và cấu hình SMTP mặc định
     */
    public static function init(): void
    {
        if (!defined('TIME_OUT_CLEAR')) {
            define('TIME_OUT_CLEAR', 1 * 60 * 30);
        }
        if (!defined('DIR_CONTROLLER')) {
            define('DIR_CONTROLLER', THEME_URL . DS . 'controller' . DS);
        }
        if (!defined('DIR_MODEL')) {
            define('DIR_MODEL', THEME_URL . DS . 'model' . DS);
        }
        if (!defined('DIR_VIEW')) {
            define('DIR_VIEW', THEME_URL . DS . 'view' . DS);
        }
        if (!defined('DIR_CLASS')) {
            define('DIR_CLASS', THEME_URL . DS . 'class' . DS);
        }
        if (!defined('DIR_TAXONOMY')) {
            define('DIR_TAXONOMY', THEME_URL . DS . 'taxonomy' . DS);
        }
        if (!defined('DIR_METABOX')) {
            define('DIR_METABOX', THEME_URL . DS . 'metabox' . DS);
        }
        if (!defined('DIR_IMAGES')) {
            define('DIR_IMAGES', THEME_URL . DS . 'images' . DS);
        }
        if (!defined('DIR_ICON')) {
            define('DIR_ICON', DIR_IMAGES . 'icon' . DS);
        }
        if (!defined('DIR_COMPONENT')) {
            define('DIR_COMPONENT', THEME_URL . DS . 'component' . DS);
        }
        if (!defined('DIR_FILE')) {
            define('DIR_FILE', THEME_URL . DS . 'file' . DS);
        }
        if (!defined('DIR_SHORTCODE')) {
            define('DIR_SHORTCODE', THEME_URL . DS . 'shortcode' . DS);
        }
        if (!defined('DIR_LANGUAGES')) {
            define('DIR_LANGUAGES', THEME_URL . DS . 'languages' . DS);
        }

        if (!defined('PART_IMAGES')) {
            define('PART_IMAGES', THEME_PART . '/images/');
        }
        if (!defined('PART_ICON')) {
            define('PART_ICON', PART_IMAGES . '/icons/');
        }
        if (!defined('PART_FILE')) {
            define('PART_FILE', THEME_PART . '/file/');
        }

        self::loadEnv(ABSPATH . '.env');
        self::loadEnv(THEME_URL . DS . '.env');

        self::defineSmtpConstants();
    }

    /**
     * [18/11/2026] - Đọc biến môi trường từ file .env nếu chưa tồn tại trong getenv()
     */
    public static function loadEnv(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (getenv($key) === false) {
                    putenv("{$key}={$val}");
                    $_ENV[$key]    = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
    }

    /**
     * [18/11/2026] - Định nghĩa các hằng số SMTP ưu tiên wp-config.php -> .env -> fallback an toàn
     */
    private static function defineSmtpConstants(): void
    {
        if (!defined('SMTP_HOST')) {
            define('SMTP_HOST', defined('WP_SMTP_HOST') ? WP_SMTP_HOST : (getenv('SMTP_HOST') ?: 'smtp.gmail.com'));
        }
        if (!defined('SMTP_PORT')) {
            define('SMTP_PORT', defined('WP_SMTP_PORT') ? WP_SMTP_PORT : (getenv('SMTP_PORT') ? (int) getenv('SMTP_PORT') : 465));
        }
        if (!defined('SMTP_SECURE')) {
            define('SMTP_SECURE', defined('WP_SMTP_SECURE') ? WP_SMTP_SECURE : (getenv('SMTP_SECURE') ?: 'ssl'));
        }
        if (!defined('SMTP_AUTH')) {
            $envAuth = getenv('SMTP_AUTH');
            define(
                'SMTP_AUTH',
                defined('WP_SMTP_AUTH')
                    ? WP_SMTP_AUTH
                    : ($envAuth !== false && $envAuth !== '' ? filter_var($envAuth, FILTER_VALIDATE_BOOLEAN) : true)
            );
        }
        if (!defined('SMTP_USERNAME')) {
            define('SMTP_USERNAME', defined('WP_SMTP_USERNAME') ? WP_SMTP_USERNAME : (getenv('SMTP_USERNAME') ?: ''));
        }
        if (!defined('SMTP_PASSWORD')) {
            define('SMTP_PASSWORD', defined('WP_SMTP_PASSWORD') ? WP_SMTP_PASSWORD : (getenv('SMTP_PASSWORD') ?: ''));
        }
        if (!defined('SMTP_FROM_EMAIL')) {
            define('SMTP_FROM_EMAIL', defined('WP_SMTP_FROM_EMAIL') ? WP_SMTP_FROM_EMAIL : (getenv('SMTP_FROM_EMAIL') ?: (defined('SMTP_USERNAME') ? SMTP_USERNAME : '')));
        }
        if (!defined('SMTP_FROM_NAME')) {
            define('SMTP_FROM_NAME', defined('WP_SMTP_FROM_NAME') ? WP_SMTP_FROM_NAME : (getenv('SMTP_FROM_NAME') ?: 'Digiwin vietnam'));
        }
    }
}
