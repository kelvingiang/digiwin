<?php
declare(strict_types=1);

namespace DGW\Ajax;

use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - AJAX Handler tải thêm bài viết, tăng lượt xem, tăng lượt thích và kiểm tra captcha toán học
 */
class PostsAjax
{
    /**
     * [18/11/2026] - Đăng ký AJAX hooks bài viết và tương tác
     */
    public static function register(): void
    {
        add_action('init', [self::class, 'ensureAjaxSession']);

        $actions = [
            'load_more_posts'    => 'handleLoadMorePosts',
            'plus_one_view'      => 'handlePlusOneView',
            'plus_one_like'      => 'handlePlusOneLike',
            'check_math_captcha' => 'handleCheckMathCaptcha',
        ];

        foreach ($actions as $action => $method) {
            add_action('wp_ajax_' . $action, [self::class, $method]);
            add_action('wp_ajax_nopriv_' . $action, [self::class, $method]);
        }
    }

    /**
     * [18/11/2026] - Đảm bảo session sẵn sàng cho AJAX nếu header chưa flush
     */
    public static function ensureAjaxSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * [18/11/2026] - Load thêm bài viết hỗ trợ Translation Group
     */
    public static function handleLoadMorePosts(): void
    {
        $lastId  = isset($_POST['lastID']) ? (int) $_POST['lastID'] : 0;
        $post    = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : 'post';
        $rawCate = isset($_POST['cateID']) ? sanitize_text_field(wp_unslash($_POST['cateID'])) : '';
        $cateId  = is_numeric($rawCate) ? (int) $rawCate : $rawCate;
        $count   = isset($_POST['count']) ? (int) $_POST['count'] : 5;
        $cate    = isset($_POST['cate']) ? sanitize_text_field($_POST['cate']) : '';

        $args = [
            'post_type'      => $post,
            'posts_per_page' => $count,
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'meta_key'       => '_metabox_order',
            'post_status'    => 'publish',
            'meta_query'     => [
                [
                    'key'     => '_metabox_langguage',
                    'value'   => dgw_get_lang(),
                    'compare' => '=',
                ],
            ],
        ];

        if (!empty($cateId)) {
            $targetTerms = [$cateId];
            $field       = is_numeric($cateId) ? 'term_id' : 'slug';

            $termObj = is_numeric($cateId)
                ? get_term((int) $cateId, $cate)
                : get_term_by('slug', (string) $cateId, $cate);

            if ($termObj && !is_wp_error($termObj)) {
                $groupId = get_term_meta($termObj->term_id, 'translation_group', true);
                if (!empty($groupId)) {
                    $groupTerms = get_terms([
                        'taxonomy'   => $cate,
                        'fields'     => 'ids',
                        'hide_empty' => false,
                        'meta_query' => [
                            [
                                'key'     => 'translation_group',
                                'value'   => $groupId,
                                'compare' => '=',
                            ],
                        ],
                    ]);
                    if (!empty($groupTerms) && !is_wp_error($groupTerms)) {
                        $targetTerms = $groupTerms;
                        $field       = 'term_id';
                    }
                }
            }

            $args['tax_query'] = [
                [
                    'taxonomy' => $cate,
                    'field'    => $field,
                    'terms'    => $targetTerms,
                ],
            ];
        }

        $args['offset'] = $lastId;
        $wpQuery        = new WP_Query($args);

        if ($wpQuery->have_posts()) {
            $html = '';
            $stt  = $lastId + 1;

            while ($wpQuery->have_posts()) {
                $wpQuery->the_post();
                $postId    = get_the_ID();
                $thumbId   = get_post_thumbnail_id($postId);
                $urlData   = wp_get_attachment_image_src($thumbId, 'medium');
                $srcset    = wp_get_attachment_image_srcset($thumbId, 'medium');
                $permalink = get_the_permalink($postId);

                $html .= "<div class='item' data-id='" . esc_attr((string) $stt) . "' data-link='" . esc_url($permalink) . "' data-post='" . esc_attr((string) $postId) . "'>";
                $html .= '<div>';
                if (has_post_thumbnail() && is_array($urlData)) {
                    $html .= "<img class='item-img' src='" . esc_url($urlData[0]) . "' srcset='" . esc_attr((string) $srcset) . "' fetchpriority='high' width='" . esc_attr((string) $urlData[1]) . "' height='" . esc_attr((string) $urlData[2]) . "' alt='" . esc_attr(get_the_title()) . "' />";
                } else {
                    $html .= "<img class='item-img' src='" . esc_url(PART_IMAGES . 'no-image.jpg') . "' srcset='" . esc_url(PART_IMAGES . 'no-image.jpg') . "' fetchpriority='high' width='410' height='270' alt='" . esc_attr(get_the_title()) . "' />";
                }

                ob_start();
                get_template_part('templates/view-comment');
                $html .= ob_get_clean();
                $html .= '</div>';

                $html .= "<div class='item-title'><a class='item-link' href='" . esc_url($permalink) . "' title='" . esc_attr(get_the_title()) . "'><h3>" . esc_html(get_the_title()) . '</h3></a></div>';
                $html .= '</div>';

                $stt++;
            }

            wp_reset_postdata();

            wp_send_json([
                'status' => 'done',
                'html'   => $html,
            ]);
        } else {
            wp_send_json(['status' => 'empty']);
        }

        wp_die();
    }

    /**
     * [18/11/2026] - Tăng lượt xem bài viết
     */
    public static function handlePlusOneView(): void
    {
        $postId = isset($_POST['postID']) ? (int) $_POST['postID'] : 0;
        $view   = (int) get_post_meta($postId, '_metabox_view', true);
        update_post_meta($postId, '_metabox_view', $view + 1);
        wp_send_json([
            'status' => 'done',
            'html'   => $view + 1,
        ]);
    }

    /**
     * [18/11/2026] - Tăng lượt thích bài viết
     */
    public static function handlePlusOneLike(): void
    {
        $postId = isset($_POST['postID']) ? absint($_POST['postID']) : 0;
        if ($postId <= 0 || !get_post_status($postId)) {
            wp_send_json_error(['status' => 'error', 'message' => 'Invalid Post ID']);
            wp_die();
        }

        $currentLikes = (int) get_post_meta($postId, '_metabox_like', true);
        $newLikes     = $currentLikes + 1;
        update_post_meta($postId, '_metabox_like', $newLikes);

        wp_send_json_success([
            'status' => 'done',
            'count'  => $newLikes,
            'html'   => $newLikes,
        ]);
        wp_die();
    }

    /**
     * [18/11/2026] - Kiểm tra captcha toán học qua AJAX
     */
    public static function handleCheckMathCaptcha(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        $userAns = isset($_POST['answer']) ? (int) $_POST['answer'] : null;
        $realAns = isset($_SESSION['comment_captcha_answer']) ? (int) $_SESSION['comment_captcha_answer'] : null;

        if ($userAns === $realAns) {
            wp_send_json(['status' => 'ok']);
        }

        wp_send_json(['status' => 'fail']);
    }
}
