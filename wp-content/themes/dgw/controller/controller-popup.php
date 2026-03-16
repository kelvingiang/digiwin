<?php
require_once(DIR_MODEL . 'model-popup-function.php');

function uploadImg($name, $File)
{
    if (!empty($File['name'])) {
        $errors = [];

        $file_size = $File['size'];
        $file_tmp  = $File['tmp_name'];
        $file_name = $File['name'];

        // 副檔名
        $trim_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 安全檔名
        $safe_name = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file_name);
        $safe_title = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $name);
        $cus_name  = $safe_title . '-' . $safe_name;

        // 檔案限制
        $extensions = ["jpeg", "jpg", "png", "bmp", "webp", "avif"];
        if (!in_array($trim_type, $extensions)) {
            $errors[] = "上傳照片檔案僅限JPEG, PNG, BMP.";
        }
        if ($file_size > 2097152) {
            $errors[] = '上傳檔案容量不可大於2MB';
        }

        // 路徑
        $path = DIR_IMAGES . 'pop-up' . DS;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // MIME 檢查
        $allowed_types = ['image/jpeg', 'image/png', 'image/bmp', 'image/webp', 'image/avif'];
        if (!in_array(mime_content_type($file_tmp), $allowed_types)) {
            $errors[] = "檔案類型不正確!";
        }

        if (empty($errors)) {
            if (move_uploaded_file($file_tmp, $path . $cus_name)) {
                return $cus_name;
            } else {
                $errors[] = "檔案移動失敗";
            }
        }
        return $errors;
    }
}

class Controller_Popup
{
    private $model;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create'));
        $this->model = new Model_Popup_Function();
    }

    public function create()
    {
        // THEM 1 NHOM MENU MOI VAO TRONG ADMIN MENU
        $page_title = __('Pop-up'); // TIEU DE CUA TRANG
        $menu_title = __('Pop-up');  // TEN HIEN TRONG MENU
        // CHON QUYEN TRUY CAP manage_categories DE role ADMINNITRATOR VÀ EDITOR DEU THAY DUOC
        $capability = 'manage_categories'; // QUYEN TRUY CAP DE THAY MENU NAY
        $menu_slug = 'popup_page'; // TEN slug TEN DUY NHAT KO DC TRUNG VOI TRANG KHAC GAN TREN THANH DIA CHI OF MENU
        // THAM SO THU 5 GOI DEN HAM HIEN THI GIAO DIEN TRONG MENU
        $icon = PART_ICON . 'icon-setting.png';  // THAM SO THU 6 LA LINK DEN ICON DAI DIEN
        $position = 5; // VI TRI HIEN THI TRONG MENU

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
            case 'active':
                $this->activeAction();
                break;
            case 'passive':
                $this->passiveAction();
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
        require_once(DIR_VIEW . 'view-pop-up.php');
    }

    public function trashAction()
    {
        $this->model->toTrash(getParams(), getParams('action'));
        toBack(1);
    }

    public function activeAction()
    {
        $this->model->toActive(getParams());
        toBack(1);
    }

    public function passiveAction()
    {
        $this->model->toPassive(getParams());
        toBack(1);
    }

    public function addAction()
    {
        $flag = true;
        if (isPost()) {
                  if ($_FILES)
                foreach ($_FILES as $key => $file) {
                    $upload = uploadImg($_POST['txt-title'], $file);
                    if (is_array($upload)) {
                        $flag = false;
                        $error = json_encode($upload, JSON_UNESCAPED_UNICODE);
                        $error = urlencode($error);
                        $url = 'admin.php?page=' . $_REQUEST['page']
                            . '&action=' . getParams('action')
                            . '&id=' . getParams('id')
                            . '&e=' . $error;
                        wp_redirect($url);

                        exit;
                    } else {
                        $data_upload["$key"] = $upload;
                    }
                }

            $data = array_merge($_POST, $data_upload);

            if ($flag) {
                $this->model->Save($data, getParams('action'));
                toBack(1);
            }
        }
        require_once(DIR_VIEW . 'from-pop-up.php');
    }

    public function deleteAction()
    {
        $this->model->toDelete(getParams());
        toBack(1);
    }
}
