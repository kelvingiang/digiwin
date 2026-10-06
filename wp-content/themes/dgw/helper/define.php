<?php
define('TIME_OUT_CLEAR', 1 * 60 * 30);
define('DIR_CONTROLLER', THEME_URL . DS . 'controller' . DS);
define('DIR_MODEL', THEME_URL . DS . 'model' . DS);
define('DIR_VIEW', THEME_URL . DS . 'view' . DS);
define('DIR_CLASS', THEME_URL . DS . 'class' . DS);
define('DIR_TAXONOMY', THEME_URL . DS . 'taxonomy' . DS);
define('DIR_METABOX', THEME_URL . DS . 'metabox' . DS);
define('DIR_IMAGES', THEME_URL . DS . 'images' . DS);
define('DIR_ICON', DIR_IMAGES . 'icon' . DS);
define('DIR_COMPONENT', THEME_URL . DS . 'component' . DS);
define('DIR_FILE', THEME_URL . DS . 'file' . DS);
define('DIR_SHORTCODE', THEME_URL . DS . 'shortcode' . DS);
define('DIR_LANGUAGES', THEME_URL . DS . 'languages' . DS);


// duong part
define('PART_IMAGES', THEME_PART . '/images/');
define('PART_ICON', PART_IMAGES . '/icons/');
define('PART_FILE', THEME_PART . '/file/');



/**
 * [2026-10-05] - @author: Kelvin - Helper function doc bien moi truong tu file .env
 */
if (!function_exists('dgw_load_env')) {
    function dgw_load_env($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (getenv($key) === false) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
    }
}

// Nap file .env tu thu muc goc WordPress hoac Theme (neu co)
if (defined('ABSPATH')) {
    dgw_load_env(ABSPATH . '.env');
}
dgw_load_env(__DIR__ . '/../.env');

/**
 * [2026-10-05] - @author: Kelvin - Cau hinh SMTP an toan, khong hardcode mat khau dang plain text
 * Uu tien theo thu tu:
 * 1. Hang so trong wp-config.php goc (SMTP_* hoac WP_SMTP_*)
 * 2. Bien moi truong (.env / getenv)
 * 3. Fallback an toan
 */
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', defined('WP_SMTP_HOST') ? WP_SMTP_HOST : (getenv('SMTP_HOST') ?: 'smtp.gmail.com'));
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', defined('WP_SMTP_PORT') ? WP_SMTP_PORT : (getenv('SMTP_PORT') ? (int)getenv('SMTP_PORT') : 465));
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', defined('WP_SMTP_SECURE') ? WP_SMTP_SECURE : (getenv('SMTP_SECURE') ?: 'ssl'));
}
if (!defined('SMTP_AUTH')) {
    define('SMTP_AUTH', defined('WP_SMTP_AUTH') ? WP_SMTP_AUTH : (getenv('SMTP_AUTH') !== false && getenv('SMTP_AUTH') !== '' ? filter_var(getenv('SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN) : true));
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

