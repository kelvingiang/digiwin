<?php
/**
 * [2026-10-12] - @author: Kelvin - Tối ưu hóa view-like component:
 * 1. Khắc phục lỗi $post->ID bằng get_the_ID() chuẩn WordPress core.
 * 2. Tự động đếm lượt view an toàn (chống F5 spam bằng cookie 3h).
 * 3. Chuẩn hóa trạng thái Like (localStorage, optimistic UI, rollback khi lỗi, hỗ trợ class .is-liked).
 * 4. Nâng cao UI/UX, hỗ trợ đầy đủ accessibility (aria-label).
 */

$post_id = get_the_ID();
if (!$post_id) {
    return;
}

// 1. Tự động tăng lượt xem khi xem bài viết (bảo vệ bằng cookie 3 giờ tránh F5 spam)
if (!is_admin() && is_singular()) {
    $view_cookie_name = 'dgw_viewed_' . $post_id;
    if (!isset($_COOKIE[$view_cookie_name])) {
        $current_view = (int) get_post_meta($post_id, '_metabox_view', true);
        $current_view++;
        update_post_meta($post_id, '_metabox_view', $current_view);
        setcookie($view_cookie_name, '1', time() + (3 * 3600), COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN);
    }
}

$view_count = (int) get_post_meta($post_id, '_metabox_view', true);
$like_count = (int) get_post_meta($post_id, '_metabox_like', true);
?>

<div class="view_like_area" id="view-like-<?php echo esc_attr($post_id); ?>">
    <div class="view_like_content">
        <!-- View Counter -->
        <div class="view-item view-box" title="<?php echo esc_attr__('Lượt xem', 'dgw'); ?>">
            <span class="view-count"><?php echo esc_html(number_format_i18n($view_count)); ?></span>
            <span class="view-icon">
                <svg class="icon-comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true">
                    <path d="M320 144C254.8 144 201.2 173.6 160.1 211.7C121.6 247.5 95 290 81.4 320C95 350 121.6 392.5 160.1 428.3C201.2 466.4 254.8 496 320 496C385.2 496 438.8 466.4 479.9 428.3C518.4 392.5 545 350 558.6 320C545 290 518.4 247.5 479.9 211.7C438.8 173.6 385.2 144 320 144zM127.4 176.6C174.5 132.8 239.2 96 320 96C400.8 96 465.5 132.8 512.6 176.6C559.4 220.1 590.7 272 605.6 307.7C608.9 315.6 608.9 324.4 605.6 332.3C590.7 368 559.4 420 512.6 463.4C465.5 507.1 400.8 544 320 544C239.2 544 174.5 507.2 127.4 463.4C80.6 419.9 49.3 368 34.4 332.3C31.1 324.4 31.1 315.6 34.4 307.7C49.3 272 80.6 220 127.4 176.6zM320 400C364.2 400 400 364.2 400 320C400 290.4 383.9 264.5 360 250.7C358.6 310.4 310.4 358.6 250.7 360C264.5 383.9 290.4 400 320 400zM240.4 311.6C242.9 311.9 245.4 312 248 312C283.3 312 312 283.3 312 248C312 245.4 311.8 242.9 311.6 240.4C274.2 244.3 244.4 274.1 240.5 311.5zM286 196.6C296.8 193.6 308.2 192.1 319.9 192.1C328.7 192.1 337.4 193 345.7 194.7C346 194.8 346.2 194.8 346.5 194.9C404.4 207.1 447.9 258.6 447.9 320.1C447.9 390.8 390.6 448.1 319.9 448.1C258.3 448.1 206.9 404.6 194.7 346.7C192.9 338.1 191.9 329.2 191.9 320.1C191.9 309.1 193.3 298.3 195.9 288.1C196.1 287.4 196.2 286.8 196.4 286.2C208.3 242.8 242.5 208.6 285.9 196.7z" />
                </svg>
            </span>
        </div>

        <!-- Like Counter & Action Button -->
        <div class="view-item like-box" title="<?php echo esc_attr__('Lượt thích', 'dgw'); ?>">
            <span class="like-count"><?php echo esc_html(number_format_i18n($like_count)); ?></span>
            <div class="like-click">
                <button type="button" 
                        class="like-btn-action" 
                        data-post="<?php echo esc_attr($post_id); ?>" 
                        data-count="<?php echo esc_attr($like_count); ?>" 
                        aria-label="<?php echo esc_attr__('Thích bài viết này', 'dgw'); ?>">
                    <svg class="icon-comment icon-like" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true">
                        <path d="M235.5 102.8C256.3 68 300.5 54 338 71.6L345.2 75.4C380 96.3 394 140.5 376.4 178L376.4 178L362.3 208L472 208L479.4 208.4C515.7 212.1 544 242.8 544 280C544 293.2 540.4 305.4 534.2 316C540.3 326.6 543.9 338.8 544 352C544 370.3 537.1 386.8 526 399.5C527.3 404.8 528 410.3 528 416C528 441.1 515.1 463 495.8 475.9C493.9 511.4 466.4 540.1 431.4 543.6L424 544L319.9 544C301.9 544 284 540.6 267.3 534.1L260.2 531.1L259.5 530.8L252.9 527.6L252.2 527.3L240 520.8C227.7 514.3 216.7 506.1 207.1 496.7C203 523.6 179.8 544.1 151.8 544.1L119.8 544.1C88.9 544.1 63.8 519 63.8 488.1L64 264C64 233.1 89.1 208 120 208L152 208C162.8 208 172.9 211.1 181.5 216.5L231.6 110L232.2 108.8L234.9 103.8L235.5 102.9zM120 256C115.6 256 112 259.6 112 264L112 488C112 492.4 115.6 496 120 496L152 496C156.4 496 160 492.4 160 488L160 264C160 259.6 156.4 256 152 256L120 256zM317.6 115C302.8 108.1 285.3 113.4 276.9 127L274.7 131L217.9 251.9C214.4 259.4 212.4 267.4 211.9 275.6L211.8 279.8L211.8 392.7L212 400.6C214.4 433.3 233.4 462.7 262.7 478.3L274.2 484.4L280.5 487.5C292.9 493.1 306.3 496 319.9 496L424 496L426.4 495.9C438.5 494.7 448 484.4 448 472L447.8 469.4C447.7 468.5 447.6 467.7 447.4 466.8C444.7 454.7 451.7 442.6 463.4 438.8C473.1 435.7 480 426.6 480 416C480 411.7 478.9 407.8 476.9 404.2C470.6 393.1 474.1 379 484.9 372.2C491.7 367.9 496.1 360.4 496.1 352C496.1 344.9 493 338.5 487.9 334C482.7 329.4 479.7 322.9 479.7 316C479.7 309.1 482.7 302.6 487.9 298C493 293.5 496.1 287.1 496.1 280L496 277.6C494.9 266.3 485.9 257.3 474.6 256.2L472.2 256.1L324.7 256.1C316.5 256.1 308.9 251.9 304.5 245C300.1 238.1 299.5 229.3 303 221.9L333 157.6C340 142.6 334.4 124.9 320.5 116.6L317.6 115z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        $(function() {
            var postID = '<?php echo esc_js($post_id); ?>';
            var $area = $('#view-like-' + postID);
            var $btn = $area.find('.like-btn-action');
            var $count = $area.find('.like-count');
            var ajaxUrl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';

            // 1. Khởi tạo trạng thái đã like hay chưa từ localStorage
            if (localStorage.getItem('liked_' + postID)) {
                $btn.addClass('is-liked').prop('disabled', true);
            } else {
                $btn.addClass('like-post');
            }

            // 2. Xử lý sự kiện click Like
            $area.on('click', '.like-post', function(e) {
                e.preventDefault();

                var $currentBtn = $(this);
                if ($currentBtn.hasClass('is-loading') || $currentBtn.hasClass('is-liked')) {
                    return;
                }

                var currentCount = parseInt($currentBtn.attr('data-count'), 10) || 0;
                var optimisticCount = currentCount + 1;

                // Optimistic UI update
                $count.text(optimisticCount);
                $currentBtn.removeClass('like-post').addClass('is-liked is-loading');

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'plus_one_like',
                        postID: postID
                    },
                    dataType: 'json',
                    success: function(res) {
                        $currentBtn.removeClass('is-loading');
                        if (res && (res.success || res.status === 'done' || (res.data && res.data.status === 'done'))) {
                            var finalCount = res.count || (res.data && res.data.count) || optimisticCount;
                            $count.text(finalCount);
                            $currentBtn.attr('data-count', finalCount).prop('disabled', true);
                            localStorage.setItem('liked_' + postID, '1');
                        } else {
                            // Rollback nếu server trả về lỗi
                            $count.text(currentCount);
                            $currentBtn.removeClass('is-liked').addClass('like-post');
                        }
                    },
                    error: function() {
                        // Rollback nếu request thất bại
                        $currentBtn.removeClass('is-loading is-liked').addClass('like-post');
                        $count.text(currentCount);
                    }
                });
            });
        });
    })(jQuery);
</script>