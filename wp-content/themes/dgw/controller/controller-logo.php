<?php
require_once(DIR_MODEL . 'model-logo-function.php');
class Controller_logo
{
    private $model;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create'));
        $this->model = new Model_Logo_Function();
    }

    public function create()
    {
        // THEM 1 NHOM MENU MOI VAO TRONG ADMIN MENU
        $page_title = __('客戶商標'); // TIEU DE CUA TRANG
        $menu_title = __('客戶商標');  // TEN HIEN TRONG MENU
        // CHON QUYEN TRUY CAP manage_categories DE role ADMINNITRATOR VÀ EDITOR DEU THAY DUOC
        $capability = 'manage_categories'; // QUYEN TRUY CAP DE THAY MENU NAY
        $menu_slug = 'logo_page'; // TEN slug TEN DUY NHAT KO DC TRUNG VOI TRANG KHAC GAN TREN THANH DIA CHI OF MENU
        // THAM SO THU 5 GOI DEN HAM HIEN THI GIAO DIEN TRONG MENU
        $icon = PART_ICON . 'icon-setting.png';  // THAM SO THU 6 LA LINK DEN ICON DAI DIEN
        $position = 2; // VI TRI HIEN THI TRONG MENU

        add_menu_page($page_title, $menu_title, $capability, $menu_slug, array($this, 'dispatchActive'), $icon, $position);
    }

    /* PHAN DIEN HUONG CHO  CAC ACTION ============================ */

    public function dispatchActive()
    {

        $action = getParams('action');
        switch ($action) {
            case 'trash':
            case 'restore':
                $this->trashAction();
                break;
            case 'add':
            case 'edit':
                $this->addAction();
                break;
            case 'delete':
                $this->deleteAction();
                break;
            default:
                $this->displayPage();
                break;
        }
    }

    public function createUrl()
    {
        echo $url = 'admin.php?page=' . getParams('page');

        if (getParams('filter_category') != '0') {
            $url .= '&filter_category=' . getParams('filter_category');
        }

        if (mb_strlen(getParams('s'))) {
            $url .= '&s=' . getParams('s');
        }
        return $url;
    }

    public function displayPage()
    {
        if (getParams('action') == -1) {
            $url = $this->createUrl();
            wp_redirect($url);
        }

        if (isPost()) {
            update_option('first_load', $_POST['txt-first-load']);
            update_option('more_load', $_POST['txt-more-load']);
        }
        require_once(DIR_VIEW . 'view-logo.php');
    }

    public function trashAction()
    {
        $this->model->toTrash(getParams(), getParams('action'));
        require_once(DIR_VIEW . 'view-logo.php');
    }

    public function addAction()
    {
        $flag = true;
        if (isPost()) {
            if ($_FILES)
                $logo = uploadLogo($_POST['txt-company'], $_FILES);
            $data = $_POST;
            if ($logo != '') {
                if (is_array($logo)) {
                    $url = 'admin.php?page=' . $_REQUEST['page'] . '&paged=' . getParams('page') . '&action=' . getParams('action') . '&id=' . getParams('id') . '&e=' . $logo[0];
                    wp_redirect($url);
                    $flag = false;
                } else {
                    $data['txt-img'] = $logo;
                }
            } else {
                $data['txt-img'] = $_POST['hid-img'];
            }

            if ($flag) {
                $this->model->Save($data, getParams('action'));
                toBack(1);
            }
        }
        require_once(DIR_VIEW . 'from-logo.php');
    }

    public function deleteAction()
    {
        $this->model->toDelete(getParams());
        require_once(DIR_VIEW . 'view-logo.php');
    }
}
