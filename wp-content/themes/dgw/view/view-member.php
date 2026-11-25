<?php
declare(strict_types=1);

/**
 * [20/11/2026] - Chuẩn MVC: View chỉ nhận $dataList, $page, $msgCode từ MemberController
 */
if (!isset($dataList)) {
    return;
}

$page    = isset($page) ? (string) $page : (string) getParams('page');
$msgCode = isset($msgCode) ? (int) $msgCode : (int) getParams('msg');
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Registered List', 'dgw'); ?></h1>
    <?php
    // [2026-11-20] - @author: Kelvin - Bổ sung Nonce bảo vệ liên kết xuất Excel
    $exportUrl = wp_nonce_url(
        admin_url('admin.php?page=' . $page . '&action=export_members_excel'),
        'dgw_export_members_excel'
    );
    ?>
    <a href="<?php echo esc_url($exportUrl); ?>" class="page-title-action"><?php echo esc_html__('Export Excel file', 'dgw'); ?></a>
    <hr class="wp-header-end">
    <?php if ($msgCode === 1) : ?>
        <div class="updated notice notice-success is-dismissible">
            <p><?php echo esc_html__('Data Adjustment succeeded', 'dgw'); ?></p>
        </div>
    <?php endif; ?>
    <form action="" method="post" name="<?php echo esc_attr($page); ?>" id="<?php echo esc_attr($page); ?>">
        <?php $dataList->search_box(__('Search', 'dgw'), 'search_id') ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>