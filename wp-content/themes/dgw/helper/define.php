<?php
define('TIME_OUT_CLEAR', 1 * 60 * 30);
define('DIR_CONTROLLER', THEME_URL . DS . 'controller' . DS);
define('DIR_MODEL', THEME_URL . DS . 'model' . DS);
define('DIR_VIEW', THEME_URL . DS . 'view' . DS);
define('DIR_CLASS', THEME_URL . DS . 'class' . DS);
define('DIR_TAXONOMY', THEME_URL . DS . 'taxonomy' . DS);
define('DIR_METABOX', THEME_URL . DS . 'metabox' . DS);
define('DIR_IMAGES', THEME_URL . DS . 'images' . DS);
define('DIR_ICON', DIR_IMAGES . 'icon' . DS);
define('DIR_COMPONENT', THEME_URL . DS . 'component' . DS);
define('DIR_FILE', THEME_URL . DS . 'file' . DS);
define('DIR_SHORTCODE', THEME_URL . DS . 'shortcode' . DS);
define('DIR_LANGUAGES', THEME_URL . DS . 'languages' . DS);


// duong part
define('PART_IMAGES', THEME_PART . '/images/');
define('PART_ICON', PART_IMAGES . '/icons/');
define('PART_FILE', THEME_PART . '/file/');



/** SMTP 配置 (為了增強 Email 發送可靠性) */
define('SMTP_HOST', 'smtp.gmail.com');  // 
// define('SMTP_PORT', 587);                     
// define('SMTP_SECURE', 'tls');                 
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'digiwin.asean@gmail.com');
define('SMTP_PASSWORD', 'dzklgituqxvbqotl');
define('SMTP_FROM_EMAIL', 'digiwin.asean@gmail.com');
define('SMTP_FROM_NAME', 'Digiwin vietnam');

// define('SMTP_USERNAME', 'kelvinctcvn@gmail.com'); 
// define('SMTP_PASSWORD', 'yidgmjjlepprajbn'); 
// define('SMTP_FROM_EMAIL', 'kelvinctcvn@gmail.com');
// define('SMTP_HOST', 'dwm6.digiwin.com');  // 
// // // define('SMTP_PORT', 587);                     
// define('SMTP_PORT', 465);                     
// // define('SMTP_SECURE', 'tls');                 
// // define('SMTP_SECURE', 'ssl');                 
// define('SMTP_AUTH', true);                    
// define('SMTP_USERNAME', 'marketing_vn@digiwin.com'); 
// define('SMTP_PASSWORD', 'Dgw##20260114ss'); 
// define('SMTP_FROM_EMAIL', 'marketing_vn@digiwin.com');
// define('SMTP_FROM_NAME', 'Digiwin vietnam');

// if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // define('WP_HOME', 'http://digiwin.test');
    // define('WP_SITEURL', 'http://digiwin.test');
// } else {
//     define('WP_HOME', 'https://www.digiwin.com.vn');
//     define('WP_SITEURL', 'https://www.digiwin.com.vn');
// }
