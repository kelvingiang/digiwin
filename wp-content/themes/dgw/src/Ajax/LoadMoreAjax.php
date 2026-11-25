<?php
declare(strict_types=1);

namespace DGW\Ajax;

use DGW\Models\CustomPostModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler xử lý các endpoint load more bài viết (bk_load_more, load_more_post, load_more_in_group)
 */
class LoadMoreAjax
{
    /**
     * [18/11/2026] - Đăng ký các AJAX hooks load more
     */
    public static function register(): void
    {
        $actions = [
            'bk_load_more'       => 'handleBkLoadMore',
            'load_more_post'     => 'handleLoadMorePost',
            'load_more_in_group' => 'handleLoadMoreInGroup',
        ];

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [self::class, $method]);
            add_action('wp_ajax_nopriv_' . $action, [self::class, $method]);
        }
    }

    /**
     * [18/11/2026] - Load thêm bài viết theo category hỗ trợ Translation Group
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     */
    public static function handleBkLoadMore(): void
    {
        $lastId   = isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0;
        $postType = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
        $cateId   = isset($_POST['cateID']) ? (int) $_POST['cateID'] : 0;
        $count    = isset($_POST['count']) ? (int) $_POST['count'] : 10;
        $cate     = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $lang = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : (string) ($_SESSION['languages'] ?? 'vn');

        $wpQuery = CustomPostModel::get_load_more_posts($postType, $cateId, $cate, $count, $lastId, $lang);
        $html    = '';

        if ($wpQuery->have_posts()) {
            $stt = $lastId + 1;
            while ($wpQuery->have_posts()) {
                $wpQuery->the_post();
                $html .= "<div class='item' data-id='" . esc_attr((string) $stt) . "'>";
                $html .= "<a href='" . esc_url(get_the_permalink()) . "'>";
                if (has_post_thumbnail()) {
                    $thumbUrl = (string) get_the_post_thumbnail_url();
                    $html    .= "<img class='item-img' src='" . esc_url($thumbUrl) . "' srcset='" . esc_url($thumbUrl) . "'/>";
                } else {
                    $html .= "<img class='item-img' src='" . esc_url(PART_IMAGES . 'no-image.jpg') . "'/>";
                }
                $html .= "<div class='item-title'>" . esc_html(get_the_title()) . '</div>';
                $html .= '</a>';
                $html .= '</div>';
                $stt++;
            }
            wp_reset_postdata();

            $response = ['status' => 'done', 'html' => $html];
        } else {
            $response = ['status' => 'empty'];
        }

        wp_send_json($response);
    }

    /**
     * [18/11/2026] - Load thêm bài viết theo category_name
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     */
    public static function handleLoadMorePost(): void
    {
        $lastId   = isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0;
        $postType = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
        $count    = isset($_POST['count']) ? (int) $_POST['count'] : 10;
        $cate     = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

        $wpQuery = CustomPostModel::get_load_more_by_category_name($postType, $cate, $count, $lastId);
        $html    = '';

        if ($wpQuery->have_posts()) {
            $stt = $lastId + 1;
            while ($wpQuery->have_posts()) {
                $wpQuery->the_post();
                $html .= "<div class='item' data-id='" . esc_attr((string) $stt) . "'>";
                $html .= "<a href='" . esc_url(get_the_permalink()) . "'>";
                if (has_post_thumbnail()) {
                    $thumbUrl = (string) get_the_post_thumbnail_url();
                    $html    .= "<img class='item-img' src='" . esc_url($thumbUrl) . "' srcset='" . esc_url($thumbUrl) . "'/>";
                } else {
                    $html .= "<img class='item-img' src='" . esc_url(PART_IMAGES . 'no-image.jpg') . "'/>";
                }
                $html .= "<div class='item-title'>" . esc_html(get_the_title()) . '</div>';
                $html .= '</a>';
                $html .= '</div>';
                $stt++;
            }
            wp_reset_postdata();

            $response = ['status' => 'done', 'html' => $html];
        } else {
            $response = ['status' => 'empty'];
        }

        wp_send_json($response);
    }

    /**
     * [18/11/2026] - Load thêm bài viết trong cùng nhóm loại trừ bài hiện tại
     * [20/11/2026] - Ủy quyền truy vấn WP_Query sang CustomPostModel chuẩn MVC
     */
    public static function handleLoadMoreInGroup(): void
    {
        $lastId = isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0;
        $cate   = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : 'post';
        $slug   = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';

        $wpQuery = CustomPostModel::get_load_more_in_group($cate, 5, $lastId + 1);
        $html    = '';

        if ($wpQuery->have_posts()) {
            $stt = $lastId + 1;
            while ($wpQuery->have_posts()) {
                $wpQuery->the_post();
                global $post;
                if ($post && $post->post_name === $slug) {
                    continue;
                }
                $html .= "<div class='item row' data-id='" . esc_attr((string) $stt) . "'>";
                $html .= "<div class='col-lg-2'>";
                if (has_post_thumbnail()) {
                    $thumbUrl = (string) get_the_post_thumbnail_url();
                    $html    .= "<img class='item-img' src='" . esc_url($thumbUrl) . "' srcset='" . esc_url($thumbUrl) . "'/>";
                } else {
                    $html .= "<img class='item-img' src='" . esc_url(PART_IMAGES . 'no-image.jpg') . "'/>";
                }
                $html .= '</div>';
                $html .= "<div class='col-lg-10'>";
                $html .= "<div class='group-list-item-title'>";
                $html .= "<a class='my-link' href='" . esc_url(get_the_permalink()) . "'><h3>" . esc_html(get_the_title()) . '</h3></a>';
                $html .= '</div>';
                $html .= '<div>' . get_the_content() . '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $stt++;
            }
            wp_reset_postdata();

            $response = ['status' => 'done', 'html' => $html];
        } else {
            $response = ['status' => 'empty'];
        }

        wp_send_json($response);
    }
}
