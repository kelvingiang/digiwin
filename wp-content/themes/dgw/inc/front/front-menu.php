<?php
/**
 * Menu and Taxonomy helper definitions
 * 
 * // 2026-09-15 - @author: Kelvin - Loại bỏ menu_mobile_list(), dùng chung menu_main_list() và tối ưu static memoization cache
 * // 2026-11-05 - Cập nhật getCategories() truyền đúng ngôn ngữ hiện hành ($lang)
 */

// 2026-09-15 - @author: Kelvin - Cache tĩnh menu trang chủ và hỗ trợ i18n
// 2026-11-05 - Cache theo ngôn ngữ hiện hành ($lang)
function menu_home_list()
{
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn';
    static $arr = array();
    if (isset($arr[$lang])) {
        return $arr[$lang];
    }
    $arr[$lang] = array(
        "industry"   => __("Industries", 'dgw'),
        "solution"   => __("Solutions", 'dgw'),
        "service"    => __("Service", 'dgw'),
        "activities" => __("Active", 'dgw'),
    );
    return $arr[$lang];
}

// 2026-09-15 - @author: Kelvin - Thêm static memoization cache, chuẩn hóa key 'join-digiwin' dùng chung cho Desktop & Mobile
/**
 * 2026-10-06 | Gia Minh
 * 2026-11-05 | Lấy getCategories() theo đúng ngôn ngữ hiện hành ($lang)
 * Menu chính hỗ trợ đa ngôn ngữ chuẩn gettext i18n và cache tối ưu hiệu suất
 *
 * @return array
 */
function menu_main_list()
{
    $lang = function_exists('dgw_get_lang') ? dgw_get_lang() : (str_contains(get_locale(), 'zh') ? 'cn' : 'vn');
    static $menu_cache = array();

    if (isset($menu_cache[$lang])) {
        return $menu_cache[$lang];
    }

    $about_slug   = 'about-' . $lang;
    $contact_slug = 'contact-' . $lang;

    $arr = array(
        $about_slug => array(
            'name'  => __('About Digiwin', 'dgw'),
            'class' => 'menu-main-item',
            'data'  => $about_slug,
        ),
        'cases' => array(
            'name'     => __('Case Studies', 'dgw'),
            'class'    => 'menu-main-item',
            'data'     => 'cases',
            'subClass' => 'menu-main-sub-1',
            'sub'      => function_exists('getCategories') ? getCategories('casestudies_category', $lang) : array(),
        ),
        'solution' => array(
            'name'     => __('Solutions', 'dgw'),
            'class'    => 'menu-main-item',
            'data'     => 'solution',
            'subClass' => 'menu-main-sub-1',
            'sub'      => function_exists('getCategories') ? getCategories('solutions_category', $lang) : array(),
        ),
        'resource' => array(
            'name'     => __('Resources', 'dgw'),
            'class'    => 'menu-main-item',
            'data'     => 'resource',
            'subClass' => 'menu-main-sub-1',
            'sub'      => function_exists('getCategories') ? getCategories('resources_category', $lang) : array(),
        ),
        'activities' => array(
            'name'     => __('Activities', 'dgw'),
            'class'    => 'menu-main-item',
            'data'     => 'activities',
            'subClass' => 'menu-main-sub-1',
            'sub'      => function_exists('getCategories') ? getCategories('active_category', $lang) : array(),
        ),
        'join-digiwin' => array(
            'name'  => __('Join Digiwin', 'dgw'),
            'class' => 'menu-main-item',
            'data'  => 'join-digiwin',
        ),
        'partner' => array(
            'name'  => __('Partners', 'dgw'),
            'class' => 'menu-main-item',
            'data'  => 'partner',
        ),
        $contact_slug => array(
            'name'  => __('Contact Us', 'dgw'),
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
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('General Director', 'dgw'),
        '2' => __('Deputy General Director', 'dgw'),
        '3' => __('Director of Business Development Center', 'dgw'),
        '4' => __('Technical Director', 'dgw'),
        '5' => __('Director of Solutions and Consulting Center', 'dgw'),
        '6' => __('Staff', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho danh sách cấp học vấn
function education_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Ph.D', 'dgw'),
        '2' => __('Master', 'dgw'),
        '3' => __('University', 'dgw'),
        '4' => __('Colleges', 'dgw'),
        '5' => __('Intermediate', 'dgw'),
        '6' => __('High School', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho mức kinh nghiệm
function experience_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('No experience', 'dgw'),
        '2' => __('1-2 years', 'dgw'),
        '3' => __('3-4 years', 'dgw'),
        '4' => __('5-6 years', 'dgw'),
        '5' => __('Over 7 years', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho loại hình hoạt động
function activities_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Enterprise Visits', 'dgw'),
        '2' => __('Lectures and Seminars', 'dgw'),
        '3' => __('Association Activities', 'dgw'),
        '4' => __('Customer Sharing', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho danh sách phòng ban
function department_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Business', 'dgw'),
        '2' => __('Consulting', 'dgw'),
        '3' => __('Services', 'dgw'),
        '4' => __('Integrated Planning Office', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho danh mục tài nguyên
function resources_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Document Download', 'dgw'),
        '2' => __('Digital Transformation Knowledge Base', 'dgw'),
        '3' => __('Video Area', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho danh mục dịch vụ
function services_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Course Information', 'dgw'),
        '2' => __('Maintenance Services', 'dgw'),
        '3' => __('Industry-University Services', 'dgw'),
    );
    return $arr;
}

// 2026-09-15 - @author: Kelvin - Thêm static cache cho hình thức làm việc
function working_form_list()
{
    static $arr = null;
    if ($arr !== null) {
        return $arr;
    }
    $arr = array(
        '1' => __('Official employee', 'dgw'),
        '2' => __('Part time', 'dgw'),
        '3' => __('Internship', 'dgw'),
    );
    return $arr;
}
