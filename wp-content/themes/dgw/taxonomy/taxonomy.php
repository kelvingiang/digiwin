<?php
declare(strict_types=1);

use DGW\Taxonomies\TaxonomyRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Bộ điều phối trung tâm khởi tạo Taxonomies và Tags theo chuẩn PSR-4
 */
class Taxonomy_Main
{
    private string $_taxonomy_name = 'main_taxonomy_options';

    /**
     * @var array<string, bool>
     */
    private array $_taxonomy_options = [];

    /**
     * [2026-11-20] - @author: Kelvin - Đọc cấu hình main_taxonomy_options và đăng ký các Taxonomy đang bật
     */
    public function __construct()
    {
        $defaultOption = [
            'tags_advertising'     => false,
            'taxonomy_industries'  => false,
            'tags_solution'        => false,
            'tags_casestudies'     => true,
            'taxonomy_casestudies' => true,
            'taxonomy_advertising' => true,
            'taxonomy_solution'    => true,
            'taxonomy_resource'    => true,
            'taxonomy_active'      => true,
            'taxonomy_services'    => true,
            'taxonomy_joinus'      => true,
        ];

        $savedOptions            = get_option($this->_taxonomy_name, $defaultOption);
        $this->_taxonomy_options = wp_parse_args(is_array($savedOptions) ? $savedOptions : [], $defaultOption);

        TaxonomyRegistry::registerEnabled($this->_taxonomy_options);
    }
}
