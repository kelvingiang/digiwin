<?php
/**
 * [2026-10-08] - @author: Kelvin
 * Chức năng: Template hiển thị Banner Popup quảng cáo / thông báo tại trang chủ (Home Popup).
 * Mục đích tối ưu Technical SEO:
 * 1. Crawlability & Internal Link: Chuyển thẻ bọc sang thẻ <a> chuẩn HTML giúp Googlebot thu thập link và truyền PageRank.
 * 2. Giảm TTFB: Sử dụng WordPress Transient API để cache kích thước ảnh, hạn chế disk I/O từ getimagesize().
 * 3. Tối ưu LCP & Core Web Vitals: Bỏ fetchpriority="high" để nhường băng thông cho Hero Banner chính; dùng decoding="async".
 * 4. Image SEO: Tự động gán alt text động theo tiêu đề thông báo từ cơ sở dữ liệu thay vì chuỗi tĩnh.
 * 5. Accessibility (A11y): Thêm role="dialog", aria-modal="true", phím Escape và nút đóng chuẩn screen-reader.
 * 6. Bảo mật: Escape toàn bộ dữ liệu đầu ra với esc_url(), esc_attr() tránh XSS.
 */

require_once DIR_MODEL . 'model-popup-function.php';
$model = new Model_Popup_Function();
$data  = $model->getActive();

if (!empty($data)) :
    $lang = dgw_get_lang();

    // [2026-10-08] - Sanitize & gán dữ liệu hiển thị theo ngôn ngữ hiện tại
    $popup_id     = !empty($data['id_' . $lang]) ? absint($data['id_' . $lang]) : 0;
    $popup_link   = !empty($data['link_' . $lang]) ? esc_url($data['link_' . $lang]) : '#';
    $popup_target = (!empty($data['target']) && (int)$data['target'] === 1) ? '_blank' : '_self';
    $popup_title  = !empty($data['title']) ? esc_attr($data['title']) : 'Digiwin Software Announcement';

    // Đường dẫn hình ảnh cho Desktop và Mobile
    $img_name_pc = !empty($data['img_' . $lang]) ? $data['img_' . $lang] : '';
    $img_name_mb = !empty($data['img_mobile_' . $lang]) ? $data['img_mobile_' . $lang] : '';

    $img_url_pc  = $img_name_pc ? esc_url(PART_IMAGES . 'pop-up/' . $img_name_pc) : '';
    $img_url_mb  = $img_name_mb ? esc_url(PART_IMAGES . 'pop-up/' . $img_name_mb) : '';

    // [2026-10-08] - Tối ưu TTFB: Cache kích thước ảnh bằng Transient API (24 giờ) để tránh đọc ổ đĩa lặp lại
    $cache_key = 'dgw_popup_dim_' . md5($img_name_pc . $img_name_mb);
    $img_sizes = get_transient($cache_key);

    if (false === $img_sizes) {
        $img_sizes = ['pc' => null, 'mb' => null];

        // Lấy kích thước ảnh Desktop
        if ($img_name_pc) {
            $img_path_pc = $_SERVER['DOCUMENT_ROOT'] . parse_url($img_url_pc, PHP_URL_PATH);
            if (file_exists($img_path_pc)) {
                $size_pc = @getimagesize($img_path_pc);
                if ($size_pc) {
                    $img_sizes['pc'] = ['w' => $size_pc[0], 'h' => $size_pc[1]];
                }
            }
        }

        // Lấy kích thước ảnh Mobile
        if ($img_name_mb) {
            $img_path_mb = $_SERVER['DOCUMENT_ROOT'] . parse_url($img_url_mb, PHP_URL_PATH);
            if (file_exists($img_path_mb)) {
                $size_mb = @getimagesize($img_path_mb);
                if ($size_mb) {
                    $img_sizes['mb'] = ['w' => $size_mb[0], 'h' => $size_mb[1]];
                }
            }
        }

        set_transient($cache_key, $img_sizes, DAY_IN_SECONDS);
    }

    $size_pc = $img_sizes['pc'];
    $size_mb = $img_sizes['mb'];
?>
    <!-- [2026-10-08] - Khối Popup với Semantic Dialog và thuộc tính Accessibility (A11y) -->
    <div class="dgw-popup" 
         role="dialog" 
         aria-modal="true" 
         aria-label="<?php echo $popup_title; ?>">
        <div class="dgw-popup-space">
            <!-- [2026-10-08] - Nút đóng chuẩn button hỗ trợ Screen Reader và Keyboard Navigation -->
            <button type="button" 
                    class="dgw-popup-close" 
                    aria-label="<?php echo esc_attr__('Đóng thông báo', 'dgw'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
                    <path d="M320 112C434.9 112 528 205.1 528 320C528 434.9 434.9 528 320 528C205.1 528 112 434.9 112 320C112 205.1 205.1 112 320 112zM320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM231 231C221.6 240.4 221.6 255.6 231 264.9L286 319.9L231 374.9C221.6 384.3 221.6 399.5 231 408.8C240.4 418.1 255.6 418.2 264.9 408.8L319.9 353.8L374.9 408.8C384.3 418.2 399.5 418.2 408.8 408.8C418.1 399.4 418.2 384.2 408.8 374.9L353.8 319.9L408.8 264.9C418.2 255.5 418.2 240.3 408.8 231C399.4 221.7 384.2 221.6 374.9 231L319.9 286L264.9 231C255.5 221.6 240.3 221.6 231 231z" />
                </svg>
            </button>

            <!-- [2026-10-08] - Đổi div sang thẻ <a> để Googlebot crawl liên kết nội bộ (Internal Link Equity) -->
            <a class="popup-link item"
               href="<?php echo $popup_link; ?>"
               target="<?php echo esc_attr($popup_target); ?>"
               <?php if ($popup_target === '_blank') : ?>rel="noopener noreferrer"<?php endif; ?>
               data-id="<?php echo esc_attr($popup_id); ?>"
               data-link="<?php echo $popup_link; ?>"
               data-post="<?php echo esc_attr($popup_id); ?>"
               data-target="<?php echo esc_attr($data['target']); ?>">

                <picture>
                    <?php if (!empty($size_pc) && !empty($img_url_pc)): ?>
                        <!-- [2026-10-08] - Media query cho màn hình máy tính kèm kích thước chống CLS -->
                        <source media="(min-width: 768px)"
                                srcset="<?php echo $img_url_pc; ?>"
                                width="<?php echo esc_attr($size_pc['w']); ?>"
                                height="<?php echo esc_attr($size_pc['h']); ?>">
                    <?php endif; ?>

                    <!-- [2026-10-08] - Tối ưu LCP/CLS: Thẻ <picture> tự đổi source theo breakpoint, img dùng chung layout -->
                    <img decoding="async"
                         alt="<?php echo $popup_title; ?>"
                         src="<?php echo $img_url_mb ?: $img_url_pc; ?>"
                         <?php if (!empty($size_mb)): ?>
                             width="<?php echo esc_attr($size_mb['w']); ?>"
                             height="<?php echo esc_attr($size_mb['h']); ?>"
                         <?php endif; ?>>
                </picture>
            </a>
        </div>
    </div>

    <script>
        // [2026-10-08] - @author: Kelvin - Điều khiển hiển thị popup theo phiên duyệt (sessionStorage)
        if (!sessionStorage.getItem('dgwPopupShown')) {
            var popupElem = document.querySelector(".dgw-popup");
            if (popupElem) {
                popupElem.classList.add("is-visible");
                document.body.style.overflowY = "hidden";
                document.body.style.overflowX = "hidden";
            }
        }

        jQuery(document).ready(function($) {
            // [2026-10-08] - Đóng popup khi click vào nút Close hoặc overlay bên ngoài
            $(".dgw-popup-close, .dgw-popup").on("click", function(e) {
                if (e.target !== this && !$(this).hasClass('dgw-popup-close')) {
                    if ($(e.target).closest('.popup-link').length) return; 
                }
                
                $(".dgw-popup").removeClass("is-visible");
                $("body").css({
                    "overflow-y": "auto",
                    "overflow-x": "auto"
                });

                // Ghi nhớ đã đóng để không làm phiền người dùng trong phiên duyệt
                sessionStorage.setItem('dgwPopupShown', 'true');
            });

            // [2026-10-08] - Hỗ trợ phím Escape để tăng trải nghiệm UX/Accessibility
            $(document).on("keydown", function(e) {
                if (e.key === "Escape" && $(".dgw-popup").is(":visible")) {
                    $(".dgw-popup-close").trigger("click");
                }
            });

            // [2026-10-08] - Ngăn chặn click đúp khi thẻ <a> kết hợp với hàm ajax đếm view trong footer.php
            $(document).on("click", ".dgw-popup .popup-link", function(e) {
                e.preventDefault();
            });
        });
    </script>
<?php endif; ?>