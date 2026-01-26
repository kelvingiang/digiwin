<?php

class Metabox_View
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create()
    {
        $id = 'admin-metabox-web';
        $title =  __('View') .' - '.  __('Like');
        $callback = array($this, 'display');
        add_meta_box($id, $title, $callback, array('post','solutions','services','industries','active','resources','casestudies','joinus'));
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
                    <label><?php _e('View') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-view" id="txt-view" class="my-input type-number"
                        value="<?php echo get_post_meta($post->ID, '_metabox_view', true) ?>" />
                </div>
            </div>
            <div class="col">
                <div class="cell-title">
                    <label><?php _e('Like') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-like" id="txt-like" class="my-input type-number"
                        value="<?php echo get_post_meta($post->ID, '_metabox_like', true) ?>" />
                </div>
            </div>
            <!-- <div class="col">
                <div class="cell-title">
                    <label><?php //_e('Comment') ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" name="txt-comment" id="txt-comment" class="my-input type-number"
                        value="<?php //echo get_post_meta($post->ID, '_metabox_comment', true) ?>" />
                </div>
            </div> -->
        </div>

        <div class="clear"></div>
<?php
    }

    public function save($post_id)
    {
        if (!empty($_POST['txt-view'])) {
            update_post_meta($post_id, '_metabox_view', $_POST['txt-view']);
        }

        if (!empty($_POST['txt-like'])) {
            update_post_meta($post_id, '_metabox_like', $_POST['txt-like']);
        }

        // if (!empty($_POST['txt-comment'])) {
        //     update_post_meta($post_id, '_metabox_comment', $_POST['txt-comment']);
        // }
    }
}
