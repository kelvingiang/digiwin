<?php
require_once(DIR_MODEL . 'model-popup.php');
$dataList = new Model_Pop_up(); 
$dataList->prepare_items();
$lbl = null;
$msg = null;
$page = getParams('page');
$linkAdd = admin_url('admin.php?page=' . $page . '&action=add');  // TAO LINH CHO ADD NEW
$lblAdd = __('Add Item');
if (getParams('msg') == 1) {
    $msg .= '<div class="updated notice notice-success is-dismissible"><p>' . __('Data Adjustment succeeded') . '</p></div>';
}
?>

<div class="wrap">
    <h2 style="font-weight: bold">
        <?php echo esc_html__($lbl); ?>
        <a href ="<?php echo esc_url($linkAdd); ?>"  class ="add-new-h2"><?php echo esc_html__($lblAdd); ?></a>
    </h2>
    <?php echo @$msg; ?>
    <form action ="" method="post" name="<?php echo $page; ?>" id="<?php echo $page; ?>">
        <?php $dataList->search_box(__('Search'), 'search_id') ?>
        <?php $dataList->views(); ?>
        <?php $dataList->display(); ?>
    </form>
</div>
<script>
    function clickChangeStatus(event) {
        location.replace("<?php echo  'admin.php?page=' . $page . '&action=status&id=' ?>" + event);
    }
</script>