<?php
declare(strict_types=1);

use DGW\Metaboxes\MetaboxRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Bộ điều phối trung tâm khởi tạo Metaboxes theo chuẩn PSR-4 (chỉ chạy trong WP-Admin)
 */
class Metabox_Main
{
    private string $_controller_name = 'main_controller_options';

    /**
     * @var array<string, bool>
     */
    private array $_controller_options = [];

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo các Metabox được bật khi đang ở WP-Admin
     */
    public function __construct()
    {
        if (!is_admin()) {
            return;
        }

        $defaultOption = [
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

        $savedOptions              = get_option($this->_controller_name, $defaultOption);
        $this->_controller_options = wp_parse_args(is_array($savedOptions) ? $savedOptions : [], $defaultOption);

        MetaboxRegistry::registerEnabled($this->_controller_options);
    }
}
