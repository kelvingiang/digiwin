<?php
declare(strict_types=1);

namespace DGW\Admin;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Tùy biến cột hiển thị danh sách bài viết mặc định trong WP-Admin (chuyển từ inc/admin/admin-custom-columns.php)
 */
class PostColumns
{
    /**
     * [2026-11-19] - @author: Kelvin - Đăng ký các hook quản lý cột danh sách post
     * [2026-11-25] - @author: Kelvin - Nhận đủ 2 tham số ($column, $postId) cho manage_posts_custom_column
     */
    public static function register(): void
    {
        add_filter('manage_posts_columns', [self::class, 'filterColumns']);
        add_action('manage_posts_custom_column', [self::class, 'renderColumn'], 10, 2);
        add_filter('manage_edit-post_sortable_columns', [self::class, 'sortableColumns']);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Định nghĩa lại các cột hiển thị của bảng Post
     */
    public static function filterColumns(array $columns): array
    {
        unset($columns['tags'], $columns['comments'], $columns['date']);

        $columns['author']      = __('Author', 'dgw');
        $columns['categories']  = __('Category', 'dgw');
        $columns['home']        = __('Home Page', 'dgw');
        $columns['language']    = __('Language', 'dgw');
        $columns['order']       = __('Show Order', 'dgw');
        $columns['create-date'] = __('Create Date', 'dgw');

        return $columns;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Render giá trị cho các cột tùy chỉnh với Late Escaping
     * [2026-11-25] - @author: Kelvin - Bổ sung render cột 'img' hiển thị ảnh đại diện cho CPT (vd: slider)
     */
    public static function renderColumn(string $column, int $postId = 0): void
    {
        global $post;
        $targetPostId = $postId > 0 ? $postId : ($post instanceof WP_Post ? (int) $post->ID : 0);
        if ($targetPostId <= 0) {
            return;
        }

        switch ($column) {
            case 'img':
                $thumbId = (int) get_post_thumbnail_id($targetPostId);
                $imgSrc  = $thumbId > 0 ? wp_get_attachment_image_src($thumbId, 'medium') : false;

                if (is_array($imgSrc) && !empty($imgSrc[0])) {
                    $editLink = current_user_can('edit_post', $targetPostId) ? get_edit_post_link($targetPostId) : '';
                    $imgHtml  = sprintf(
                        '<img src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async" style="height:auto;" />',
                        esc_url((string) $imgSrc[0]),
                        esc_attr(get_the_title($targetPostId)),
                        (int) ($imgSrc[1] ?? 0),
                        (int) ($imgSrc[2] ?? 0)
                    );

                    echo $editLink
                        ? '<div><a href="' . esc_url($editLink) . '">' . $imgHtml . '</a></div>'
                        : '<div>' . $imgHtml . '</div>';
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;

            case 'home':
                if (get_post_meta($targetPostId, '_metabox_home', true)) {
                    echo '<div class="show-home"></div>';
                }
                break;

            case 'language':
                echo esc_html((string) get_post_meta($targetPostId, '_metabox_langguage', true));
                break;

            case 'order':
                echo esc_html((string) get_post_meta($targetPostId, '_metabox_order', true));
                break;

            case 'create-date':
                $targetPost = get_post($targetPostId);
                if ($targetPost instanceof WP_Post) {
                    echo esc_html(get_the_date('d/m/Y', $targetPost));
                }
                break;
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cho phép sắp xếp theo ngày tạo và thứ tự hiển thị
     */
    public static function sortableColumns(array $columns): array
    {
        $columns['create-date'] = 'date';
        $columns['order']       = 'order';

        return $columns;
    }
}
