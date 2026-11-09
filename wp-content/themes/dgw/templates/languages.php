<?php
/**
 * [15/10/2026] - Redesign languages-box: Hiện đại hóa bộ chuyển đổi ngôn ngữ dạng Segmented Pill Switcher
 * Tích hợp SSR xác định ngôn ngữ hiển thị ban đầu chống giật giao diện (Zero CLS)
 * Tương thích ngược toàn bộ AJAX handler change_languages và hỗ trợ Accessibility (A11y)
 * [05/11/2026] - Lấy ngôn ngữ hiện hành ưu tiên qua dgw_get_lang() để đồng bộ với Single Post
 * [06/11/2026] - Hỗ trợ tự động ánh xạ URL bản dịch đối ứng cho cả trang Category lẫn Single Post
 */
$data = null;
if (!empty($_COOKIE['custom_session'])) {
    $data = get_member_information($_COOKIE['custom_session']);
}
// Xác định ngôn ngữ hiện tại từ dgw_get_lang(), mặc định là 'vn'
$current_lang = function_exists('dgw_get_lang') ? dgw_get_lang() : ((!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], ['cn', 'vn'], true)) ? $_COOKIE['site_lang'] : 'vn');

/**
 * [06/11/2026] - @author: Senior Full-Stack Developer
 * Mục đích: Nhận diện URL điều hướng chuẩn xác khi đổi ngôn ngữ:
 * 1. Nếu đang ở trang danh mục (cate): Ánh xạ sang term đối ứng trong cùng Translation Group (vd: tiptop_vn <-> tiptop_cn)
 * 2. Nếu đang ở trang chi tiết bài viết (single page): Chuyển hướng về trang danh mục tương ứng theo ngôn ngữ đích.
 */
$redirect_url_cn = '';
$redirect_url_vn = '';
$single_cate_redirect_url = '';

$current_cate_slug = function_exists('dgw_detect_current_category_slug') ? dgw_detect_current_category_slug() : (get_query_var('cate') ?: '');

if (!empty($current_cate_slug)) {
    // 1. Đang ở trang Category: Lấy term bản dịch đối ứng
    $cpt_page = get_query_var('pagename');
    if (empty($cpt_page) && !empty($_SERVER['REQUEST_URI'])) {
        $path_parts = explode('/', trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
        $cpt_page = $path_parts[0] ?? 'solution';
    }
    $cpt_page = !empty($cpt_page) ? $cpt_page : 'solution';

    $term_cn = function_exists('dgw_get_translated_term') ? dgw_get_translated_term($current_cate_slug, 'cn') : null;
    $term_vn = function_exists('dgw_get_translated_term') ? dgw_get_translated_term($current_cate_slug, 'vn') : null;

    if ($term_cn instanceof WP_Term) {
        $redirect_url_cn = home_url('/' . $cpt_page . '/cate/' . $term_cn->slug . '/');
    }
    if ($term_vn instanceof WP_Term) {
        $redirect_url_vn = home_url('/' . $cpt_page . '/cate/' . $term_vn->slug . '/');
    }
} elseif (is_single()) {
    // 2. Đang ở trang Single Post: Chuyển hướng về cate tương ứng
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
                $term_cn = function_exists('dgw_get_translated_term') ? dgw_get_translated_term($cate_slug, 'cn', $conf['tax']) : null;
                $term_vn = function_exists('dgw_get_translated_term') ? dgw_get_translated_term($cate_slug, 'vn', $conf['tax']) : null;

                $slug_cn = ($term_cn instanceof WP_Term) ? $term_cn->slug : $cate_slug;
                $slug_vn = ($term_vn instanceof WP_Term) ? $term_vn->slug : $cate_slug;

                $redirect_url_cn = home_url('/' . $conf['page'] . '/cate/' . $slug_cn . '/');
                $redirect_url_vn = home_url('/' . $conf['page'] . '/cate/' . $slug_vn . '/');
                $single_cate_redirect_url = ($current_lang === 'cn') ? $redirect_url_cn : $redirect_url_vn;
            } else {
                $redirect_url_cn = home_url('/' . $conf['page'] . '/');
                $redirect_url_vn = home_url('/' . $conf['page'] . '/');
                $single_cate_redirect_url = home_url('/' . $conf['page'] . '/');
            }
        } elseif ($post_type === 'post') {
            $categories = get_the_category($queried_obj->ID);
            if (!empty($categories) && !is_wp_error($categories)) {
                $single_cate_redirect_url = get_category_link($categories[0]->term_id);
                $redirect_url_cn = $single_cate_redirect_url;
                $redirect_url_vn = $single_cate_redirect_url;
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

    <!-- [15/10/2026] - Giao diện chuyển đổi ngôn ngữ hiện đại dạng Segmented Pill kèm thuộc tính điều hướng -->
    <div class="languages-box" role="group" aria-label="<?php esc_attr_e('Language Switcher', 'dgw'); ?>" data-redirect-cate="<?php echo esc_url($single_cate_redirect_url); ?>">
        <div class="languages-box__switcher">
            <button type="button"
                class="languages-box__item link-languages <?php echo ($current_lang === 'cn') ? 'is-active not-link' : ''; ?>"
                data-type="cn"
                data-redirect="<?php echo esc_url($redirect_url_cn); ?>"
                title="繁體中文"
                aria-label="繁體中文"
                <?php echo ($current_lang === 'cn') ? 'aria-pressed="true"' : 'aria-pressed="false"'; ?>
                onclick="changeLanguages(this)">
                <span>中</span>
            </button>
            <button type="button"
                class="languages-box__item link-languages <?php echo ($current_lang === 'vn') ? 'is-active not-link' : ''; ?>"
                data-type="vn"
                data-redirect="<?php echo esc_url($redirect_url_vn); ?>"
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
     * [06/11/2026] - Xử lý chuyển đổi ngôn ngữ thông minh:
     * - Nếu có URL đích chuyên biệt (data-redirect của term đối ứng hoặc data-redirect-cate): chuyển hướng trực tiếp
     * - Nếu đang ở URL chi tiết bài viết: chuyển hướng về trang cate
     * - Các trang khác: tự động reload để bảo toàn ngữ cảnh và cập nhật cookie mới
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

            // Lấy URL chuyển hướng cụ thể theo ngôn ngữ được chọn nếu có
            var targetRedirectUrl = $el.attr('data-redirect');
            var redirectCateUrl = (targetRedirectUrl && targetRedirectUrl.trim() !== '')
                ? targetRedirectUrl
                : ($el.closest('.languages-box').attr('data-redirect-cate') || '');

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
                        // 1. Nếu có URL chuyển hướng hợp lệ (term đối ứng hoặc cate): chuyển hướng ngay
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
