<?php

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php ';
}

class Model_Pop_up extends WP_List_Table
{

    private $_pre_page = 30;
    private $_sql;
    private $_stt = 1;
    private $table;

    public function __construct($args = array())
    {
        $args = array(
            'plural' => 'ID', // GIA TRI NAY TUONG UNG VOI key TRONG table
            'singular' => 'ID', // GIA TRI NAY TUONG UNG VOI key TRONG table
            'ajax' => FALSE,
            'screen' => null,
        );
        parent::__construct($args);
        global $wpdb;
        $this->table = $wpdb->prefix . 'pop_up';
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();  // NHUNG GI CAN HIEN THI TREN BANG 
        $hidden = $this->get_hidden_columns(); // NHUNG COT TA SE AN DI
        $sorttable = $this->get_sortable_columns(); // CAC COT DC SAP XEP TANG HOAC GIAM DAN

        $this->_column_headers = array($columns, $hidden, $sorttable); //DUA 3 GIA TRI TREN VAO DAY DE SHOW DU LIEU
        $this->items = $this->table_data(); // LAY DU LIEU TU DATABASE

        $total_items = $this->total_items(); // TONG SO DONG DA LIEU
        $per_page = $this->_pre_page; // SO TRANG 
        $total_pages = ceil($total_items / $per_page); // TONG SO TRANG
        // PHAN TRANG
        $args = array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => $total_pages
        );
        $this->set_pagination_args($args);
    }

    public function get_columns()
    {
        $arr = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('票提'),
            'status' => __('狀態'),
            'img' => __('圖片'),
            'link' => __('連接'),
            'date' => __('Create Date'),
        );
        return $arr;
    }

    // ====KHAI  BAO CAC COT BI AN DI TREN GRIDVIEW=====================================
    public function get_hidden_columns()
    {
        return array();
    }

    // ==== CAC COT AP DUNG  SAP XEP  ============================================
    public function get_sortable_columns()
    {
        return array(
            'status' => array('status', true),
            'date' => array('create_date', true),
        );
    }

    private function table_data()
    {
        global $wpdb;
        // LAY GIA TRI SAP XEP DU LIEU TREN COT
        // [13/07/2026] Fix: Thay thế cách lấy biến $_GET kết hợp kiểm tra !empty() để tránh lỗi "Undefined array key" khi key không tồn tại trên PHP 8+.
        $orderby = !empty($_GET['orderby']) ? $_GET['orderby'] : 'ID';
        $order = !empty($_GET['order']) ? $_GET['order'] : 'DESC';
        $sql = 'SELECT m.* FROM ' . $this->table . ' AS m  ';
        $whereArr = array();  // TAO MANG WHERE ============

        if (getParams('customvar') == 'trash') {
            $whereArr[] = "(trash = 1)";
        } else {
            $whereArr[] = "(trash = 0)";
        }

        if (getParams('s') != ' ') {
            $s = esc_sql(getParams('s'));
            $whereArr[] = "(m.title  LIKE '%$s%')";
        }

        // CHUYEN CAC GIA TRI where KET VOI NHAU BOI and ==========================
        if (count($whereArr) > 0) {
            $sql .= " WHERE " . join(" AND ", $whereArr);
        }
        // orderby
        $sql .= 'ORDER BY m.' . esc_sql($orderby) . ' ' . esc_sql($order);
        $this->_sql = $sql;
        //LAY GIA TRI PHAN TRANG PAGEING
        $paged = max(1, @$_REQUEST['paged']);
        $offset = ($paged - 1) * $this->_pre_page;

        $sql .= ' LIMIT  ' . $this->_pre_page . ' OFFSET ' . $offset;
        // LAY KET QUA  THONG QUA CAU sql
        $data = $wpdb->get_results($sql, ARRAY_A);

        return $data;
    }

    // TINH TONG SO DONG DU LIEU  DE AP DUNG CHO VIEC PHAN TRANG
    public function total_items()
    {
        global $wpdb;
        return $wpdb->query($this->_sql);
    }

    // SO TONG ITEM DUNG DE PHAN TRANG
    public function total_list()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table");
    }

    // SO TONG ITEM TRONG TRASH(SO RAC)
    public function total_trash()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE trash = 1");
    }

    // SO TONG ITEM HIEN HANH
    public function total_publish()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM $this->table WHERE trash = 0");
    }

    function get_views()
    {
        $views = array();
        $current = (!empty($_REQUEST['customvar']) ? $_REQUEST['customvar'] : 'all');

        //All link
        $class = ($current == 'all' ? ' class="current"' : '');
        $all_url = remove_query_arg('customvar');
        $views['all'] = "<strong>" . __('All') . " (" . $this->total_list() . ")</strong>";

        //Foo link
        $foo_url = add_query_arg('customvar', 'published');
        $class = ($current == 'foo' ? ' class="current"' : '');
        $views['foo'] = "<a href='{$foo_url}' {$class} > " . __('Published') . " (" . $this->total_publish() . ")</a>";

        //Bar link
        $bar_url = add_query_arg('customvar', 'trash');
        $class = ($current == 'bar' ? ' class="current"' : '');
        $views['bar'] = "<a href='{$bar_url}' {$class} >" . __('Trash') . "(" . $this->total_trash() . ")</a>";

        return $views;
    }

    // CAC ITEM TRONG SELECT BOX CHUC NANG 'UNG DUNG'
    public function get_bulk_actions()
    {
        if (@$_GET['customvar'] == 'trash') {
            $actions = array(
                'restore' => __('Restore'),
                'delete' => __('Delete Permanently')
            );
        } else {
            $actions = array(
                'trash' => __('Trash'),
            );
        }
        return $actions;
    }

    //  NOI DUNG HIEN THI CUA COT TRONG GRID
    public function column_cb($item)
    {
        $singular = $this->_args['singular'];
        $html = '<input type="checkbox" name="' . $singular . '[]" value="' . $item['ID'] . '"/>';
        return $html;
    }



    public function column_title($item)
    {
        $page = getParams('page');

        if (@$_GET['customvar'] == 'trash') {
            $actions = array(
                'restore' => '<a href="?page=' . $page . '&action=restore&id=' . $item['ID'] . '">' . __('Restore') . '</a>',
                'delete' => '<a href="?page=' . $page . '&action=delete&id=' . $item['ID'] . '">' . __('Delete Permanently') . '</a>',
            );
        } else {
            // echo $item['status'];
            if ($item['status'] == '0' || $item['status'] == 0) {
                $actions = array(
                    'active' => '<a href="?page=' . $page . '&action=active&id=' . $item['ID'] . '">啟用</a>',
                    'edit' => '<a href="?page=' . $page . '&action=edit&id=' . $item['ID'] . '">' . __('Edit') . '</a>',
                    'trash' => '<a href="?page=' . $page . '&action=trash&id=' . $item['ID'] . '">' . __('Trash') . '</a>',
                );
            } elseif ($item['status'] == '1' || $item['status'] == 1) {
                $actions = array(
                    'passive' => '<a href="?page=' . $page . '&action=passive&id=' . $item['ID'] . '">停止</a>',
                    'edit' => '<a href="?page=' . $page . '&action=edit&id=' . $item['ID'] . '">' . __('Edit') . '</a>',
                    'trash' => '<a href="?page=' . $page . '&action=trash&id=' . $item['ID'] . '">' . __('Trash') . '</a>',
                );
            } else {
                // 添加 else 條件，處理其他 status 值
                $actions = array(
                    'edit' => '<a href="?page=' . $page . '&action=edit&id=' . $item['ID'] . '">' . __('Edit') . '</a>',
                    'trash' => '<a href="?page=' . $page . '&action=trash&id=' . $item['ID'] . '">' . __('Trash') . '</a>',
                );
            }
        }

        $html = '<strong><a href="?page=' . $page . '&action=edit&id=' . $item['ID'] . '">' . $item['title'] . '</a></strong>' . $this->row_actions($actions);
        return $html;
    }

    public function column_img($item)
    {
        echo "<div>";
        echo '<img src="' . PART_IMAGES . 'pop-up/' . $item['img_cn'] . '" alt="logo image cn" />';
        echo "</div>";
    }

    public function column_link($item)
    {
        echo $item['link_vn'] . '<br>' . $item['link_cn'];
    }

    public function column_status($item)
    {
        if ($item['status'] == '1') {
            echo "<div class='show-home'></div>";
        };
    }

    public function column_date($item)
    {
        echo $item['create_date'];
    }
}
