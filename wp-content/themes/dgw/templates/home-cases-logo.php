<?php
/**
 * [2026-10-08] - @author: Kelvin
 * Chức năng: Template hiển thị danh sách Logo Đối tác / Khách hàng tiêu biểu tại trang chủ (Home Cases Logo).
 * Mục đích tối ưu Technical SEO & Performance:
 * 1. Cache-First Strategy (Giảm TTFB): Cache toàn bộ danh sách logo kèm kích thước (width/height) bằng Transient API (12 giờ) để triệt tiêu Disk I/O (getimagesize) và SQL query lặp lại.
 * 2. Tối ưu Core Web Vitals (LCP): Xóa bỏ fetchpriority="high" ở tất cả các logo, thay bằng loading="lazy" và decoding="async" để không tranh chấp băng thông với Hero Slider chính.
 * 3. Image SEO & Entity Recognition: Tự động dùng tên công ty ($value['company']) làm thuộc tính alt và title thay cho chuỗi tĩnh generic ("digiwin partner").
 * 4. Technical Link SEO & Security: Escape URL đầy đủ với esc_url(), bổ sung target="_blank" và rel="noopener noreferrer" cho liên kết ngoài.
 * 5. Layout Stability (CLS): Cung cấp đầy đủ width và height giúp trình duyệt dự trù không gian khung hình, ngăn hiện tượng nhảy bố cục.
 */

// [20/11/2026] - Chuẩn MVC: View chỉ nhận $logo_list từ PostViewHelper/LogoModel, không khởi tạo Model trực tiếp
$logo_list = isset($args['logo_list']) && is_array($args['logo_list'])
    ? $args['logo_list']
    : \DGW\Models\LogoModel::getCachedHomeLogos();

if (!empty($logo_list)) :
?>
    <!-- [2026-10-08] - Khối lưới hiển thị Logo Đối tác chuẩn SEO Semantic -->
    <div class="case-logo">
        <?php foreach ($logo_list as $logo) : 
            $has_link      = !empty($logo['link']) && $logo['link'] !== '#';
            $company_title = esc_attr($logo['company']);
            $tag           = $has_link ? 'a' : 'div';
            $href_attr     = $has_link ? 'href="' . esc_url($logo['link']) . '" target="_blank" rel="noopener noreferrer"' : '';
            $aria_attr     = $has_link ? 'aria-label="' . $company_title . '"' : '';
        ?>
            <div class="case-logo-item">
                <!-- [2026-10-08] - Thẻ bọc logo đối tác, hỗ trợ rel="noopener noreferrer" chống rò rỉ referrer -->
                <<?php echo $tag; ?> class="case-logo-card" <?php echo $href_attr; ?> <?php echo $aria_attr; ?> title="<?php echo $company_title; ?>">
                    <!-- [2026-10-08] - Tối ưu Core Web Vitals: loading="lazy", decoding="async", alt động theo tên công ty -->
                    <img alt="<?php echo $company_title; ?>"
                         src="<?php echo esc_url($logo['img_url']); ?>"
                         loading="lazy"
                         decoding="async"
                         width="<?php echo esc_attr((string) $logo['width']); ?>"
                         height="<?php echo esc_attr((string) $logo['height']); ?>" />
                </<?php echo $tag; ?>>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>