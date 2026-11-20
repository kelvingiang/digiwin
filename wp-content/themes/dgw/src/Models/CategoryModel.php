<?php
declare(strict_types=1);

namespace DGW\Models;

use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Model xử lý truy vấn và chuẩn hóa danh mục Taxonomy đa ngôn ngữ (thay thế inc/front/front-category.php)
 */
class CategoryModel
{
    /**
     * Cache kiểm tra taxonomy có sử dụng term meta 'term_lang' hay không trong một request
     *
     * @var array<string, bool>
     */
    private static array $hasTermLangCache = [];

    /**
     * Cache tĩnh dữ liệu danh mục trong một request để tránh lặp query khi dựng menu nhiều cấp
     *
     * @var array<string, array>
     */
    private static array $categoriesCache = [];

    /**
     * [2026-11-19] - @author: Kelvin - Lấy danh mục gốc theo ngôn ngữ hiện hành cho menu chính
     */
    public static function getCategories(string $taxonomy, ?string $lang = null): array
    {
        return self::fetchTerms($taxonomy, 0, '', $lang, 'menu-main-sub-1-item', false);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy danh mục theo parent kèm slug page cho menu con / sidebar
     */
    public static function getAllCategories(string $taxonomy, int $parent, string $page, ?string $lang = null): array
    {
        return self::fetchTerms($taxonomy, $parent, $page, $lang, '', true);
    }

    /**
     * [2026-11-19] - @author: Kelvin - Kiểm tra taxonomy có dùng meta term_lang hay không (memoized)
     */
    private static function hasTermLangMeta(string $taxonomy): bool
    {
        if (isset(self::$hasTermLangCache[$taxonomy])) {
            return self::$hasTermLangCache[$taxonomy];
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => 0,
            'number'     => 1,
            'fields'     => 'ids',
            'meta_key'   => 'term_lang',
        ]);

        self::$hasTermLangCache[$taxonomy] = (!empty($terms) && !is_wp_error($terms));

        return self::$hasTermLangCache[$taxonomy];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Truy vấn, chuẩn hóa và sắp xếp danh sách term theo thứ tự cate_order
     */
    private static function fetchTerms(
        string $taxonomy,
        int $parent,
        string $page,
        ?string $lang,
        string $defaultClass,
        bool $includePage
    ): array {
        $currentLang = $lang ?: (function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn');
        $cacheKey    = implode('|', [$taxonomy, (string) $parent, $page, $currentLang, $defaultClass, $includePage ? '1' : '0']);

        if (isset(self::$categoriesCache[$cacheKey])) {
            return self::$categoriesCache[$cacheKey];
        }

        $args = [
            'type'       => 'post',
            'number'     => 100,
            'taxonomy'   => $taxonomy,
            'hide_empty' => 0,
            'parent'     => $parent,
        ];

        if (self::hasTermLangMeta($taxonomy)) {
            $args['meta_query'] = [
                [
                    'key'     => 'term_lang',
                    'value'   => $currentLang,
                    'compare' => '=',
                ],
            ];
        }

        $categories = get_categories($args);
        $result     = [];

        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $term) {
                if (!$term instanceof WP_Term) {
                    continue;
                }

                $metaOrder = get_term_meta($term->term_id, 'cate_order', true);
                $metaLang  = get_term_meta($term->term_id, 'term_lang', true);
                $option    = get_option('option_' . $taxonomy . '_' . $term->term_id, []);
                $optionArr = is_array($option) ? $option : [];

                $name  = !empty($metaLang) ? $term->name : ($optionArr['cate_' . $currentLang] ?? $term->name);
                $order = ($metaOrder !== '') ? (int) $metaOrder : (int) ($optionArr['cate_order'] ?? 0);

                $item = [
                    'ID'    => $term->term_id,
                    'slug'  => $term->slug,
                    'name'  => $name,
                    'class' => $defaultClass,
                    'order' => $order,
                    'sub'   => '',
                ];

                if ($includePage) {
                    $item['page'] = $page;
                }

                $result[] = $item;
            }
        }

        usort($result, static function (array $a, array $b): int {
            return ((int) ($a['order'] ?? 0)) <=> ((int) ($b['order'] ?? 0));
        });

        self::$categoriesCache[$cacheKey] = $result;

        return $result;
    }
}
