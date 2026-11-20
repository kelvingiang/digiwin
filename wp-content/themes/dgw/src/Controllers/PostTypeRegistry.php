<?php
declare(strict_types=1);

namespace DGW\Controllers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Danh mục cấu hình tập trung cho toàn bộ Custom Post Types thay thế 8 file controller-* lặp code
 */
class PostTypeRegistry
{
    /**
     * [2026-11-20] - @author: Kelvin - Trả về bản đồ cấu hình CPT theo key option trong main_controller_options
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefinitions(): array
    {
        return [
            'controller_active' => [
                'post_type'           => 'active',
                'name'                => 'Active',
                'singular_name'       => 'Active',
                'menu_name'           => 'Active',
                'exclude_from_search' => false, // Cho phép Divi Visual Builder và REST API truy vấn preview
                'has_archive'         => true,
                'menu_position'       => 6,
                'supports'            => ['thumbnail', 'editor', 'title', 'comments'],
                'show_in_rest'        => true,
            ],
            'controller_case_study' => [
                'post_type'     => 'casestudies',
                'name'          => 'Case Studies',
                'singular_name' => 'Case Studies',
                'menu_name'     => 'Case Studies',
                'has_archive'   => true,
                'menu_position' => 8,
                'supports'      => ['editor', 'thumbnail', 'title', 'comments'],
            ],
            'controller_industries' => [
                'post_type'      => 'industries',
                'name'           => 'Industries',
                'singular_name'  => 'Industries',
                'menu_name'      => 'Industries',
                'has_archive'    => false,
                'menu_position'  => 5,
                'supports'       => ['thumbnail', 'editor', 'title'],
                'unset_columns'  => ['date', 'categories', 'comments'],
                'custom_columns' => [
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],
            'controller_join_us' => [
                'post_type'      => 'joinus',
                'name'           => 'Join Digiwin',
                'singular_name'  => 'Join Digiwin',
                'menu_name'      => 'Join Digiwin',
                'has_archive'    => true,
                'menu_position'  => 4,
                'supports'       => ['title', 'thumbnail', 'editor'],
                'custom_columns' => [
                    'language'    => 'Language',
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],
            'controller_resources' => [
                'post_type'     => 'resources',
                'name'          => 'Resources',
                'singular_name' => 'Resources',
                'menu_name'     => 'Resources',
                'has_archive'   => true,
                'menu_position' => 8,
                'supports'      => ['thumbnail', 'editor', 'title', 'comments'],
            ],
            'controller_services' => [
                'post_type'        => 'services',
                'name'             => 'Services',
                'singular_name'    => 'Services',
                'menu_name'        => 'Services',
                'has_archive'      => false,
                'menu_position'    => 7,
                'supports'         => ['thumbnail', 'editor', 'title'],
                'order_query_keys' => ['order', '_metabox_order'],
            ],
            'controller_solutions' => [
                'post_type'     => 'solutions',
                'name'          => 'Solutions',
                'singular_name' => 'Solutions',
                'menu_name'     => 'Solutions',
                'has_archive'   => true,
                'menu_position' => 4,
                'supports'      => ['title', 'thumbnail', 'editor', 'comments'],
            ],
            'controller_slider' => [
                'post_type'      => 'slider',
                'name'           => 'Slider',
                'name_suffix'    => ' 1300 x 430',
                'singular_name'  => 'Slider',
                'menu_name'      => 'Slider',
                'has_archive'    => true,
                'menu_position'  => 6,
                'supports'       => ['title', 'thumbnail'],
                'unset_columns'  => ['home', 'categories', 'comments'],
                'custom_columns' => [
                    'order'       => 'Show Order',
                    'create-date' => 'Create Date',
                ],
            ],
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các CPT đang được bật trong main_controller_options
     */
    public static function registerEnabled(array $options): void
    {
        foreach (self::getDefinitions() as $optionKey => $config) {
            if (!empty($options[$optionKey])) {
                new BasePostTypeController((string) $config['post_type'], $config);
            }
        }
    }
}
