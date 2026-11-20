<?php
declare(strict_types=1);

namespace DGW\Controllers;

use DGW\Core\UploadHandler;
use DGW\Helpers\RequestHelper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Controller quản lý Logo đối tác (sửa lỗi biến chưa khởi tạo, Nonce và redirect chuẩn)
 */
class LogoController extends BaseAdminController
{
    private ?\Model_Logo_Function $model = null;

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký menu Customer Trademark trong WP-Admin
     */
    protected function addMenuPage()
    {
        return add_menu_page(
            __('Customer Trademark', 'dgw'),
            __('Customer Trademark', 'dgw'),
            $this->capability,
            'logo_page',
            [$this, 'renderPage'],
            PART_ICON . 'icon-setting.png',
            3
        );
    }

    /**
     * [2026-11-20] - @author: Kelvin - Xử lý action thay đổi dữ liệu và upload logo trước khi render HTML
     */
    public function handleEarlyRequest(): void
    {
        parent::handleEarlyRequest();

        $action = $this->resolveListAction();
        $page   = 'logo_page';

        switch ($action) {
            case 'trash':
            case 'restore':
                $this->verifyListActionNonce($page, $action);
                $this->getModel()->toTrash(getParams(), $action);
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
     * [2026-11-20] - @author: Kelvin - Render form thêm/sửa hoặc bảng danh sách logo
     */
    public function renderPage(): void
    {
        $action = (string) getParams('action');
        if ($action === 'add' || $action === 'edit') {
            require_once DIR_VIEW . 'form-logo.php';
            return;
        }

        require_once DIR_VIEW . 'view-logo.php';
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra Nonce, upload logo và lưu dữ liệu an toàn
     */
    private function handleSave(string $action): void
    {
        $this->verifyNonceAction('dgw_logo_save_action', 'dgw_logo_nonce');

        $postData = wp_unslash($_POST);
        $company  = sanitize_text_field((string) ($postData['txt-company'] ?? ''));
        $logo     = !empty($_FILES['file-logo']['name']) ? UploadHandler::uploadLogo($company, $_FILES) : null;

        if (is_array($logo)) {
            $errorUrl = add_query_arg(
                [
                    'page'   => 'logo_page',
                    'paged'  => max(1, absint(getParams('paged', 1))),
                    'action' => $action,
                    'id'     => absint(getParams('id')),
                    'e'      => rawurlencode((string) ($logo[0] ?? '')),
                ],
                admin_url('admin.php')
            );
            wp_safe_redirect($errorUrl);
            exit;
        }

        $data            = $postData;
        $data['txt-img'] = (is_string($logo) && $logo !== '')
            ? $logo
            : sanitize_file_name((string) ($postData['hid-img'] ?? ''));

        $this->getModel()->Save($data, $action);
        RequestHelper::toBack(1);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Lazy-load Model_Logo_Function
     */
    private function getModel(): \Model_Logo_Function
    {
        if ($this->model === null) {
            require_once DIR_MODEL . 'model-logo-function.php';
            $this->model = new \Model_Logo_Function();
        }

        return $this->model;
    }
}
