<?php

function ColorCode($id)
{
    switch ($id) {
        case "1":
            $color = 'black';
            break;
        case "2":
            $color = 'red';
            break;
        case "3":
            $color = 'blue';
            break;
        case "4":
            $color = 'pink';
            break;
        case "5":
            $color = 'silver';
            break;
        case "6":
            $color = 'green';
            break;
    }
    return $color;
}

/*================================================================
SEARCH  POST  BY METABOX 
==================================================================*/

function get_category_summary($id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT summary_vn, summary_cn, img FROM  $table WHERE ID = $id ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_product_category()
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT * FROM  $table WHERE kind = 'p' ORDER BY `orders` DESC  ";
    $row = $wpdb->get_results($sql, ARRAY_A);
    return $row;
}

function get_category_by_id($ID)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT name_cn, name_vn FROM  $table WHERE  ID = $ID  ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_show_name($kind, $val)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product_category';
    $sql = "SELECT * FROM  $table WHERE kind = '$kind' AND val = '$val' ";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_product($id)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product';
    $sql = "SELECT * FROM  $table WHERE ID = $id";
    $row = $wpdb->get_row($sql, ARRAY_A);
    return $row;
}

function get_products($cate)
{
    global $wpdb;
    $table = $wpdb->prefix . 'product';
    if ($cate == '') {
        $sql = "SELECT * FROM  $table WHERE trash = 0 ORDER BY `is_order` DESC";
    } else {
        $sql = "SELECT * FROM  $table WHERE trash = 0 AND category = $cate ORDER BY `is_order` DESC";
    }
    $row = $wpdb->get_results($sql, ARRAY_A);
    return $row;
}


/**
 * Cấu hình toàn bộ các trường của form thành viên
 */
function dgw_get_auth_fields_config(): array {
    return [
        ['id' => 'email',      'label' => __('E-mail', 'dgw'),    'col' => 'two', 'type' => 'email',    'place' => 'example@email.com'],
        ['id' => 'password',   'label' => __('Password', 'dgw'),  'col' => 'two', 'type' => 'password', 'place' => 'Tối thiểu 6 ký tự'],
        
        ['type' => 'separator'],

        ['id' => 'company',    'label' => __('Company Name', 'dgw'), 'col' => 'one', 'type' => 'text'],
        ['id' => 'username',   'label' => __('Full Name', 'dgw'),    'col' => 'two', 'type' => 'text', 'place' => 'Nguyễn Văn A'],
        ['id' => 'position',   'label' => __('Position', 'dgw'),     'col' => 'two', 'type' => 'text'],
        ['id' => 'phone',      'label' => __('Phone', 'dgw'),        'col' => 'two', 'type' => 'text', 'class' => 'type-phone-more', 'max' => 15],
        ['id' => 'tax',        'label' => __('Tax Number', 'dgw'),   'col' => 'two', 'type' => 'text', 'class' => 'type-number', 'max' => 13],
        ['id' => 'industry',   'label' => __('Industry', 'dgw'),     'col' => 'two', 'type' => 'text'],
        ['id' => 'department', 'label' => __('Department', 'dgw'),   'col' => 'two', 'type' => 'text'],
    ];
}

/**
 * Hàm render Full Form (Bao gồm Fields, Button và Message)
 * * @param string $prefix Tiền tố cho ID (reg- hoặc chang-)
 * @param string $button_text Nội dung nút bấm
 * @param object|null $data Dữ liệu user nếu có
 */
function dgw_render_auth_form_full(string $prefix = 'reg-', string $button_text = '', $data = null): void {
    $fields = dgw_get_auth_fields_config();
    $current_col = '';
    $button_text = $button_text ?: __('Register', 'dgw');

    // 1. Render các Input Fields
    foreach ($fields as $field) {
        if ($field['type'] === 'separator') {
            if ($current_col !== '') { echo '</div>'; $current_col = ''; }
            echo '<hr class="hr-style">';
            continue;
        }

        if ($current_col !== $field['col']) {
            if ($current_col !== '') echo '</div>'; 
            echo '<div class="' . esc_attr($field['col']) . '-columns">';
            $current_col = $field['col'];
        }

        $id    = $field['id'];
        $val   = $data ? ($data->$id ?? '') : '';
        $type  = $field['type'] ?? 'text';
        $class = $field['class'] ?? '';
        $max   = isset($field['max']) ? 'maxlength="' . $field['max'] . '"' : '';
        $place = isset($field['place']) ? 'placeholder="' . esc_attr($field['place']) . '"' : '';
        $input_mode = (str_contains($class, 'number') || str_contains($class, 'phone')) ? 'inputmode="numeric"' : '';

        ?>
        <div class="row-cell">
            <label><?php echo esc_html($field['label']); ?></label>
            <input type="<?php echo esc_attr($type); ?>" 
                   id="<?php echo esc_attr($prefix . $id); ?>" 
                   class="<?php echo esc_attr($class); ?>" 
                   value="<?php echo esc_attr($val); ?>" 
                   <?php echo $place . ' ' . $max . ' ' . $input_mode; ?> 
                   autocomplete="<?php echo ($type === 'password') ? 'new-password' : 'on'; ?>" />
        </div>
        <?php
    }
    
    if ($current_col !== '') echo '</div>'; // Đóng div cột cuối cùng

    // 2. Render phần Button và Message (Đã đưa vào trong function)
    ?>
    <div class="btn-space">
        <button id="btn-<?php echo ($prefix === 'reg-') ? 'register' : 'change-info'; ?>" class="btn-my-style">
            <?php echo esc_html($button_text); ?>
        </button>
        <p id="<?php echo ($prefix === 'reg-') ? 'register' : 'change-info'; ?>-msg" class="msg"></p>
    </div>
    <?php
}




