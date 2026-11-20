<?php
declare(strict_types=1);

namespace DGW\Admin;

use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Quản lý trường đa ngôn ngữ & thứ tự cho post_tag và category trong WP-Admin (gộp inc/admin/admin-add-post-tag-field.php & admin-add-post-taxonomy-field.php)
 */
class TermMetaFields
{
    private const NONCE_ACTION = 'dgw_save_term_meta';
    private const NONCE_NAME   = 'dgw_term_meta_nonce';

    /**
     * Cấu hình đồng nhất cho từng taxonomy: tiền tố option lưu DB và tiền tố tên input form
     */
    private const TAXONOMY_CONFIGS = [
        'post_tag' => [
            'option_prefix' => 'post_tag_',
            'field_prefix'  => 'tag_',
        ],
        'category' => [
            'option_prefix' => 'post_cate_',
            'field_prefix'  => 'cate_',
        ],
    ];

    /**
     * [2026-11-19] - @author: Kelvin - Đăng ký toàn bộ hook form, save, delete và custom columns cho các taxonomy cấu hình
     */
    public static function register(): void
    {
        foreach (self::TAXONOMY_CONFIGS as $taxonomy => $config) {
            add_action("{$taxonomy}_add_form_fields", static function () use ($taxonomy): void {
                self::renderAddFields($taxonomy);
            });

            add_action("{$taxonomy}_edit_form_fields", static function (WP_Term $term) use ($taxonomy): void {
                self::renderEditFields($term, $taxonomy);
            }, 10, 1);

            add_action("created_{$taxonomy}", static function (int $termId) use ($taxonomy): void {
                self::saveFields($termId, $taxonomy);
            }, 10, 1);

            add_action("edited_{$taxonomy}", static function (int $termId) use ($taxonomy): void {
                self::saveFields($termId, $taxonomy);
            }, 10, 1);

            add_action("delete_{$taxonomy}", static function (int $termId = 0) use ($taxonomy): void {
                self::deleteFields($termId, $taxonomy);
            }, 10, 1);

            add_filter("manage_edit-{$taxonomy}_columns", [self::class, 'filterColumns']);

            add_filter("manage_{$taxonomy}_custom_column", static function ($out, string $columnName, int $termId) use ($taxonomy) {
                return self::renderColumn($out, $columnName, $termId, $taxonomy);
            }, 10, 3);
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render các input trường tùy chỉnh ở form thêm mới term
     */
    private static function renderAddFields(string $taxonomy): void
    {
        $prefix = self::TAXONOMY_CONFIGS[$taxonomy]['field_prefix'];
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
        <div class="form-field">
            <label for="<?php echo esc_attr($prefix . 'vn'); ?>"><?php esc_html_e('Name', 'dgw'); ?> (<?php esc_html_e('Vietnamese', 'dgw'); ?>)</label>
            <input type="hidden" name="<?php echo esc_attr($prefix . 'cn'); ?>" id="<?php echo esc_attr($prefix . 'cn'); ?>" value="" />
            <input type="text" name="<?php echo esc_attr($prefix . 'vn'); ?>" id="<?php echo esc_attr($prefix . 'vn'); ?>" value="" />
        </div>
        <div class="form-field">
            <label for="<?php echo esc_attr($prefix . 'en'); ?>"><?php esc_html_e('Name', 'dgw'); ?> (<?php esc_html_e('English', 'dgw'); ?>)</label>
            <input type="text" name="<?php echo esc_attr($prefix . 'en'); ?>" id="<?php echo esc_attr($prefix . 'en'); ?>" value="" />
        </div>
        <div class="form-field">
            <label for="<?php echo esc_attr($prefix . 'order'); ?>"><?php esc_html_e('Show Order', 'dgw'); ?></label>
            <input type="text" name="<?php echo esc_attr($prefix . 'order'); ?>" id="<?php echo esc_attr($prefix . 'order'); ?>" value="" />
        </div>
        <script>
            jQuery('#tag-name').on('focusout', function () {
                jQuery('#<?php echo esc_js($prefix . 'cn'); ?>').val(jQuery(this).val());
            });
        </script>
        <?php
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render các input trường tùy chỉnh ở form chỉnh sửa term
     */
    private static function renderEditFields(WP_Term $term, string $taxonomy): void
    {
        $config       = self::TAXONOMY_CONFIGS[$taxonomy];
        $prefix       = $config['field_prefix'];
        $optionPrefix = $config['option_prefix'];
        $value        = get_option($optionPrefix . $term->term_id, []);
        $data         = is_array($value) ? $value : [];

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="<?php echo esc_attr($prefix . 'vn'); ?>"><?php esc_html_e('Name', 'dgw'); ?> (<?php esc_html_e('Vietnamese', 'dgw'); ?>)</label>
            </th>
            <td>
                <input type="hidden" name="<?php echo esc_attr($prefix . 'cn'); ?>" id="<?php echo esc_attr($prefix . 'cn'); ?>" value="<?php echo esc_attr((string) ($data[$prefix . 'cn'] ?? '')); ?>" />
                <input type="text" name="<?php echo esc_attr($prefix . 'vn'); ?>" id="<?php echo esc_attr($prefix . 'vn'); ?>" value="<?php echo esc_attr((string) ($data[$prefix . 'vn'] ?? '')); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="<?php echo esc_attr($prefix . 'en'); ?>"><?php esc_html_e('Name', 'dgw'); ?> (<?php esc_html_e('English', 'dgw'); ?>)</label>
            </th>
            <td>
                <input type="text" name="<?php echo esc_attr($prefix . 'en'); ?>" id="<?php echo esc_attr($prefix . 'en'); ?>" value="<?php echo esc_attr((string) ($data[$prefix . 'en'] ?? '')); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="<?php echo esc_attr($prefix . 'order'); ?>"><?php esc_html_e('Show Order', 'dgw'); ?></label>
            </th>
            <td>
                <input type="text" name="<?php echo esc_attr($prefix . 'order'); ?>" id="<?php echo esc_attr($prefix . 'order'); ?>" value="<?php echo esc_attr((string) ($data[$prefix . 'order'] ?? '')); ?>" />
            </td>
        </tr>
        <script>
            jQuery('#name').on('focusout', function () {
                jQuery('#<?php echo esc_js($prefix . 'cn'); ?>').val(jQuery(this).val());
            });
        </script>
        <?php
    }

    /**
     * [2026-11-19] - @author: Kelvin - Kiểm tra quyền, xác thực CSRF nonce và lưu thông tin term vào wp_options
     */
    private static function saveFields(int $termId, string $taxonomy): void
    {
        if (!current_user_can('manage_categories')) {
            return;
        }

        if (empty($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        $config       = self::TAXONOMY_CONFIGS[$taxonomy];
        $prefix       = $config['field_prefix'];
        $optionPrefix = $config['option_prefix'];
        $keys         = ['cn', 'vn', 'en', 'order'];

        $hasAnyField = false;
        foreach ($keys as $suffix) {
            if (isset($_POST[$prefix . $suffix])) {
                $hasAnyField = true;
                break;
            }
        }

        if (!$hasAnyField) {
            return;
        }

        $payload = [];
        foreach ($keys as $suffix) {
            $fieldKey           = $prefix . $suffix;
            $payload[$fieldKey] = sanitize_text_field(wp_unslash($_POST[$fieldKey] ?? ''));
        }

        update_option($optionPrefix . absint($termId), $payload);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Xóa dữ liệu tùy chỉnh khi xóa term
     */
    private static function deleteFields(int $termId, string $taxonomy): void
    {
        $optionPrefix = self::TAXONOMY_CONFIGS[$taxonomy]['option_prefix'];
        $id           = $termId > 0 ? $termId : (function_exists('getParams') ? absint(getParams('tag_ID', 0)) : 0);

        if ($id > 0) {
            delete_option($optionPrefix . $id);
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cấu hình cột hiển thị trong bảng danh sách Category/Tag
     */
    public static function filterColumns(): array
    {
        return [
            'cb'         => '<input type="checkbox" />',
            'name'       => __('Name', 'dgw'),
            'slug'       => __('Slug', 'dgw'),
            'vietnamese' => __('Vietnamese', 'dgw'),
            'english'    => __('English', 'dgw'),
            'order'      => __('Show Order', 'dgw'),
            'posts'      => __('Count', 'dgw'),
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render dữ liệu từng cột tùy chỉnh trong bảng Category/Tag
     */
    private static function renderColumn($out, string $columnName, int $termId, string $taxonomy)
    {
        $term = get_term($termId, $taxonomy);
        if (!$term instanceof WP_Term) {
            return $out;
        }

        $config = self::TAXONOMY_CONFIGS[$taxonomy];
        $prefix = $config['field_prefix'];
        $raw    = get_option($config['option_prefix'] . $term->term_id, []);
        $data   = is_array($raw) ? $raw : [];

        switch ($columnName) {
            case 'order':
                echo esc_html((string) ($data[$prefix . 'order'] ?? '0'));
                break;
            case 'vietnamese':
                echo esc_html((string) ($data[$prefix . 'vn'] ?? ''));
                break;
            case 'english':
                echo esc_html((string) ($data[$prefix . 'en'] ?? ''));
                break;
        }

        return $out;
    }
}
