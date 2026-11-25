<?php
declare(strict_types=1);

/**
 * [20/11/2026] - Chuẩn MVC: View chỉ nhận $dataList, $page, $msgCode từ PopupController
 */
if (!isset($dataList)) {
    return;
}

$page    = isset($page) ? (string) $page : (string) getParams('page');
$msgCode = isset($msgCode) ? (int) $msgCode : (int) getParams('msg');
$linkAdd = admin_url('admin.php?page=' . $page . '&action=add');
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Pop-up', 'dgw'); ?></h1>
    <a href="<?php echo esc_url($linkAdd); ?>" class="page-title-action"><?php echo esc_html__('Add Item', 'dgw'); ?></a>
    <hr class="wp-header-end">

    <?php if ($msgCode === 1) : ?>
        <div class="updated notice notice-success is-dismissible">
            <p><?php echo esc_html__('Data Adjustment succeeded', 'dgw'); ?></p>
        </div>
    <?php endif; ?>

    <form action="" method="post" name="<?php echo esc_attr($page); ?>" id="<?php echo esc_attr($page); ?>">
        <?php $dataList->search_box(__('Search', 'dgw'), 'search_id'); ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>
