<?php
declare(strict_types=1);

/**
 * [2026-11-20] - @author: Kelvin - Bổ sung Nonce bảo vệ form thông tin công ty và Late Escaping (esc_attr, esc_html__) đầy đủ cho toàn bộ input
 */
if ((int) getParams('msg') === 1) :
?>
    <div class="updated notice notice-success is-dismissible"><p><?php echo esc_html__('Data Adjustment succeeded', 'dgw'); ?></p></div>
<?php endif; ?>
<form name="f-info" id="f-info" method="post">
    <?php wp_nonce_field('dgw_info_save_action', 'dgw_info_nonce'); ?>
    <div class="row-three-column" style="margin: 1rem 0rem;">
        <div class="col">
            <div class="cell-title">
                <label for="txt-phone"><?php echo esc_html__('Phone', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-phone" name="txt-phone" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_phone', true)); ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label for="txt-fax"><?php echo esc_html__('Fax', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-fax" name="txt-fax" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_fax', true)); ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label for="txt-email"><?php echo esc_html__('E-mail', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-email" name="txt-email" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_email', true)); ?>" />
            </div>
        </div>
    </div>

    <div class="row-three-column">
        <div class="col">
            <div class="cell-title">
                <label for="txt-years-experience"><?php echo esc_html__('Years of experience', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-years-experience" name="txt-years-experience" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_years_experience', true)); ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label for="txt-project-clients"><?php echo esc_html__('Projects & Clients', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-project-clients" name="txt-project-clients" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_project_clients', true)); ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label for="txt-product-solution"><?php echo esc_html__('Product Solutions', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-product-solution" name="txt-product-solution" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_product_solution', true)); ?>" />
            </div>
        </div>
    </div>

    <div id="tabs" style="margin-top: 2rem;">
        <ul>
            <li><a href="#tabs-1"><?php echo esc_html__('Chinese', 'dgw'); ?></a></li>
            <li><a href="#tabs-2"><?php echo esc_html__('Vietnamese', 'dgw'); ?></a></li>
        </ul>
        <div id="tabs-1">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label for="txt-name-cn"><?php echo esc_html__('Company Name', 'dgw'); ?> (<?php echo esc_html__('Chinese', 'dgw'); ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-cn" name="txt-name-cn" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_name_cn', true)); ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label for="txt-address-cn"><?php echo esc_html__('Address', 'dgw'); ?> (<?php echo esc_html__('Chinese', 'dgw'); ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-cn" name="txt-address-cn" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_address_cn', true)); ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Summary', 'dgw'); ?> (<?php echo esc_html__('Chinese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_summary_cn', true), 'txt-summary-cn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Operating', 'dgw'); ?> (<?php echo esc_html__('Chinese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_operating_cn', true), 'txt-operating-cn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Location', 'dgw'); ?> (<?php echo esc_html__('Chinese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_location_cn', true), 'txt-location-cn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="tabs-2">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label for="txt-name-vn"><?php echo esc_html__('Company Name', 'dgw'); ?> (<?php echo esc_html__('Vietnamese', 'dgw'); ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-vn" name="txt-name-vn" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_name_vn', true)); ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label for="txt-address-vn"><?php echo esc_html__('Address', 'dgw'); ?> (<?php echo esc_html__('Vietnamese', 'dgw'); ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-vn" name="txt-address-vn" class="my-input" value="<?php echo esc_attr((string) get_post_meta(1, '_info_address_vn', true)); ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Summary', 'dgw'); ?> (<?php echo esc_html__('Vietnamese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_summary_vn', true), 'txt-summary-vn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Operating', 'dgw'); ?> (<?php echo esc_html__('Vietnamese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_operating_vn', true), 'txt-operating-vn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo esc_html__('Company Location', 'dgw'); ?> (<?php echo esc_html__('Vietnamese', 'dgw'); ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor((string) get_post_meta(1, '_info_location_vn', true), 'txt-location-vn', ['wpautop' => false, 'editor_height' => '400']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="button-row">
        <input type="submit" name="btn-submit" id="btn-submit" class="button button-primary button-large" value="<?php echo esc_attr__('Submit', 'dgw'); ?>" />
    </div>
</form>

<script>
    jQuery(function() {
        jQuery("#tabs").tabs();
    });
</script>
