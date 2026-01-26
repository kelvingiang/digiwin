<?php

class Controller_Company_Information {

    private $model;

    public function __construct() {
        add_action('admin_menu', array($this, 'create'));
        //  $this->model = new Model_Member_Function();
    }

    public function create() {
// THEM 1 NHOM MENU MOI VAO TRONG ADMIN MENU
        $page_title = __('Company Information'); // TIEU DE CUA TRANG
        $menu_title = __('Company Information');  // TEN HIEN TRONG MENU
// CHON QUYEN TRUY CAP manage_categories DE role ADMINNITRATOR VÀ EDITOR DEU THAY DUOC
        $capability = 'manage_categories'; // QUYEN TRUY CAP DE THAY MENU NAY
        $menu_slug = 'information_page'; // TEN slug TEN DUY NHAT KO DC TRUNG VOI TRANG KHAC GAN TREN THANH DIA CHI OF MENU
// THAM SO THU 5 GOI DEN HAM HIEN THI GIAO DIEN TRONG MENU
        $icon = PART_ICON . 'icon-setting.png';  // THAM SO THU 6 LA LINK DEN ICON DAI DIEN
        $position = 2; // VI TRI HIEN THI TRONG MENU

        add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'), $icon, $position);
    }

    /* PHAN DIEN HUONG CHO  CAC ACTION ============================ */

    public function dispatchActive() {

        $action = getParams('action');
        switch ($action) {
            default :
                $this->displayPage();
                break;
        }
    }

    public function createUrl() {
        echo $url = 'admin.php?page=' . getParams('page');

        if (getParams('filter_category') != '0') {
            $url .= '&filter_category=' . getParams('filter_category');
        }

        if (mb_strlen(getParams('s'))) {
            $url .= '&s=' . getParams('s');
        }
        return $url;
    }

    public function displayPage() {
        if (getParams('action') == -1) {
            $url = $this->createUrl();
            wp_redirect($url);
        }

        if (isPost()) {
            update_post_meta(1, '_info_phone', $_POST['txt-phone']);
            update_post_meta(1, '_info_fax', $_POST['txt-fax']);
            update_post_meta(1, '_info_email', $_POST['txt-email']);

            update_post_meta(1, '_info_name_cn', $_POST['txt-name-cn']);
            update_post_meta(1, '_info_address_cn', $_POST['txt-address-cn']);
            update_post_meta(1, '_info_summary_cn', $_POST['txt-summary-cn']);
            update_post_meta(1, '_info_operating_cn', $_POST['txt-operating-cn']);
            update_post_meta(1, '_info_location_cn', $_POST['txt-location-cn']);

            update_post_meta(1, '_info_name_vn', $_POST['txt-name-vn']);
            update_post_meta(1, '_info_address_vn', $_POST['txt-address-vn']);
            update_post_meta(1, '_info_summary_vn', $_POST['txt-summary-vn']);
            update_post_meta(1, '_info_operating_vn', $_POST['txt-operating-vn']);
            update_post_meta(1, '_info_location_vn', $_POST['txt-location-vn']);

            update_post_meta(1, '_info_name_en', $_POST['txt-name-en']);
            update_post_meta(1, '_info_address_en', $_POST['txt-address-en']);
            update_post_meta(1, '_info_summary_en', $_POST['txt-summary-en']);
            update_post_meta(1, '_info_operating_en', $_POST['txt-operating-en']);
            update_post_meta(1, '_info_location_en', $_POST['txt-location-en']);
        }
        require_once (DIR_VIEW . 'view-information.php');
    }

}
