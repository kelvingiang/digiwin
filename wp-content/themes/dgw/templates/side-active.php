<?php
/**
 * [10/11/2026] - Template hiển thị danh mục hoạt động dạng Tab Widget 1 khối đồng nhất
 * Cho phép chuyển đổi tab linh hoạt bằng JavaScript, tự động tải các bài viết thuộc danh mục tương ứng
 */
?>
<div class="side-space">
    <div class="side-tab">
        <?php
        $cate = 'active_category';
        $page = 'actives';
        $data = getAllCategories($cate, 0, $page);
        $stt = 1;
        foreach ($data as $val) : ?>
            <div class="tab-title title-tab-<?php echo esc_attr($val['ID']); ?> <?php echo ($stt == 1 ? 'active' : ''); ?>"
                data-target="content-tab-<?php echo esc_attr($val['ID']); ?>">
                <label><?php echo esc_html($val['name']); ?></label>
            </div>
        <?php
            $stt = $stt + 1;
        endforeach; ?>
    </div>

    <div class="side-tab-content">
        <?php foreach ($data as $index => $val) : ?>
            <div class="card-list content-tab-<?php echo esc_attr($val['ID']); ?> <?php echo ($index == 0 ? 'active' : ''); ?>">
                <?php
                $tax = 'active_category';
                $tax_id = $val['ID'];
                // [2026-10-14] - @author: Kelvin - Đổi tên biến sang $side_active_query để tránh ghi đè biến toàn cục $wp_query
                $side_active_query = getCustomPostAtSideCate('active', 5, $tax, $tax_id);
                if ($side_active_query && $side_active_query->have_posts()) :
                    while ($side_active_query->have_posts()) :
                        $side_active_query->the_post();
                ?>
                        <a class="card-item" href="<?php the_permalink(); ?>">
                            <div class="card-title"><?php the_title(); ?></div>
                        </a>
                <?php
                    endwhile;
                endif;
                wp_reset_postdata(); ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // [10/11/2026] - Xử lý chuyển đổi nội dung giữa các tab danh mục
    jQuery(document).ready(function($) {
        $('.side-space .tab-title').on('click', function() {
            var target = $(this).data('target');

            // Cập nhật trạng thái active của tab
            $(this).closest('.side-space').find('.tab-title').removeClass('active');
            $(this).addClass('active');

            // Cập nhật trạng thái active của danh sách bài viết
            $(this).closest('.side-space').find('.card-list').removeClass('active');
            $(this).closest('.side-space').find('.' + target).addClass('active');
        });
    });
</script>
