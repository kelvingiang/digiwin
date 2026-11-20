<?php
// [2026-11-20] - @author: Kelvin - Bổ sung Nonce bảo vệ form cài đặt website và Late Escaping
if ((int) getParams('msg') === 1) :
?>
    <div class="updated notice notice-success is-dismissible"><p><?php echo esc_html__('Data Adjustment succeeded', 'dgw'); ?></p></div>
<?php endif; ?>
<form name="f-info" id="f-info" method="post">
    <?php wp_nonce_field('dgw_setting_save_action', 'dgw_setting_nonce'); ?>


    <div class="row-two-column">
        <div class="col">
            <div class="cell-title">
                <label><?php echo esc_html__('First load', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" 
                       id="txt-first-load"
                       name="txt-first-load"
                       class="my-input"
                       value="<?php echo esc_attr((string) get_option('first_load')); ?>"/>
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php echo esc_html__('More loading', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" 
                       id="txt-more-load"
                       name="txt-more-load"
                       class="my-input"
                       value="<?php echo esc_attr((string) get_option('more_load')); ?>"
                       />
            </div>
        </div>

    </div>

 
    <div class="button-row">
        <input type="submit" name="btn-submit" id="btn-submit" class="button button-primary button-large" value="<?php echo __('Submit', 'dgw') ?>"/>
    </div>
</form>

