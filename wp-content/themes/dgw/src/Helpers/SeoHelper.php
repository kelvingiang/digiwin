<?php
declare(strict_types=1);

namespace DGW\Helpers;

use DGW\Models\ProductModel;
use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý Technical SEO: document title, meta description/keywords, noindex member
 */
class SeoHelper
{
    /**
     * [18/11/2026] - Đăng ký hook SEO vào WordPress
     */
    public static function register(): void
    {
        add_filter('document_title_parts', [self::class, 'filterDocumentTitleParts']);
        add_action('wp_head', [self::class, 'renderMetaTags']);
        add_action('wp_head', [self::class, 'renderNoindexMemberPage']);
    }

    /**
     * [18/11/2026] - Lấy đối tượng WP_Term an toàn theo slug hoặc term_id trên các taxonomy của theme
     */
    public static function getCategoryByParam($cate): ?WP_Term
    {
        if (empty($cate)) {
            return null;
        }

        if (is_numeric($cate)) {
            $term = get_term((int) $cate);
            return ($term instanceof WP_Term) ? $term : null;
        }

        $slug       = sanitize_title((string) $cate);
        $taxonomies = [
            'solutions_category',
            'casestudies_category',
            'resources_category',
            'active_category',
            'services_category',
            'industries_category',
            'joinus_category',
            'advertising_category',
            'category',
        ];

        foreach ($taxonomies as $taxonomy) {
            $term = get_term_by('slug', $slug, $taxonomy);
            if ($term instanceof WP_Term) {
                return $term;
            }
        }

        return null;
    }

    /**
     * [18/11/2026] - Tùy biến tiêu đề trang theo cấu trúc danh mục/sản phẩm
     */
    public static function filterDocumentTitleParts($titleParts)
    {
        $siteName = get_option('company_name_vn') ?: get_bloginfo('name');
        $pageName = (string) get_query_var('pagename');
        $title    = '';

        if (is_home() || is_front_page()) {
            $title = $siteName;
        } elseif (is_single() || is_page()) {
            $cate = get_query_var('cate');
            $sp   = get_query_var('sp');

            if (empty($cate) && empty($sp)) {
                $title = trim('Digiwin ' . $pageName);
            } elseif (!empty($cate)) {
                $cateObj = self::getCategoryByParam($cate);
                if ($cateObj instanceof WP_Term) {
                    $title = $cateObj->name . ' Digiwin';
                }
            } elseif (!empty($sp)) {
                // [2026-11-19] - @author: Kelvin - Gọi trực tiếp ProductModel thay cho hàm global get_product()
                $proArr = ProductModel::getById((int) $sp);
                if (!empty($proArr['seo_title'])) {
                    $title = $proArr['seo_title'] . ' Digiwin';
                }
            }
        } elseif (is_tax() || is_tag() || is_category()) {
            $term = get_queried_object();
            if ($term && isset($term->term_id)) {
                $termMeta    = get_option('taxonomy_' . $term->term_id, []);
                $strSeoTitle = is_array($termMeta) ? ($termMeta['txtTitleSeo'] ?? '') : '';
                $title       = empty($strSeoTitle) ? $siteName : ($siteName . ' - ' . $strSeoTitle);
            }
        }

        if (!empty($title)) {
            $titleParts['title'] = $title;
        }

        return $titleParts;
    }

    /**
     * [18/11/2026] - Render thẻ meta description và keywords khi không dùng Rank Math
     */
    public static function renderMetaTags(): void
    {
        if (defined('RANK_MATH_VERSION')) {
            return;
        }

        $siteName        = get_option('company_name_vn') ?: get_bloginfo('name');
        $siteDescription = get_option('company_name_vn') . ' - ' . get_option('company_address_vn');
        $siteKeywords    = (string) get_option('company_name_vn');
        $description     = '';
        $keywords        = '';
        $pageName        = (string) get_query_var('pagename');

        if (is_home() || is_front_page()) {
            $description = $siteDescription;
            $keywords    = $siteKeywords;
        } elseif (is_single() || is_page()) {
            $cate = get_query_var('cate');
            $sp   = get_query_var('sp');

            if (empty($cate) && empty($sp)) {
                $description = $siteDescription;
                $keywords    = trim($pageName . ', ' . $siteName, ', ');
            } elseif (!empty($cate)) {
                $cateObj      = self::getCategoryByParam($cate);
                $categoryName = ($cateObj instanceof WP_Term) ? $cateObj->name : '';
                $description  = $siteDescription . ($categoryName !== '' ? ' - ' . $categoryName : '');
                $keywords     = implode(', ', array_filter([$pageName, $siteName, $categoryName]));
            } elseif (!empty($sp)) {
                // [2026-11-19] - @author: Kelvin - Gọi trực tiếp ProductModel thay cho hàm global get_product()
                $proArr      = ProductModel::getById((int) $sp) ?? [];
                $description = $proArr['seo_description'] ?? $siteDescription;
                $keywords    = !empty($proArr['seo_key']) ? (string) $proArr['seo_key'] : $siteKeywords;
            }
        } elseif (is_tax() || is_tag() || is_category()) {
            $term = get_queried_object();
            if ($term && isset($term->term_id)) {
                $termMeta    = get_option('taxonomy_' . $term->term_id, []);
                $description = is_array($termMeta) ? ($termMeta['strDescriptionSeo'] ?? $siteDescription) : $siteDescription;
                $keywords    = is_array($termMeta) ? ($termMeta['seo_keywords'] ?? $siteKeywords) : $siteKeywords;
            }
        }

        if (!empty($description)) {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
        }
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
        }
    }

    /**
     * [18/11/2026] - Chặn Google index trang member riêng tư
     */
    public static function renderNoindexMemberPage(): void
    {
        if (is_page('member')) {
            echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
        }
    }
}
