<?php
require_once(DIR_MODEL . 'model-download-function.php');
$model = new Model_Download_Function();
$downloads = $model->get_all_downloads();
$data =  $model->get_download_stats_by_title();
$count = 1;
$page = getParams('page');
?>
<div>
    <h2>下載統計</h2>
    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ccd0d4; border-radius: 4px;">
        <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
            <thead style="position: sticky; top: 0; z-index: 10; background: #f1f1f1; box-shadow: 0 1px 0 rgba(0,0,0,.1);">

                <tr>
                    <th style="width: 300px;">資源名稱</th>
                    <th style="width: 100px;">下載次數</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $item) : ?>
                    <tr>
                        <td style="width: 300px; font-weight:bold"><?php echo esc_html($item['title']); ?></td>
                        <td style="width: 100px; text-align:left; padding-right: 20px;"><?php echo esc_html($item['download_count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<div style="margin-top: 1.5rem;">
    <div class="wrap" style="display: inline-block;">
        <h1 class="wp-heading-inline">下載報表</h1> 
        <a href="<?php echo admin_url('admin.php?page=' . $page . '&action=export_download_excel'); ?>" class="page-title-action"><?php echo __('匯出 Excel 檔案', 'dgw'); ?></a>
    </div>
    <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ccd0d4; border-radius: 4px;">
        <table class="wp-list-table widefat fixed striped" style="margin: 0; border: none;">
            <thead style="position: sticky; top: 0; z-index: 10; background: #f1f1f1; box-shadow: 0 1px 0 rgba(0,0,0,.1);">
                <tr>
                    <th style="width:20px"></th>
                    <th style="width: 100px;">姓名</th>
                    <th style="width: 200px;">公司名稱</th>
                    <th style="width: 200px;">E-mail</th>
                    <th style="width: 250px;">下載資源</th>
                    <th style="width: 100px;">下載日期</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($downloads as $download) : ?>
                    <tr>
                        <td style="width:20px"><?php echo $count++; ?></td>
                        <td style="width: 100px;"><?php echo esc_html($download->username); ?></td>
                        <td style="width: 200px;"><?php echo esc_html($download->company); ?></td>
                        <td style="width: 200px;"><?php echo esc_html($download->email); ?></td>
                        <td style="width: 250px;"><?php echo esc_html($download->title); ?></td>
                        <td style="width: 100px;"><?php echo esc_html(date('Y-m-d H:i:s', strtotime($download->download_date))); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>