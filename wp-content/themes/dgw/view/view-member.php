<?php
require_once(DIR_MODEL . 'model-download.php');
$dataList = new Model_Download();
$dataList->prepare_items();
$lbl = '';
$page = getParams('page');
$linkAdd = admin_url('admin.php?page=' . $page . '&action=add');  // TAO LINH CHO ADD NEW
$lblAdd = __('Add Item');
if (getParams('msg') == 1) {
    $msg = '<div class="updated notice notice-success is-dismissible"><p>' . __('Data Adjustment succeeded') . '</p></div>';
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo __('註冊名單', 'dgw'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=' . $page . '&action=export_members_excel'); ?>" class="page-title-action"><?php echo __('匯出 Excel 檔案', 'dgw'); ?></a>
    <hr class="wp-header-end">
    <?php echo @$msg; ?>
    <form action="" method="post" name="<?php echo $page; ?>" id="<?php echo $page; ?>">
        <?php $dataList->search_box(__('Search'), 'search_id') ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>