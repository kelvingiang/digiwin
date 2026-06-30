<?php
$lang = dgw_get_lang(); // 預設值 en
$contact = 'contact-' . $lang;
$about   =  'about-' . $lang;


function menu_home_list()
{
    $arr = array(
        "industry" => "Industries",
        "solution" => "Solutions",
        "service" => "Service",
        "activities" => "Active",
    );
    return $arr;
}

function menu_mobile_list()
{
    $arr = array(
        $GLOBALS['about'] => "about",
        "cases" => "cases",
        "solution" => "solution",
        "resource" => "resource",
        "activities" => "active",
        "join-digiwin" => "join",
        "partner" => "distribution",
        $GLOBALS['contact'] => "contact"
    );
    return $arr;
}

function menu_main_list()
{
    // THIS ARRAY KEY APPLY LINK OF WEB 
    $arr = array(
        $GLOBALS['about'] => array(
            'name' => "about",
            'class' => 'menu-main-item', // neu co sub menu phai them sub Class
            'data' => 'about-' . dgw_get_lang(),
            // 'subClass' => 'menu-main-sub-1',
            // 'sub' => array(),
            //'sub' => $homeArr,
        ),
        'cases' => array(
            'name' => "cases",
            'class' => 'menu-main-item',
            'data' => "cases",
            //'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            // 'sub' => array(),
            //'sub' => getCategories('casestudies_category'),
        ),
        // 'industry' => array(
        //     'name' => "Industries",
        //     'class' => 'menu-main-item ',
        //     'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
        //     // 'sub' => 'getCategories('industries_category')',
        //     'sub' => array(),
        // ),
        'solution' => array(
            'name' => "solution",
            'class' => 'menu-main-item',
            'data' => "solution",
            'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            'sub' => getCategories('solutions_category'),
        ),
        // 'service' => array(
        //     'name' => "Service",
        //     'class' => 'menu-main-item',
        //     'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
        //     'sub' => getCategories('services_category'),
        // ),
        'resource' => array(
            'name' => "resource",
            'class' => 'menu-main-item',
            'data' => "resource",
            'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            'sub' => getCategories('resources_category'),
        ),
        'activities' => array(
            'name' => "active",
            'class' => 'menu-main-item',
            'data' => 'activities',
            'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            'sub' => getCategories('active_category'),
        ),
        // 'join-digiwin' => array(
        //     'name' => "Join Digiwin",
        //     'class' => 'menu-main-item',
        //     'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
        //     'sub' => getCategories('joinus_category'),
        // ),
        'join' => array(
            'name' => "join",
            'class' => 'menu-main-item',
            'data' => "join-digiwin",
            //'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            // 'sub' => array(),
            //'sub' => getCategories('casestudies_category'),
        ),
        // 'join-digiwin/cate/98/tag/' => array(
        'partner' => array(
            'name' => "distribution",
            'class' => 'menu-main-item',
            'data' => "partner",
            //'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            // 'sub' => array(),
            //'sub' => getCategories('casestudies_category'),
        ),

        $GLOBALS['contact'] => array(
            'name' => "contact",
            'class' => 'menu-main-item',
            'data' => 'contact-' . dgw_get_lang(),
            // 'sub' => array()
        ),
    );
    return $arr;
}


// 2026-03-06: them function cho member position
function member_position_list()
{
    return [
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
}

function get_member_position($key)
{
    $arr = member_position_list();
    return $arr[$key] ?? '';
}


function industry_sector_list()
{
    return [
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
}

function get_industry_sector($key)
{
    $arr = industry_sector_list();
    return $arr[$key] ?? '';
}


function department_list()
{
    return [
        "Board of Directors" => __('Board of Directors', 'dgw'),
        "R&D"                => __('R&D', 'dgw'),
        "Sales"              => __('Sales', 'dgw'),
        "Purchasing"         => __('Purchasing', 'dgw'),
        "Inventory"          => __('Inventory', 'dgw'),
        "Production"         => __('Production', 'dgw'),
        "Quality Control"    => __('Quality Control', 'dgw'),
        "Finance and Accounting" => __('Finance and Accounting', 'dgw'),
        "Marketing"          => __('Marketing', 'dgw'),
        "IT"                 => __('IT', 'dgw'),
        "HR"                 => __('HR', 'dgw'),
        "Import and Export"      => __('Import and Export', 'dgw'),
        "Other"              => __('Other', 'dgw'),
    ];
}


function get_department_name($key)
{
    $arr = department_list();
    return $arr[$key] ?? '';
}
