<?php 
//===== thay doi cac cot mac dinh cua post=================================================================
add_filter('manage_posts_columns', 'set_custom_edit_columns');

function set_custom_edit_columns($columns)
{
    unset($columns['tags']);
    unset($columns['comments']);
    unset($columns['date']);

    $columns['author'] = __('Author');
    $columns['categories'] = __('分類');
    $columns['home'] = __('首頁');
    $columns['language'] = __('Language');
    $columns['order'] = __('Show Order');
    $columns['create-date'] = __('創建日期');
    return $columns;
}

add_action('manage_posts_custom_column', 'Custom_Post_RenderCols');

function Custom_post_RenderCols($columns)
{
    global $post;
    switch ($columns) {

        case 'home':
            if ((get_post_meta($post->ID, '_metabox_home', true))) {
                echo "<div class='show-home'></div>";
            }
            break;
        case 'language':
            _e(get_post_meta($post->ID, '_metabox_langguage', true));
            break;

        case 'order':
            echo get_post_meta($post->ID, '_metabox_order', true);
            break;

        case 'create-date':
            echo get_the_date('d/m/Y');
            break;

        default:
            break;
    }
}

add_filter('manage_edit-post_sortable_columns', 'add_date_sortable_column');

function add_date_sortable_column($columns)
{
    $columns['create-date'] = 'date';
    $columns['order'] = 'order';
    return $columns;
}