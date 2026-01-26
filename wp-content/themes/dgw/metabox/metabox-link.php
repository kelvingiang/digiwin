<?php

class Metabox_Link
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create()
    {
        $id = 'admin-metabox-link';
        $title = __('Link');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('slider',));
    }

    public function display($post)
    {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
?>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label><?php _e('') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-link" id="txt-link" class="my-input" value="<?php echo get_post_meta($post->ID, '_metabox_link', true) ?>" />
                </div>
            </div>
        </div>


        <div class="clear"></div>
<?php
    }

    public function save($post_id)
    {

        if (!empty($_POST['txt-link'])) {
            update_post_meta($post_id, '_metabox_link', $_POST['txt-link']);
        }
    }
}
