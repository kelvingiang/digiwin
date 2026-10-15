<?php
/**
 * [15/10/2026] - Redesign languages-box: Hiện đại hóa bộ chuyển đổi ngôn ngữ dạng Segmented Pill Switcher
 * Tích hợp SSR xác định ngôn ngữ hiển thị ban đầu chống giật giao diện (Zero CLS)
 * Tương thích ngược toàn bộ AJAX handler change_languages và hỗ trợ Accessibility (A11y)
 */
$data = null;
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}
// Xác định ngôn ngữ hiện tại từ cookie, mặc định là 'vn'
$current_lang = (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], ['cn', 'vn'], true)) ? $_COOKIE['site_lang'] : 'vn';

/**
 * [15/10/2026] - @author: Senior Full-Stack Developer
 * Mục đích: Nhận diện nếu đang ở trang chi tiết bài viết (single page) thì lấy chính xác URL danh mục (cate) tương ứng.
 * Khi người dùng đổi ngôn ngữ, hệ thống sẽ chuyển hướng về trang cate (vd: /cases/cate/electronics/) thay vì reload trang single.
 */
$single_cate_redirect_url = '';
if (is_single()) {
    $queried_obj = get_queried_object();
    if ($queried_obj instanceof WP_Post) {
        $cpt_modules = [
            'casestudies' => ['page' => 'cases',      'tax' => 'casestudies_category'],
            'solutions'   => ['page' => 'solution',   'tax' => 'solutions_category'],
            'resources'   => ['page' => 'resource',   'tax' => 'resources_category'],
            'active'      => ['page' => 'activities', 'tax' => 'active_category'],
            'services'    => ['page' => 'services',   'tax' => 'services_category'],
            'industries'  => ['page' => 'industries', 'tax' => 'industries_category'],
            'joinus'      => ['page' => 'joinus',     'tax' => 'joinus_category'],
        ];

        $post_type = $queried_obj->post_type;
        if (isset($cpt_modules[$post_type])) {
            $conf      = $cpt_modules[$post_type];
            $cate_slug = get_query_var('cate');

            if (empty($cate_slug)) {
                $terms = wp_get_object_terms($queried_obj->ID, $conf['tax']);
                if (!empty($terms) && !is_wp_error($terms)) {
                    $cate_slug = $terms[0]->slug;
                }
            }

            if (!empty($cate_slug)) {
                $single_cate_redirect_url = home_url('/' . $conf['page'] . '/cate/' . $cate_slug . '/');
            } else {
                $single_cate_redirect_url = home_url('/' . $conf['page'] . '/');
            }
        } elseif ($post_type === 'post') {
            $categories = get_the_category($queried_obj->ID);
            if (!empty($categories) && !is_wp_error($categories)) {
                $single_cate_redirect_url = get_category_link($categories[0]->term_id);
            }
        }
    }
}
?>
<div class="languages-container">
    <div class="login-box">
        <?php if ($data) : ?>
            <div class="link-login-welcome">
                <a href="<?php echo esc_url(home_url('/member')); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="15" height="15" aria-hidden="true"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                        <path d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z" />
                    </svg>
                    <?php echo esc_html($data->username); ?>
                </a>
            </div>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/member')); ?>" class="link-login">
                <?php echo esc_html(__('Login-Register', 'dgw')); ?>
            </a>
        <?php endif ?>
    </div>

    <!-- [15/10/2026] - Giao diện chuyển đổi ngôn ngữ hiện đại dạng Segmented Pill kèm thuộc tính data-redirect-cate -->
    <div class="languages-box" role="group" aria-label="<?php esc_attr_e('Language Switcher', 'dgw'); ?>" data-redirect-cate="<?php echo esc_url($single_cate_redirect_url); ?>">
        <div class="languages-box__switcher">
            <button type="button"
                class="languages-box__item link-languages <?php echo ($current_lang === 'cn') ? 'is-active not-link' : ''; ?>"
                data-type="cn"
                title="繁體中文"
                aria-label="繁體中文"
                <?php echo ($current_lang === 'cn') ? 'aria-pressed="true"' : 'aria-pressed="false"'; ?>
                onclick="changeLanguages(this)">
                <span>中</span>
            </button>
            <button type="button"
                class="languages-box__item link-languages <?php echo ($current_lang === 'vn') ? 'is-active not-link' : ''; ?>"
                data-type="vn"
                title="Tiếng Việt"
                aria-label="Tiếng Việt"
                <?php echo ($current_lang === 'vn') ? 'aria-pressed="true"' : 'aria-pressed="false"'; ?>
                onclick="changeLanguages(this)">
                <span style="font-size: 10px;">VI</span>
            </button>
        </div>
    </div>
</div>

<script>
    /**
     * [15/10/2026] - @author: Senior Full-Stack Developer
     * Xử lý chuyển đổi ngôn ngữ: nếu ở trang single thì chuyển về trang cate (vd: /cases/cate/electronics/), 
     * các trang khác tự động reload để bảo toàn ngữ cảnh và trải nghiệm người dùng.
     */
    if (typeof window.changeLanguages === 'undefined') {
        window.changeLanguages = function(el) {
            var $el = jQuery(el);
            var type = $el.attr('data-type');
            var currentLang = "<?php echo esc_js($current_lang); ?>";

            // Nếu đang chọn ngôn ngữ hiện tại hoặc đang loading thì không thực hiện lại
            if (type === currentLang || $el.hasClass('is-active') || $el.hasClass('is-loading')) {
                return;
            }

            // Lấy URL trang danh mục đích từ data attribute hoặc PHP context
            var redirectCateUrl = $el.closest('.languages-box').attr('data-redirect-cate') || "<?php echo esc_url($single_cate_redirect_url); ?>";

            // Hiệu ứng visual loading tức thì cho UX
            $el.addClass('is-loading');

            jQuery.ajax({
                url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'change_languages',
                    type: type
                },
                success: function(res) {
                    if (res && res.status === 'ok') {
                        // 1. Nếu đang ở trang single có URL cate hợp lệ: chuyển hướng về cate
                        if (redirectCateUrl && redirectCateUrl.trim() !== '') {
                            window.location.href = redirectCateUrl;
                            return;
                        }

                        // 2. Fallback kiểm tra URL nếu đang ở URL dạng /{module}/cate/{term}/{post_slug}/
                        var currentPath = window.location.pathname;
                        var cateMatch = currentPath.match(/^(\/.*?\/(?:cate|category)\/[^\/]+)\/.+/);
                        if (cateMatch && cateMatch[1]) {
                            window.location.href = window.location.origin + cateMatch[1] + '/';
                            return;
                        }

                        // 3. Trường hợp ở trang chủ hoặc trang thường khác thì reload
                        window.location.reload();
                    } else {
                        $el.removeClass('is-loading');
                    }
                },
                error: function(err) {
                    console.error('AJAX Error:', err);
                    $el.removeClass('is-loading');
                }
            });
        };
    }
</script>