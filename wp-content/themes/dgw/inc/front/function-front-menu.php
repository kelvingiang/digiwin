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
            'data'=> 'about-'.dgw_get_lang(),
            // 'subClass' => 'menu-main-sub-1',
            // 'sub' => array(),
            //'sub' => $homeArr,
        ),
        'cases' => array(
            'name' => "cases",
            'class' => 'menu-main-item',
            'data'=> "cases",
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
            'data'=> 'activities', 
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
            'data'=> "join-digiwin",
            //'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            // 'sub' => array(),
            //'sub' => getCategories('casestudies_category'),
        ),
        // 'join-digiwin/cate/98/tag/' => array(
        'partner' => array(
            'name' => "distribution",
            'class' => 'menu-main-item',
            'data'=> "partner",
            //'subClass' => 'menu-main-sub-1', // neu co sub menu phai them sub Class
            // 'sub' => array(),
            //'sub' => getCategories('casestudies_category'),
        ),

        $GLOBALS['contact'] => array(
            'name' => "contact",
            'class' => 'menu-main-item',
            'data'=> 'contact-'.dgw_get_lang(),
            // 'sub' => array()
        ),
    );
    return $arr;
}
