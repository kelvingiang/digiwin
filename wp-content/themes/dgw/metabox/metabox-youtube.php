<?php

class Metabox_Youtube
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create()
    {
        $id = 'admin-metabox-source';
        $title = __('Youtube video link', 'dgw');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('solutions','services', 'industries', 'active', 'casestudies'));
    }

    public function display($post)
    {
        $action = 'admin-metabox-data';
        $name = 'admin-metabox-data-nonce';
        wp_nonce_field($action, $name);
?>
        <div class="row-one-column">
            <div class="col">
                <div class="cell-title">
                    <label><?php _e('') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text"
                        name="txt-youtube"
                        id="txt-youtube"
                        class="my-input"
                        value="<?php echo get_post_meta($post->ID, '_metabox_youtube', true) ?>" />
                </div>
            </div>
        </div>


        <div class="clear"></div>
<?php
    }

    public function save($post_id)
    {

        if (!empty($_POST['txt-youtube'])) {
            update_post_meta($post_id, '_metabox_youtube', sanitize_text_field(wp_unslash($_POST['txt-youtube'])));
        }
    }
}
