<?php ?>
<form name="f-info" id="f-info" method="post">
    <div class="row-three-column" style="margin: 1rem 0rem;">
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('Phone', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-phone" name="txt-phone" class="my-input" value="<?php echo get_post_meta('1', '_info_phone', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('Fax', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-fax" name="txt-fax" class="my-input" value="<?php echo get_post_meta('1', '_info_fax', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('E-mail', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-email" name="txt-email" class="my-input" value="<?php echo get_post_meta('1', '_info_email', true) ?>" />
            </div>
        </div>
    </div>

    <div class="row-three-column">
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('Years of experience', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-years-experience" name="txt-years-experience" class="my-input" value="<?php echo get_post_meta('1', '_info_years_experience', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('Projects & Clients', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-project-clients" name="txt-project-clients" class="my-input" value="<?php echo get_post_meta('1', '_info_project_clients', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php echo __('Product Solutions', 'dgw') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-product-solution" name="txt-product-solution" class="my-input" value="<?php echo get_post_meta('1', '_info_product_solution', true) ?>" />
            </div>
        </div>
    </div>

    <div id="tabs" style="margin-top: 2rem;">
        <ul>
            <li><a href="#tabs-1"><?php echo __('Chinese', 'dgw') ?></a></li>
            <li><a href="#tabs-2"><?php echo __('Vietnamese', 'dgw') ?></a></li>
        </ul>
        <div id="tabs-1">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label><?php echo __('Company Name', 'dgw') ?> (<?php echo __('Chinese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-cn" name="txt-name-cn" class="my-input" value="<?php echo get_post_meta('1', '_info_name_cn', true) ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label><?php echo __('Address', 'dgw') ?> (<?php echo __('Chinese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-cn" name="txt-address-cn" class="my-input" value="<?php echo get_post_meta('1', '_info_address_cn', true) ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Summary', 'dgw') ?> (<?php echo __('Chinese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_summary_cn', true), 'txt-summary-cn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Operating', 'dgw') ?> (<?php echo __('Chinese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_operating_cn', true), 'txt-operating-cn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Location', 'dgw') ?> (<?php echo __('Chinese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_location_cn', true), 'txt-location-cn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="tabs-2">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label><?php echo __('Company Name', 'dgw') ?> (<?php echo __('Vietnamese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-vn" name="txt-name-vn" class="my-input" value="<?php echo get_post_meta('1', '_info_name_vn', true) ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label><?php echo __('Address', 'dgw') ?>(<?php echo __('Vietnamese', 'dgw') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-vn" name="txt-address-vn" class="my-input" value="<?php echo get_post_meta('1', '_info_address_vn', true) ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Summary', 'dgw') ?> (<?php echo __('Vietnamese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_summary_vn', true), 'txt-summary-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Operating', 'dgw') ?> (<?php echo __('Vietnamese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_operating_vn', true), 'txt-operating-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php echo __('Company Location', 'dgw') ?> (<?php echo __('Vietnamese', 'dgw') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_location_vn', true), 'txt-location-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="button-row">
        <input type="submit" name="btn-submit" id="btn-submit" class="button button-primary button-large" value="<?php echo __('Submit', 'dgw') ?>" />
    </div>
</form>

<script>
    jQuery(function() {
        jQuery("#tabs").tabs();
    });
</script>