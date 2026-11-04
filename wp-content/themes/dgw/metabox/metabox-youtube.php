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
        $id = 'admin-metabox-youtube';
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
        // 1. Kiểm tra Nonce bảo mật
        if (!isset($_POST['admin-metabox-data-nonce']) || !wp_verify_nonce($_POST['admin-metabox-data-nonce'], 'admin-metabox-data')) {
            return;
        }

        // 2. Bỏ qua nếu là autosave của WordPress
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 3. Kiểm tra quyền hạn người dùng
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // 4. [04/11/2026] - Hỗ trợ cập nhật link mới hoặc xóa rỗng (xóa post meta) khi người dùng để trống ô input
        if (isset($_POST['txt-youtube'])) {
            $youtube_link = trim(sanitize_text_field(wp_unslash($_POST['txt-youtube'])));
            if ($youtube_link !== '') {
                update_post_meta($post_id, '_metabox_youtube', $youtube_link);
            } else {
                delete_post_meta($post_id, '_metabox_youtube');
            }
        }
    }
}
