<?php
/**
 * Menu and Taxonomy helper definitions
 * 
 * // 2026-09-15 - @author: Kelvin - Loại bỏ menu_mobile_list(), dùng chung menu_main_list() và tối ưu static memoization cache
 */

// 2026-09-15 - @author: Kelvin - Cache tĩnh menu trang chủ và hỗ trợ i18n
function menu_home_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        "industry"   => __("Industries", 'dgw'),
        "solution"   => __("Solutions", 'dgw'),
        "service"    => __("Service", 'dgw'),
        "activities" => __("Active", 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static memoization cache, chuẩn hóa key 'join-digiwin' dùng chung cho Desktop & Mobile
function menu_main_list()
{
    $lang = dgw_get_lang();
    static $menu_cache = array();

    if (isset($menu_cache[$lang])) {
        return $menu_cache[$lang];
    }

    $about_slug   = 'about-' . $lang;
    $contact_slug = 'contact-' . $lang;

    $arr = array(
        $about_slug => array(
            'name'  => "about",
            'class' => 'menu-main-item',
            'data'  => $about_slug,
        ),
        'cases' => array(
            'name'  => "cases",
            'class' => 'menu-main-item',
            'data'  => "cases",
        ),
        'solution' => array(
            'name'     => "solution",
            'class'    => 'menu-main-item',
            'data'     => "solution",
            'subClass' => 'menu-main-sub-1',
            'sub'      => getCategories('solutions_category'),
        ),
        'resource' => array(
            'name'     => "resource",
            'class'    => 'menu-main-item',
            'data'     => "resource",
            'subClass' => 'menu-main-sub-1',
            'sub'      => getCategories('resources_category'),
        ),
        'activities' => array(
            'name'     => "active",
            'class'    => 'menu-main-item',
            'data'     => 'activities',
            'subClass' => 'menu-main-sub-1',
            'sub'      => getCategories('active_category'),
        ),
        'join-digiwin' => array(
            'name'  => "join",
            'class' => 'menu-main-item',
            'data'  => "join-digiwin",
        ),
        'partner' => array(
            'name'  => "distribution",
            'class' => 'menu-main-item',
            'data'  => "partner",
        ),
        $contact_slug => array(
            'name'  => "contact",
            'class' => 'menu-main-item',
            'data'  => $contact_slug,
        ),
    );

    $menu_cache[$lang] = $arr;
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho danh sách chức vụ, tối ưu render loop
function member_position_list()
{
    static $list = null;
    if ($list !== null) {
        return $list;
    }
    $list = [
        "President"               => __('President', 'dgw'),
        "CEO"                     => __('CEO', 'dgw'),
        "Vice President"          => __('Vice President', 'dgw'),
        "General Director"        => __('General Director', 'dgw'),
        "Deputy General Director" => __('Deputy General Director', 'dgw'),
        "Director"                => __('Director', 'dgw'),
        "Deputy Director"         => __('Deputy Director', 'dgw'),
        "Secretary"               => __('Secretary', 'dgw'),
        "Assistant"               => __('Assistant', 'dgw'),
        "Manager"                 => __('Manager', 'dgw'),
        "Team Leader"             => __('Team Leader', 'dgw'),
        "Consultant"              => __('Consultant', 'dgw'),
        "Staff"                   => __('Staff', 'dgw'),
    ];
    return $list;
}

// 2026-09-15 - @author: Kelvin - Type guard an toàn và lấy chức vụ
function get_member_position($key)
{
    if (empty($key) || !is_scalar($key)) {
        return '';
    }
    $arr = member_position_list();
    return $arr[(string)$key] ?? '';
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho ngành nghề
function industry_sector_list()
{
    static $list = null;
    if ($list !== null) {
        return $list;
    }
    $list = [
        "Metal Processing"        => __('Metal Processing', 'dgw'),
        "Automotive Parts"        => __('Automotive Parts', 'dgw'),
        "Plastic Injection"       => __('Plastic Injection', 'dgw'),
        "Rubber"                  => __('Rubber', 'dgw'),
        "Electronic Parts"        => __('Electronic Parts', 'dgw'),
        "Wooden Furniture"        => __('Wooden Furniture', 'dgw'),
        "F&B"                     => __('F&B', 'dgw'),
        "Textiles and Garments"   => __('Textiles and Garments', 'dgw'),
        "Shoes and Leather"       => __('Shoes and Leather', 'dgw'),
        "Packaging"               => __('Packaging', 'dgw'),
        "Wires and Fiber Optics"  => __('Wires and Fiber Optics', 'dgw'),
        "Pharmaceuticals"         => __('Pharmaceuticals', 'dgw'),
        "Chemicals"               => __('Chemicals', 'dgw'),
        "Machinery Manufacturing" => __('Machinery Manufacturing', 'dgw'),
        "IoT / Automation"        => __('IoT / Automation', 'dgw'),
        "IT"                      => __('IT', 'dgw'),
        "Associations"            => __('Associations', 'dgw'),
        "Services"                => __('Services', 'dgw'),
        "Other Manufacturing"     => __('Other Manufacturing', 'dgw'),
        "Other"                   => __('Other', 'dgw'),
    ];
    return $list;
}

// 2026-09-15 - @author: Kelvin - Type guard an toàn và lấy ngành nghề
function get_industry_sector($key)
{
    if (empty($key) || !is_scalar($key)) {
        return '';
    }
    $arr = industry_sector_list();
    return $arr[(string)$key] ?? '';
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho phòng ban
function department_list()
{
    static $list = null;
    if ($list !== null) {
        return $list;
    }
    $list = [
        "Board of Directors"     => __('Board of Directors', 'dgw'),
        "R&D"                    => __('R&D', 'dgw'),
        "Sales"                  => __('Sales', 'dgw'),
        "Purchasing"             => __('Purchasing', 'dgw'),
        "Inventory"              => __('Inventory', 'dgw'),
        "Production"             => __('Production', 'dgw'),
        "Quality Control"        => __('Quality Control', 'dgw'),
        "Finance and Accounting" => __('Finance and Accounting', 'dgw'),
        "Marketing"              => __('Marketing', 'dgw'),
        "IT"                     => __('IT', 'dgw'),
        "HR"                     => __('HR', 'dgw'),
        "Import and Export"      => __('Import and Export', 'dgw'),
        "Other"                  => __('Other', 'dgw'),
    ];
    return $list;
}

// 2026-09-15 - @author: Kelvin - Type guard an toàn và lấy tên phòng ban
function get_department_name($key)
{
    if (empty($key) || !is_scalar($key)) {
        return '';
    }
    $arr = department_list();
    return $arr[(string)$key] ?? '';
}
