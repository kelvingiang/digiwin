<?php

class Metabox_Order
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'create'));
        add_action('save_post', array($this, 'save'));
    }

    public function create()
    {
        $id = 'admin-metabox-order';
        $title = __('Show Order');
        $callback = array($this, 'display');
        $screens  = array('post', 'slider', 'joinus', 'solutions', 'services', 'industries', 'active', 'resources', 'downloads', 'casestudies', 'advertising'); // CAC POST VA CUSTOMER POST CHO PHEP METABOX NAY HIEN THI
        foreach ($screens as $screen) {
            add_meta_box(
                $id,
                $title,
                $callback,
                $screen,
                'side',     // ✅ Sidebar 欄位
                'default'
            );
        }
        // FUNCTION NAY DE O DAY, DE KHI NAO DUNG DE METABOX THI TA MOI GOI FILE CSS NAY VO 
        //  add_action('admin_enqueue_scripts', array($this, 'add_css_file'));
    }

    public function display($post)
    {
        // thanh an nham bao mat trong wp
        $action = 'dn-metabox-data';
        $name = 'dn-metabox-data-nonce';
        wp_nonce_field($action, $name);
?>
        <div class="row-four-column">
            <div class="col">
                <div class="cell-text">
                    <input type="text" id="metabox-order" name="metabox-order" class='type-number my-input' maxlength='5' placeholder=' <?php _e('The larger the number will show in front') ?>' value="<?php echo get_post_meta($post->ID, '_metabox_order', true); ?>" />
                </div>
            </div>
        </div>


<?php
    }

    public function save($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (is_admin()) {
            //  if (@$_POST['post_type'] == 'slider') {
            if (isset($_POST['metabox-order'])) {
                update_post_meta($post_id, '_metabox_order', esc_attr($_POST['metabox-order']));
            }
            //   }
        }
    }
}
