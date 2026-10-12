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

// [2026-10-08] - Lấy dữ liệu logo qua Transient Cache để tối ưu thời gian phản hồi máy chủ (TTFB)
$transient_key = 'dgw_home_cases_logos';
$logo_list     = get_transient($transient_key);

if (false === $logo_list) {
    require_once DIR_MODEL . 'model-logo-function.php';
    $model     = new Model_Logo_Function();
    $raw_logos = $model->getAll(0);
    $logo_list = [];

    if (!empty($raw_logos) && is_array($raw_logos)) {
        foreach ($raw_logos as $item) {
            if (empty($item['img'])) {
                continue;
            }

            $img_filename = $item['img'];
            $image_path   = DIR_IMAGES . 'logo/' . $img_filename;
            $img_url      = PART_IMAGES . 'logo/' . $img_filename;

            // Kích thước mặc định
            $width  = 225;
            $height = 225;

            // Đọc kích thước file một lần duy nhất lúc tạo cache
            if (file_exists($image_path)) {
                $image_info = @getimagesize($image_path);
                if (!empty($image_info)) {
                    $width  = (int) $image_info[0];
                    $height = (int) $image_info[1];
                }
            }

            $logo_list[] = [
                'company' => !empty($item['company']) ? trim($item['company']) : 'Partner Client',
                'img_url' => $img_url,
                'link'    => !empty($item['link']) ? trim($item['link']) : '',
                'width'   => $width,
                'height'  => $height,
            ];
        }
    }

    // Cache kết quả trong 12 giờ
    set_transient($transient_key, $logo_list, 12 * HOUR_IN_SECONDS);
}

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