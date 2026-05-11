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
    <?php echo @$msg; ?>
    <form action="" method="post" name="<?php echo $page; ?>" id="<?php echo $page; ?>">
        <?php $dataList->search_box(__('Search'), 'search_id') ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>