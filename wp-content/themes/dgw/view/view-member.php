<?php
require_once(DIR_MODEL . 'model-download.php');
$dataList = new Model_Download();
$dataList->prepare_items();
$lbl = '';
$page = getParams('page');
$linkAdd = admin_url('admin.php?page=' . $page . '&action=add');  // TAO LINH CHO ADD NEW
$lblAdd = __('Add Item', 'dgw');
if (getParams('msg') == 1) {
    $msg = '<div class="updated notice notice-success is-dismissible"><p>' . __('Data Adjustment succeeded', 'dgw') . '</p></div>';
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo __('Registered List', 'dgw'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=' . $page . '&action=export_members_excel'); ?>" class="page-title-action"><?php echo __('Export Excel file', 'dgw'); ?></a>
    <hr class="wp-header-end">
    <?php echo @$msg; ?>
    <form action="" method="post" name="<?php echo $page; ?>" id="<?php echo $page; ?>">
        <?php $dataList->search_box(__('Search', 'dgw'), 'search_id') ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>