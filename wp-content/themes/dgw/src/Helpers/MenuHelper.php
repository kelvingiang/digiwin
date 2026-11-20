<?php
declare(strict_types=1);

namespace DGW\Helpers;

use DGW\Models\CategoryModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Helper cung cấp cấu trúc menu và các danh sách tùy chọn đa ngôn ngữ (thay thế inc/front/front-menu.php)
 */
class MenuHelper
{
    /**
     * [2026-11-19] - @author: Kelvin - Danh sách menu nhanh trên trang chủ (có static cache theo ngôn ngữ)
     */
    public static function homeList(): array
    {
        $lang = function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn';
        static $cache = [];

        if (isset($cache[$lang])) {
            return $cache[$lang];
        }

        $cache[$lang] = [
            'industry'   => __('Industries', 'dgw'),
            'solution'   => __('Solutions', 'dgw'),
            'service'    => __('Service', 'dgw'),
            'activities' => __('Active', 'dgw'),
        ];

        return $cache[$lang];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cấu trúc menu chính dùng chung cho Desktop & Mobile (có static cache theo ngôn ngữ)
     */
    public static function mainList(): array
    {
        $lang = function_exists('dgw_get_lang')
            ? (string) dgw_get_lang()
            : (strpos((string) get_locale(), 'zh') !== false ? 'cn' : 'vn');

        static $menuCache = [];

        if (isset($menuCache[$lang])) {
            return $menuCache[$lang];
        }

        $aboutSlug   = 'about-' . $lang;
        $contactSlug = 'contact-' . $lang;

        $menuCache[$lang] = [
            $aboutSlug => [
                'name'  => __('About Digiwin', 'dgw'),
                'class' => 'menu-main-item',
                'data'  => $aboutSlug,
            ],
            'cases' => [
                'name'     => __('Case Studies', 'dgw'),
                'class'    => 'menu-main-item',
                'data'     => 'cases',
                'subClass' => 'menu-main-sub-1',
                'sub'      => CategoryModel::getCategories('casestudies_category', $lang),
            ],
            'solution' => [
                'name'     => __('Solutions', 'dgw'),
                'class'    => 'menu-main-item',
                'data'     => 'solution',
                'subClass' => 'menu-main-sub-1',
                'sub'      => CategoryModel::getCategories('solutions_category', $lang),
            ],
            'resource' => [
                'name'     => __('Resources', 'dgw'),
                'class'    => 'menu-main-item',
                'data'     => 'resource',
                'subClass' => 'menu-main-sub-1',
                'sub'      => CategoryModel::getCategories('resources_category', $lang),
            ],
            'activities' => [
                'name'     => __('Activities', 'dgw'),
                'class'    => 'menu-main-item',
                'data'     => 'activities',
                'subClass' => 'menu-main-sub-1',
                'sub'      => CategoryModel::getCategories('active_category', $lang),
            ],
            'join-digiwin' => [
                'name'  => __('Join Digiwin', 'dgw'),
                'class' => 'menu-main-item',
                'data'  => 'join-digiwin',
            ],
            'partner' => [
                'name'  => __('Partners', 'dgw'),
                'class' => 'menu-main-item',
                'data'  => 'partner',
            ],
            $contactSlug => [
                'name'  => __('Contact Us', 'dgw'),
                'class' => 'menu-main-item',
                'data'  => $contactSlug,
            ],
        ];

        return $menuCache[$lang];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách chức vụ cho form thành viên frontend
     */
    public static function memberPositions(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('General Director', 'dgw'),
                '2' => __('Deputy General Director', 'dgw'),
                '3' => __('Director of Business Development Center', 'dgw'),
                '4' => __('Technical Director', 'dgw'),
                '5' => __('Director of Solutions and Consulting Center', 'dgw'),
                '6' => __('Staff', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách trình độ học vấn
     */
    public static function educations(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Ph.D', 'dgw'),
                '2' => __('Master', 'dgw'),
                '3' => __('University', 'dgw'),
                '4' => __('Colleges', 'dgw'),
                '5' => __('Intermediate', 'dgw'),
                '6' => __('High School', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách số năm kinh nghiệm
     */
    public static function experiences(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('No experience', 'dgw'),
                '2' => __('1-2 years', 'dgw'),
                '3' => __('3-4 years', 'dgw'),
                '4' => __('5-6 years', 'dgw'),
                '5' => __('Over 7 years', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách loại hình hoạt động
     */
    public static function activities(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Enterprise Visits', 'dgw'),
                '2' => __('Lectures and Seminars', 'dgw'),
                '3' => __('Association Activities', 'dgw'),
                '4' => __('Customer Sharing', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách phòng ban cho form thành viên frontend
     */
    public static function departments(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Business', 'dgw'),
                '2' => __('Consulting', 'dgw'),
                '3' => __('Services', 'dgw'),
                '4' => __('Integrated Planning Office', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh mục tài nguyên
     */
    public static function resources(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Document Download', 'dgw'),
                '2' => __('Digital Transformation Knowledge Base', 'dgw'),
                '3' => __('Video Area', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh mục dịch vụ
     */
    public static function services(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Course Information', 'dgw'),
                '2' => __('Maintenance Services', 'dgw'),
                '3' => __('Industry-University Services', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách hình thức làm việc
     */
    public static function workingForms(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                '1' => __('Official employee', 'dgw'),
                '2' => __('Part time', 'dgw'),
                '3' => __('Internship', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Danh sách ngành nghề cho form thành viên frontend
     */
    public static function industrySectors(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                'Metal Processing'        => __('Metal Processing', 'dgw'),
                'Automotive Parts'        => __('Automotive Parts', 'dgw'),
                'Plastic Injection'       => __('Plastic Injection', 'dgw'),
                'Rubber'                  => __('Rubber', 'dgw'),
                'Electronic Parts'        => __('Electronic Parts', 'dgw'),
                'Wooden Furniture'        => __('Wooden Furniture', 'dgw'),
                'F&B'                     => __('F&B', 'dgw'),
                'Textiles and Garments'   => __('Textiles and Garments', 'dgw'),
                'Shoes and Leather'       => __('Shoes and Leather', 'dgw'),
                'Packaging'               => __('Packaging', 'dgw'),
                'Wires and Fiber Optics'  => __('Wires and Fiber Optics', 'dgw'),
                'Pharmaceuticals'         => __('Pharmaceuticals', 'dgw'),
                'Chemicals'               => __('Chemicals', 'dgw'),
                'Machinery Manufacturing' => __('Machinery Manufacturing', 'dgw'),
                'IoT / Automation'        => __('IoT / Automation', 'dgw'),
                'IT'                      => __('IT', 'dgw'),
                'Associations'            => __('Associations', 'dgw'),
                'Services'                => __('Services', 'dgw'),
                'Other Manufacturing'     => __('Other Manufacturing', 'dgw'),
                'Other'                   => __('Other', 'dgw'),
            ];
        }
        return $cache;
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy tên ngành nghề theo key
     */
    public static function getIndustrySector(string $key): string
    {
        $list = self::industrySectors();
        return $list[$key] ?? '';
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy tên chức vụ theo key
     */
    public static function getMemberPosition(string $key): string
    {
        $list = self::memberPositions();
        return $list[$key] ?? '';
    }

    /**
     * [2026-11-19] - @author: Kelvin - Lấy tên phòng ban theo key
     */
    public static function getDepartmentName(string $key): string
    {
        $list = self::departments();
        return $list[$key] ?? '';
    }
}
