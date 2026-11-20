<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Sửa lỗi esc_html__(null) trên PHP 8.3, loại bỏ @$msg và JS clickChangeStatus thừa
 */
require_once DIR_MODEL . 'model-popup.php';
$dataList = new Model_Pop_up();
$dataList->prepare_items();
$page    = (string) getParams('page');
$linkAdd = admin_url('admin.php?page=' . $page . '&action=add');
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Pop-up', 'dgw'); ?></h1>
    <a href="<?php echo esc_url($linkAdd); ?>" class="page-title-action"><?php echo esc_html__('Add Item', 'dgw'); ?></a>
    <hr class="wp-header-end">

    <?php if ((int) getParams('msg') === 1) : ?>
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
