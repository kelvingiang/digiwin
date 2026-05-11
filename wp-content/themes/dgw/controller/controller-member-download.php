<?php
// require_once(DIR_MODEL . 'model_check_in_setting.php');
class Controller_Member_Download_Report
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'Create'));
    }

    // PHAN TAO MENU CON TRONG MENU CHA CUNG LA POST TYPE
    public function Create()
    {
        $parent_slug = 'member_page';
        $page_title = __('下載統計');
        $menu_title = __('下載統計');
        $capability = 'manage_categories';
        $menu_slug = 'member_download_report';
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'));
    }

    public function dispatchActive()
    {
        //        echo __METHOD__;
        $action = getParams('action');
        switch ($action) {
            default:
                $this->displayPage();
                break;
        }
    }

    public function displayPage()
    {
        require_once(DIR_VIEW . 'view-member-download.php');
    }
}
