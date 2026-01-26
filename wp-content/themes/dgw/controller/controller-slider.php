<?php

class Controller_Slider
{

    public function __construct()
    {
        add_action('init', array($this, 'register_custom_post'));
        add_action('manage_edit-slider_columns', array($this, 'manage_columns'));
        add_action('manage_slider_posts_custom_column', array($this, 'render_columns'));

        add_filter('manage_edit-slider_sortable_columns', array($this, 'sortable_views_column'));
        add_filter('request', array($this, 'sort_views_column'));
    }

    public function register_custom_post()
    {
        $labels = array(
            'name' => __('Slider') . '1300 x 430',
            'singular_name' => __('Slider'),
            'add_new' => __('Add New'),
            'add_new_item' => __('Add Item'),
            'edit_item' => __('Edit'),
            'new_item' => __('Add Item'),
            'all_items' => __('All Item'),
            'view_item' => __('View Item'),
            'search_items' => __('Search'),
            'not_found' => __('No slides found.'),
            'not_found_in_trash' => __('No found in Trash.'),
            'parent_item_colon' => '',
            'menu_name' => __('Slider')
        );
        $args = array(
            'labels' => $labels,
            'public' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => TRUE,
            'menu_icon' => PART_ICON . 'icon-link.png',
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 6,
            'supports' => array('title', 'thumbnail', 'editor'),
        );
        register_post_type('slider', $args);
    }

    //==== QUAN LY COT HIEN THI TRON BANG   
    public function manage_columns($columns)
    {
        unset($columns['home']); // an cot ngay mac dinh
        unset($columns['categories']); // an cot ngay mac dinh
        //==== THEM COT VA BAN
        return $columns;
    }

    //==== HIEN THI NOI DUNG TRONG COT
    public function render_columns($columns)
    {
        global $post;
    }

    //====== SAP SEP THEO TRINH TU
    public function sortable_views_column($col)
    {
        $col['order'] = 'order';
        $col['create-date'] = 'create-date';
        return $col;
    }

    public function sort_views_column($vars)
    {
        if (isset($vars['orderby']) && 'order' == $vars['orderby']) {
            $vars = array_merge(
                $vars,
                array(
                    'meta_key' => '_metabox_order', //Custom field key
                    'orderby' => '_metabox_order' //Custom field value (number)
                )
            );
        }
        return $vars;
    }
}
