<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Các hook tinh chỉnh giao diện, GTM, comment form, block CSS và debug PHPMailer
 */
class ThemeTweaks
{
    /**
     * [18/11/2026] - Đăng ký các hook hệ thống của theme
     */
    public static function register(): void
    {
        add_action('get_template_part_templates/template', [self::class, 'fallbackTemplatePart'], 10, 3);
        add_filter('comment_form_fields', [self::class, 'reorderCommentFormFields']);
        add_action('add_meta_boxes', [self::class, 'moveDiscussionMetabox']);
        add_action('template_redirect', [self::class, 'disableAuthorArchives']);
        add_action('login_head', [self::class, 'renderLoginLogo']);
        add_action('wp_head', [self::class, 'renderGtmInternalUser']);
        add_action('wp_enqueue_scripts', [self::class, 'removeBlockLibraryCss'], 100);
        add_filter('safe_style_css', [self::class, 'allowTableInlineStyles']);
        add_filter('wp_kses_allowed_html', [self::class, 'allowTableStyleAttribute'], 10, 1);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_action('phpmailer_init', [self::class, 'enablePhpMailerDebug']);
        }
    }

    /**
     * [18/11/2026] - Fallback tương thích ngược cho get_template_part('templates/template', $name)
     */
    public static function fallbackTemplatePart($slug, $name = null, $args = []): void
    {
        if (empty($name)) {
            return;
        }
        $cleanName = str_replace('_', '-', (string) $name);
        get_template_part('templates/' . $cleanName, null, $args);
    }

    /**
     * [18/11/2026] - Đưa trường comment xuống sau author và email
     */
    public static function reorderCommentFormFields(array $fields): array
    {
        if (isset($fields['comment'])) {
            $commentField = $fields['comment'];
            unset($fields['comment']);
            $fields['comment'] = $commentField;
        }
        return $fields;
    }

    /**
     * [18/11/2026] - Di chuyển hộp Discussion sang sidebar phải cho các CPT
     */
    public static function moveDiscussionMetabox(): void
    {
        $postTypes = ['post', 'resources', 'solutions', 'casestudies', 'active', 'services', 'industries'];
        foreach ($postTypes as $pt) {
            remove_meta_box('commentstatusdiv', $pt, 'normal');
            add_meta_box(
                'commentstatusdiv',
                __('Discuss', 'dgw'),
                'post_comment_status_meta_box',
                $pt,
                'side',
                'default'
            );
        }
    }

    /**
     * [18/11/2026] - Chặn trang archive tác giả bằng 404
     */
    public static function disableAuthorArchives(): void
    {
        if (is_author()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            include get_query_template('404');
            exit;
        }
    }

    /**
     * [18/11/2026] - Tùy biến logo màn hình đăng nhập WP-Admin
     */
    public static function renderLoginLogo(): void
    {
        echo "<style type=\"text/css\">\n"
            . '    h1 a { background-image: url(' . esc_url(PART_IMAGES . 'logo.png') . ") !important; }\n"
            . '</style>';
    }

    /**
     * [18/11/2026] - Đẩy cờ user nội bộ lên DataLayer cho GTM
     */
    public static function renderGtmInternalUser(): void
    {
        if (!is_user_logged_in()) {
            return;
        }
        $user = wp_get_current_user();
        $role = (array) $user->roles;
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'user_status',
                'user_role': <?php echo wp_json_encode((string) ($role[0] ?? '')); ?>,
                'is_internal': 'yes'
            });
        </script>
        <?php
    }

    /**
     * [18/11/2026] - Gỡ bỏ CSS Gutenberg Block mặc định ngoài Frontend
     */
    public static function removeBlockLibraryCss(): void
    {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-block-style');
    }

    /**
     * [18/11/2026] - Cho phép CSS inline background-color và color
     */
    public static function allowTableInlineStyles(array $styles): array
    {
        $styles[] = 'background-color';
        $styles[] = 'color';
        return $styles;
    }

    /**
     * [18/11/2026] - Cho phép thuộc tính style trong thẻ table/tr/td/th
     */
    public static function allowTableStyleAttribute(array $allowedposttags): array
    {
        $allowedposttags['table']['style'] = true;
        $allowedposttags['tr']['style']    = true;
        $allowedposttags['td']['style']    = true;
        $allowedposttags['th']['style']    = true;
        return $allowedposttags;
    }

    /**
     * [18/11/2026] - Ghi log debug PHPMailer khi WP_DEBUG bật
     */
    public static function enablePhpMailerDebug($phpmailer): void
    {
        $phpmailer->SMTPDebug   = 2;
        $phpmailer->Debugoutput = static function ($str, $level): void {
            error_log('PHPMailer Debug: ' . $str);
        };
    }
}
