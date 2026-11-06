<?php
/**
 * [02/10/2026] - Bổ sung slug vào danh sách category trả về
 * [05/11/2026] - Hỗ trợ lọc theo ngôn ngữ hiện hành ($lang) và tương thích chuẩn Term Meta đa ngôn ngữ mới (term_lang)
 */

/**
 * Ngày thực hiện: 05/11/2026
 * Chức năng: Lấy danh mục taxonomy theo đúng ngôn ngữ hiện hành
 * Tương thích: Tự động hỗ trợ cả taxonomy dùng term_lang trong wp_termmeta và taxonomy cũ dùng wp_options
 *
 * @param string $cate Tên Taxonomy
 * @param string|null $lang Ngôn ngữ ('vn', 'cn'). Nếu null sẽ tự lấy theo dgw_get_lang()
 * @return array Danh sách danh mục đã sắp xếp theo thứ tự order
 */
function getCategories(string $cate, ?string $lang = null): array
{
    $current_lang = $lang ?: (function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn');
    $arr = array();

    $argsCate = array(
        'type'       => 'post',
        'number'     => 100,
        'taxonomy'   => $cate,
        'hide_empty' => 0,
        'parent'     => 0,
    );

    // Kiểm tra xem taxonomy này có term nào sử dụng meta term_lang không
    $has_term_lang = get_terms(array(
        'taxonomy'   => $cate,
        'hide_empty' => 0,
        'number'     => 1,
        'meta_key'   => 'term_lang',
    ));

    if (!empty($has_term_lang) && !is_wp_error($has_term_lang)) {
        $argsCate['meta_query'] = array(
            array(
                'key'     => 'term_lang',
                'value'   => $current_lang,
                'compare' => '=',
            ),
        );
    }

    $categories = get_categories($argsCate);

    if ($categories && !is_wp_error($categories)) {
        foreach ($categories as $value) {
            $meta_order = get_term_meta($value->term_id, 'cate_order', true);
            $meta_lang  = get_term_meta($value->term_id, 'term_lang', true);

            $option = get_option("option_" . $cate . "_" . $value->term_id);

            // Nếu term đã có term_lang chuẩn thì lấy luôn name của term, nếu cũ thì fallback qua option
            $name  = !empty($meta_lang) ? $value->name : ($option['cate_' . $current_lang] ?? $value->name);
            $order = ($meta_order !== '') ? (int)$meta_order : (int)($option['cate_order'] ?? 0);

            $arr[$value->term_id] = array(
                'ID'    => $value->term_id,
                'slug'  => $value->slug,
                'name'  => $name,
                'class' => 'menu-main-sub-1-item',
                'order' => $order,
                'sub'   => '',
            );
        }
    }

    usort($arr, "cmp");

    return $arr;
}

/**
 * Ngày thực hiện: 05/11/2026
 * Chức năng: Lấy danh mục con kèm đường dẫn phân trang theo đúng ngôn ngữ hiện hành
 */
function getAllCategories(string $cate, int $parent, string $page, ?string $lang = null): array
{
    $current_lang = $lang ?: (function_exists('dgw_get_lang') ? dgw_get_lang() : 'vn');
    $arr = array();

    $argsCate = array(
        'type'       => 'post',
        'number'     => 100,
        'taxonomy'   => $cate,
        'hide_empty' => 0,
        'parent'     => $parent,
    );

    $has_term_lang = get_terms(array(
        'taxonomy'   => $cate,
        'hide_empty' => 0,
        'number'     => 1,
        'meta_key'   => 'term_lang',
    ));

    if (!empty($has_term_lang) && !is_wp_error($has_term_lang)) {
        $argsCate['meta_query'] = array(
            array(
                'key'     => 'term_lang',
                'value'   => $current_lang,
                'compare' => '=',
            ),
        );
    }

    $categories = get_categories($argsCate);

    if ($categories && !is_wp_error($categories)) {
        foreach ($categories as $value) {
            $meta_order = get_term_meta($value->term_id, 'cate_order', true);
            $meta_lang  = get_term_meta($value->term_id, 'term_lang', true);

            $option = get_option("option_" . $cate . "_" . $value->term_id);

            $name  = !empty($meta_lang) ? $value->name : ($option['cate_' . $current_lang] ?? $value->name);
            $order = ($meta_order !== '') ? (int)$meta_order : (int)($option['cate_order'] ?? 0);

            $arr[$value->term_id] = array(
                'ID'    => $value->term_id,
                'slug'  => $value->slug,
                'name'  => $name,
                'class' => "",
                'order' => $order,
                'page'  => $page,
                'sub'   => '',
            );
        }
    }

    usort($arr, "cmp");

    return $arr;
}
