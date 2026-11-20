<?php
declare(strict_types=1);

namespace DGW\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Danh mục cấu hình tập trung cho toàn bộ Taxonomies và Tags của theme
 */
class TaxonomyRegistry
{
    /**
     * [2026-11-20] - @author: Kelvin - Bản đồ cấu hình 8 Category Taxonomies đa ngôn ngữ
     *
     * @return array<string, array{taxonomy: string, post_type: string, slug?: string}>
     */
    public static function getCategoryDefinitions(): array
    {
        return [
            'taxonomy_advertising' => [
                'taxonomy'  => 'advertising_category',
                'post_type' => 'advertising',
            ],
            'taxonomy_industries' => [
                'taxonomy'  => 'industries_category',
                'post_type' => 'industries',
            ],
            'taxonomy_solution' => [
                'taxonomy'  => 'solutions_category',
                'post_type' => 'solutions',
                'slug'      => 'solutions-category',
            ],
            'taxonomy_resource' => [
                'taxonomy'  => 'resources_category',
                'post_type' => 'resources',
            ],
            'taxonomy_active' => [
                'taxonomy'  => 'active_category',
                'post_type' => 'active',
            ],
            'taxonomy_casestudies' => [
                'taxonomy'  => 'casestudies_category',
                'post_type' => 'casestudies',
            ],
            'taxonomy_services' => [
                'taxonomy'  => 'services_category',
                'post_type' => 'services',
            ],
            'taxonomy_joinus' => [
                'taxonomy'  => 'joinus_category',
                'post_type' => 'joinus',
            ],
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Bản đồ cấu hình 3 Tag Taxonomies
     *
     * @return array<string, array{taxonomy: string, post_type: string, label: string, option_prefix: string, field_prefix: string, hierarchical_rewrite: bool}>
     */
    public static function getTagDefinitions(): array
    {
        return [
            'tags_advertising' => [
                'taxonomy'             => 'advertising_tags',
                'post_type'            => 'advertising',
                'label'                => 'Language',
                'option_prefix'        => 'option_Tag_advertising_',
                'field_prefix'         => 'cate_advertising',
                'hierarchical_rewrite' => true,
            ],
            'tags_solution' => [
                'taxonomy'             => 'solutions_tags',
                'post_type'            => 'solutions',
                'label'                => 'Product',
                'option_prefix'        => 'option_solutions_tag_',
                'field_prefix'         => 'cate_solution',
                'hierarchical_rewrite' => false,
            ],
            'tags_casestudies' => [
                'taxonomy'             => 'casestudies_tags',
                'post_type'            => 'casestudies',
                'label'                => 'Product',
                'option_prefix'        => 'option_casestudies_tag_',
                'field_prefix'         => 'cate_solution',
                'hierarchical_rewrite' => false,
            ],
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các Taxonomies và Tags đang bật trong main_taxonomy_options
     */
    public static function registerEnabled(array $options): void
    {
        foreach (self::getCategoryDefinitions() as $optionKey => $config) {
            if (!empty($options[$optionKey])) {
                new BaseTranslationTaxonomy(
                    $config['taxonomy'],
                    $config['post_type'],
                    $config['slug'] ?? $config['taxonomy']
                );
            }
        }

        foreach (self::getTagDefinitions() as $optionKey => $config) {
            if (!empty($options[$optionKey])) {
                new BaseTagOptionTaxonomy(
                    $config['taxonomy'],
                    $config['post_type'],
                    $config['label'],
                    $config['option_prefix'],
                    $config['field_prefix'],
                    $config['hierarchical_rewrite']
                );
            }
        }
    }
}
