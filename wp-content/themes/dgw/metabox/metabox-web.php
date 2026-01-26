<?php

class Metabox_Web_FreeBook {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create() {
        $id = 'admin-metabox-web';
        $title = __('Web Site And FreeBook');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('advertising',));
    }

    public function display($post) {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
        ?>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label><?php _e('Web Site') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-web" id="txt-web" class="my-input"
                           value="<?php echo get_post_meta($post->ID, '_metabox_web', true) ?>" />
                </div>
            </div>
        </div>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label><?php _e('Free Book') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-freebook" id="txt-freebook" class="my-input"
                           value="<?php echo get_post_meta($post->ID, '_metabox_freebook', true) ?>" />
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <?php
    }

    public function save($post_id) {

        if (!empty($_POST['txt-web'])) {
            update_post_meta($post_id, '_metabox_web', $_POST['txt-web']);
        }

        if (!empty($_POST['txt-freebook'])) {
            update_post_meta($post_id, '_metabox_freebook', $_POST['txt-freebook']);
        }
    }

}
