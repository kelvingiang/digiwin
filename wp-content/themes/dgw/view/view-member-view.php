<?php
/**
 * [03/11/2026] - View hiển thị thống kê số lần xem theo từng video và danh sách chi tiết thành viên đã xem video (kid = 2)
 */
require_once(DIR_MODEL . 'model-download-function.php');
$model = new Model_Download_Function();
// Chỉ liệt kê và thống kê dữ liệu xem video YouTube (kid = 2)
$views = $model->get_all_downloads(2);
$data  = $model->get_download_stats_by_title(2);
$count = 1;
$page  = getParams('page');
?>
<div>
    <h2><?php echo __('Video View Statistics', 'dgw'); ?></h2>
    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccd0d4; border-radius: 4px;">
        <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
            <thead style="position: sticky; top: 0; z-index: 10; background: #f1f1f1; box-shadow: 0 1px 0 rgba(0,0,0,.1);">
                <tr>
                    <th style="width: 300px;"><?php echo __('Post Title', 'dgw'); ?></th>
                    <th style="width: 100px; text-align:center;"><?php echo __('View Count', 'dgw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item) : ?>
                    <tr>
                        <td style="width: 300px; font-weight:bold"><?php echo esc_html($item['title']); ?></td>
                        <td style="width: 100px; text-align:center;"><?php echo esc_html($item['download_count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1.5rem;">
    <div class="wrap" style="display: inline-block;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $page . '&action=export_view_excel')); ?>" class="page-title-action"><?php echo __('Export Excel file', 'dgw'); ?></a>
    </div>
    <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ccd0d4; border-radius: 4px;">
        <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
            <thead style="position: sticky; top: 0; z-index: 10; background: #f1f1f1; box-shadow: 0 1px 0 rgba(0,0,0,.1);">
                <tr>
                    <th style="width:20px"></th>
                    <th style="width: 100px;"><?php echo __('Full Name', 'dgw'); ?></th>
                    <th style="width: 200px;"><?php echo __('Company Name', 'dgw'); ?></th>
                    <th style="width: 200px;"><?php echo __('E-mail', 'dgw'); ?></th>
                    <th style="width: 250px;"><?php echo __('Post Title', 'dgw'); ?></th>
                    <th style="width: 100px;"><?php echo __('Watch Date', 'dgw'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($views as $view) : ?>
                    <tr>
                        <td style="width:20px"><?php echo $count++; ?></td>
                        <td style="width: 100px;"><?php echo esc_html($view->username); ?></td>
                        <td style="width: 200px;"><?php echo esc_html($view->company); ?></td>
                        <td style="width: 200px;"><?php echo esc_html($view->email); ?></td>
                        <td style="width: 250px;"><?php echo esc_html($view->title); ?></td>
                        <td style="width: 100px;"><?php echo esc_html(date('Y-m-d H:i:s', strtotime($view->download_date))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
