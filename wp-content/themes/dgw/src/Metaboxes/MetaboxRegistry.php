<?php
declare(strict_types=1);

namespace DGW\Metaboxes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Danh mục cấu hình tập trung cho toàn bộ 15 Metaboxes của theme
 */
class MetaboxRegistry
{
    private const OPTION_NAME = 'main_controller_options';

    /**
     * @var array<string, bool>
     */
    private const DEFAULT_OPTIONS = [
        'metabox_source'     => true,
        'metabox_view'       => true,
        'metabox_web'        => true,
        'metabox_download'   => true,
        'metabox_language'   => true,
        'metabox_industries' => false,
        'metabox_service'    => false,
        'metabox_solution'   => false,
        'metabox_active'     => false,
        'metabox_seo'        => false,
        'metabox_order'      => true,
        'metabox_home'       => true,
        'metabox_link'       => true,
        'metabox_sidebar'    => true,
        'metabox_youtube'    => true,
    ];

    /**
     * [20/11/2026] - Đọc cấu hình và đăng ký các Metabox đang bật (chỉ chạy trong WP-Admin)
     */
    public static function register(): void
    {
        if (!is_admin()) {
            return;
        }

        $savedOptions = get_option(self::OPTION_NAME, self::DEFAULT_OPTIONS);
        $options      = wp_parse_args(is_array($savedOptions) ? $savedOptions : [], self::DEFAULT_OPTIONS);

        self::registerEnabled($options);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Bản đồ cấu hình Metabox theo key trong main_controller_options
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getDefinitions(): array
    {
        return [
            'metabox_youtube' => [
                'id'        => 'admin-metabox-youtube',
                'title'     => 'Youtube video link',
                'screens'   => ['solutions', 'services', 'industries', 'active', 'casestudies', 'resources'],
                'type'      => 'text_fields',
                'row_class' => 'row-one-column',
                'fields'    => [
                    [
                        'name'              => 'txt-youtube',
                        'meta_key'          => '_metabox_youtube',
                        'label'             => '',
                        'delete_when_empty' => true,
                    ],
                ],
            ],
            'metabox_source' => [
                'id'        => 'admin-metabox-source',
                'title'     => 'Google Drive file download link',
                'screens'   => ['resources'],
                'type'      => 'text_fields',
                'row_class' => 'row-one-column',
                'fields'    => [
                    [
                        'name'              => 'txt-source',
                        'meta_key'          => '_metabox_source',
                        'label'             => '',
                        'delete_when_empty' => true,
                    ],
                ],
            ],
            'metabox_view' => [
                'id'          => 'admin-metabox-view',
                'title_parts' => ['View', 'Like'],
                'screens'     => ['post', 'solutions', 'services', 'industries', 'active', 'resources', 'casestudies', 'joinus'],
                'type'        => 'text_fields',
                'row_class'   => 'row-three-column',
                'fields'      => [
                    [
                        'name'        => 'txt-view',
                        'meta_key'    => '_metabox_view',
                        'label'       => 'View',
                        'input_class' => 'my-input type-number',
                    ],
                    [
                        'name'        => 'txt-like',
                        'meta_key'    => '_metabox_like',
                        'label'       => 'Like',
                        'input_class' => 'my-input type-number',
                    ],
                ],
            ],
            'metabox_link' => [
                'id'        => 'admin-metabox-link',
                'title'     => 'Link',
                'screens'   => ['slider'],
                'type'      => 'text_fields',
                'row_class' => 'row-two-column',
                'fields'    => [
                    [
                        'name'              => 'txt-link',
                        'meta_key'          => '_metabox_link',
                        'label'             => '',
                        'delete_when_empty' => true,
                    ],
                ],
            ],
            'metabox_web' => [
                'id'        => 'admin-metabox-web',
                'title'     => 'Web Site And FreeBook',
                'screens'   => ['advertising'],
                'type'      => 'text_fields',
                'row_class' => 'row-two-column',
                'fields'    => [
                    [
                        'name'     => 'txt-web',
                        'meta_key' => '_metabox_web',
                        'label'    => 'Web Site',
                    ],
                    [
                        'name'     => 'txt-freebook',
                        'meta_key' => '_metabox_freebook',
                        'label'    => 'Free Book',
                    ],
                ],
            ],
            'metabox_language' => [
                'id'      => 'admin-metabox-langguage',
                'title'   => 'Language',
                'screens' => ['post', 'joinus', 'solutions', 'services', 'industries', 'active', 'casestudies', 'resources', 'downloads', 'advertising', 'slider'],
                'type'    => 'radio_lang',
            ],
            'metabox_download' => [
                'id'      => 'admin-metabox-download',
                'title'   => 'Data Download',
                'screens' => ['downloads'],
                'type'    => 'download_file',
            ],
            'metabox_industries' => [
                'id'      => 'admin-metabox-industries',
                'title'   => 'Industries',
                'screens' => ['industries'],
                'type'    => 'editors',
                'fields'  => [
                    [
                        'name'     => 'txt-challenge',
                        'meta_key' => '_industry_challenge',
                        'label'    => 'Management Challenges',
                    ],
                    [
                        'name'     => 'txt-solution',
                        'meta_key' => '_industry_solution',
                        'label'    => 'Solution',
                    ],
                ],
            ],
            'metabox_service' => [
                'id'            => 'admin-metabox-service',
                'title'         => 'Services',
                'screens'       => ['services'],
                'type'          => 'multilang_tabs',
                'meta_prefix'   => 'service',
                'name_label'    => 'Service Name',
                'content_label' => 'Service Content',
            ],
            'metabox_solution' => [
                'id'      => 'admin-metabox-solution',
                'title'   => 'Solutions',
                'screens' => ['solutions'],
                'type'    => 'editors',
                'fields'  => [
                    [
                        'name'     => 'txt-value',
                        'meta_key' => '_solution_value',
                        'label'    => 'Solution Value',
                    ],
                    [
                        'name'     => 'txt-features',
                        'meta_key' => '_solution_features',
                        'label'    => 'Solution Features',
                    ],
                    [
                        'name'     => 'txt-service',
                        'meta_key' => '_solution_service',
                        'label'    => 'Advisory Services',
                    ],
                ],
            ],
            'metabox_active' => [
                'id'            => 'admin-metabox-active',
                'title'         => 'Active',
                'screens'       => ['active'],
                'type'          => 'multilang_tabs',
                'meta_prefix'   => 'active',
                'name_label'    => 'Active Name',
                'content_label' => 'Active Content',
            ],
            'metabox_home' => [
                'id'         => 'admin-metabox-home',
                'title'      => 'Show In Home Page',
                'label'      => 'Show In Home Page',
                'context'    => 'side',
                'screens'    => ['post', 'resources', 'solutions', 'casestudies', 'active', 'services', 'industries'],
                'type'       => 'checkbox',
                'input_name' => 'ckd-show',
                'meta_key'   => '_metabox_home',
            ],
            'metabox_seo' => [
                'id'      => 'tw-metabox-seo',
                'title'   => 'Seo',
                'screens' => ['post', 'slider'],
                'type'    => 'seo',
                'fields'  => [
                    [
                        'name'     => 'txt_title',
                        'meta_key' => '_seo_title',
                    ],
                    [
                        'name'     => 'txt_key',
                        'meta_key' => '_seo_key',
                    ],
                    [
                        'name'     => 'txt_description',
                        'meta_key' => '_seo_description',
                    ],
                ],
            ],
            'metabox_order' => [
                'id'      => 'admin-metabox-order',
                'title'   => 'Show Order',
                'context' => 'side',
                'screens' => ['post', 'slider', 'joinus', 'solutions', 'services', 'industries', 'active', 'resources', 'downloads', 'casestudies', 'advertising'],
                'type'    => 'order',
            ],
            'metabox_sidebar' => [
                'id'         => 'admin-metabox-sidebar',
                'title'      => 'Show in Sidebar',
                'label'      => 'Show in Sidebar',
                'context'    => 'side',
                'screens'    => ['resources', 'solutions', 'casestudies', 'active', 'joinus'],
                'type'       => 'checkbox',
                'input_name' => 'ckd-sidebar',
                'meta_key'   => '_metabox_sidebar',
            ],
        ];
    }

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các Metabox đang bật (chỉ chạy trong WP-Admin)
     */
    public static function registerEnabled(array $options): void
    {
        if (!is_admin()) {
            return;
        }

        foreach (self::getDefinitions() as $optionKey => $config) {
            if (!empty($options[$optionKey])) {
                new ConfigurableMetabox((string) $config['id'], $config);
            }
        }
    }
}
