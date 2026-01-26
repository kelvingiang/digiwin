<?php ?>
<form name="f-info" id="f-info" method="post">
    <div class="row-three-column" style="margin: 1rem 0rem;">
        <div class="col">
            <div class="cell-title">
                <label><?php _e('Phone') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-phone" name="txt-phone" class="my-input" value="<?php echo get_post_meta('1', '_info_phone', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php _e('Fax') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-fax" name="txt-fax" class="my-input" value="<?php echo get_post_meta('1', '_info_fax', true) ?>" />
            </div>
        </div>
        <div class="col">
            <div class="cell-title">
                <label><?php _e('E-mail') ?></label>
            </div>
            <div class="cell-text">
                <input type="text" id="txt-email" name="txt-email" class="my-input" value="<?php echo get_post_meta('1', '_info_email', true) ?>" />
            </div>
        </div>
    </div>

    <div id="tabs">
        <ul>
            <li><a href="#tabs-1"><?php _e('Chinese') ?></a></li>
            <li><a href="#tabs-2"><?php _e('Vietnamese') ?></a></li>
            <li><a href="#tabs-3"><?php _e('English') ?></a></li>
        </ul>
        <div id="tabs-1">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label><?php _e('Company Name') ?> (<?php _e('Chinese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-cn" name="txt-name-cn" class="my-input" value="<?php echo get_post_meta('1', '_info_name_cn', true) ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label><?php _e('Address') ?> (<?php _e('Chinese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-cn" name="txt-address-cn" class="my-input" value="<?php echo get_post_meta('1', '_info_address_cn', true) ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Summary') ?> (<?php _e('Chinese') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_summary_cn', true), 'txt-summary-cn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Operating') ?> (<?php _e('Chinese') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_operating_cn', true), 'txt-operating-cn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Location') ?> (<?php _e('Chinese') ?>)
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
                        <label><?php _e('Company Name') ?> (<?php _e('Vietnamese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-vn" name="txt-name-vn" class="my-input" value="<?php echo get_post_meta('1', '_info_name_vn', true) ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <label><?php _e('Address') ?>(<?php _e('Vietnamese') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-vn" name="txt-address-vn" class="my-input" value="<?php echo get_post_meta('1', '_info_address_vn', true) ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Summary') ?> (<?php _e('Vietnamese') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_summary_vn', true), 'txt-summary-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Operating') ?> (<?php _e('Vietnamese') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_operating_vn', true), 'txt-operating-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Location') ?> (<?php _e('Vietnamese') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_location_vn', true), 'txt-location-vn', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="tabs-3">
            <div class="row-two-column">
                <div class="col">
                    <div class="cell-title">
                        <label><?php _e('Company Name') ?> (<?php _e('English') ?>)</label>
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-name-en" name="txt-name-en" class="my-input" value="<?php echo get_post_meta('1', '_info_name_en', true) ?>" />
                    </div>
                </div>
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Address') ?>(<?php _e('English') ?>)
                    </div>
                    <div class="cell-text">
                        <input type="text" id="txt-address-en" name="txt-address-en" class="my-input" value="<?php echo get_post_meta('1', '_info_address_en', true) ?>" />
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Summary') ?>(<?php _e('English') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_summary_en', true), 'txt-summary-en', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Operating') ?> (<?php _e('English') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_operating_en', true), 'txt-operating-en', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

            <div class="row-one-column">
                <div class="col">
                    <div class="cell-title">
                        <?php _e('Company Location') ?> (<?php _e('English') ?>)
                    </div>
                    <div class="cell-text">
                        <?php wp_editor(get_post_meta('1', '_info_location_en', true), 'txt-location-en', array('wpautop' => false, 'editor_height' => '400')); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="button-row">
        <input type="submit" name="btn-submit" id="btn-submit" class="button button-primary button-large" value="<?php echo _e('Submit') ?>" />
    </div>
</form>

<script>
    jQuery(function() {
        jQuery("#tabs").tabs();
    });
</script>