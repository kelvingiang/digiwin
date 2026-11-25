<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Core\UploadHandler;
use DGW\Helpers\RequestHelper;
use DGW\Models\PopupListModel;
use DGW\Models\PopupModel;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller quản lý Pop-up (tách upload sang UploadHandler, bổ sung kiểm tra Nonce và bỏ update_option nhầm)
 */
class PopupController extends BaseAdminController
{
    private ?PopupModel $model = null;

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu Pop-up trong WP-Admin
     */
    protected function addMenuPage()
    {
        return add_menu_page(
            __('Pop-up', 'dgw'),
            __('Pop-up', 'dgw'),
            $this->capability,
            'popup_page',
            [$this, 'renderPage'],
            PART_ICON . 'icon-setting.png',
            5
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý các action thay đổi trạng thái, xóa hoặc lưu Pop-up trước khi render HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        $action = $this->resolveListAction();
        $page   = 'popup_page';

        switch ($action) {
            case 'trash':
            case 'restore':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toTrash(getParams(), $action);
                RequestHelper::toBack(1);
                break;

            case 'active':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toActive(getParams());
                RequestHelper::toBack(1);
                break;

            case 'passive':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toPassive(getParams());
                RequestHelper::toBack(1);
                break;

            case 'delete':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toDelete(getParams());
                RequestHelper::toBack(1);
                break;

            case 'add':
            case 'edit':
                if (RequestHelper::isPost()) {
                    $this->handleSave($action);
                }
                break;

            case '-1':
                $this->redirectIfFilterSubmitted();
                break;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render form thêm/sửa hoặc bảng danh sách Pop-up (chuẩn MVC: lấy dữ liệu tại Controller)
     */
    public function renderPage(): void
    {
        $action = (string) getParams('action');
        if ($action === 'add' || $action === 'edit') {
            $paramId = absint(getParams('id'));
            $data    = $paramId > 0 ? ($this->getModel()->getItem($paramId) ?: []) : [];

            $this->renderView('form-pop-up.php', [
                'data' => is_array($data) ? $data : [],
            ]);
            return;
        }

        $dataList = new PopupListModel();
        $dataList->prepare_items();

        $this->renderView('view-pop-up.php', [
            'dataList' => $dataList,
            'page'     => (string) getParams('page'),
            'msgCode'  => (int) getParams('msg'),
        ]);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra Nonce, upload 4 ảnh qua UploadHandler và lưu dữ liệu Pop-up
     */
    private function handleSave(string $action): void
    {
        $this->verifyNonceAction('dgw_popup_save_action', 'dgw_popup_nonce');

        $postData   = wp_unslash($_POST);
        $title      = sanitize_text_field((string) ($postData['txt-title'] ?? ''));
        $dataUpload = [];

        if (!empty($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (!is_array($file) || empty($file['name'])) {
                    continue;
                }

                $upload = UploadHandler::uploadPopupImage($title, $file);
                if (is_array($upload)) {
                    $errorJson = wp_json_encode($upload, JSON_UNESCAPED_UNICODE);
                    $errorUrl  = add_query_arg(
                        [
                            'page'   => 'popup_page',
                            'action' => $action,
                            'id'     => absint(getParams('id')),
                            'e'      => rawurlencode((string) $errorJson),
                        ],
                        admin_url('admin.php')
                    );
                    wp_safe_redirect($errorUrl);
                    exit;
                }

                if (is_string($upload) && $upload !== '') {
                    $dataUpload[sanitize_key((string) $key)] = $upload;
                }
            }
        }

        $data = array_merge($postData, $dataUpload);
        $this->getModel()->Save($data, $action);
        RequestHelper::toBack(1);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lazy-load PopupModel (PSR-4)
     */
    private function getModel(): PopupModel
    {
        if ($this->model === null) {
            $this->model = new PopupModel();
        }

        return $this->model;
    }
}
