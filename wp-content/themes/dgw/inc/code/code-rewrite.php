<?php
declare(strict_types=1);

/**
 * ============================================================================
 * [02/10/2026] - Refactor & Performance Optimization
 * Tác giả cập nhật: Senior Web Developer
 * File: inc/code/code-rewrite.php
 * Mục đích:
 * 1. Khắc phục triệt để lỗi hiệu năng nghiêm trọng: flush_rewrite_rules() chạy
 *    trong hook 'init' gây nghẽn MySQL, tăng TTFB và ghi đè .htaccess liên tục.
 * 2. Tối ưu biểu thức chính quy (Regex) trong Rewrite Rules: dùng anchor '^', '$'
 *    và định lượng '+' thay vì '*' để tránh match chuỗi rỗng và lỗi xung đột URL.
 * 3. Chuẩn hóa PHP 8.2+: declare(strict_types=1), type hinting và return types.
 * ============================================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Rewrite_Url
{
    /**
     * [02/10/2026] - Version định danh rewrite rules.
     * Mục đích: Khi có rule mới, chỉ cần tăng số version này (vd: '1.0.2'), hệ thống
     * sẽ tự động flush rewrite rules DUY NHẤT 1 LẦN trong Admin thay vì flush mỗi request.
     */
    private const REWRITE_VERSION = '1.0.2';

    /**
     * [02/10/2026] - Khởi tạo các hook WordPress chuẩn.
     * Thay đổi: Tách biệt hook đăng ký rule và hook xử lý flush an toàn.
     */
    public function __construct()
    {
        // Đăng ký custom rules vào thời điểm 'init'
        add_action('init', [$this, 'register_rewrite_rules'], 10);

        // Đăng ký các biến query vars tùy chỉnh vào WP_Query
        add_filter('query_vars', [$this, 'register_query_vars'], 10);

        // [02/10/2026] Thay đổi: Chỉ flush rule khi theme được kích hoạt lại
        add_action('after_switch_theme', [$this, 'flush_rules_on_activation']);

        // [02/10/2026] Thay đổi: Kiểm tra version để flush rule 1 lần duy nhất trong wp-admin
        add_action('admin_init', [$this, 'maybe_flush_rewrite_rules']);
    }

    /**
     * [02/10/2026] - Đăng ký các Rewrite Rules tùy biến cho website.
     * Thay đổi:
     * - Thêm '^' ở đầu và '$' ở cuối regex để định vị chính xác đường dẫn.
     * - Đổi ([^/]*) thành ([^/]+) để bắt buộc phải có giá trị slug, loại bỏ URL rác.
     * - Bỏ hoàn toàn flush_rewrite_rules() ở cuối hàm này để cứu vớt TTFB.
     */
    public function register_rewrite_rules(): void
    {
        // Rule 1: Danh mục kết hợp thẻ bài viết (Category + Tag đầy đủ)
        // Cấu trúc URL: /{pagename}/cate/{cate_slug}/tag/{tag_slug}/
        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)/tag/([^/]+)/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]&tag=$matches[3]',
            'top'
        );

        // Rule 2: Danh mục khi tag rỗng hoặc kết thúc tại tag/ (VD: /solution/cate/tiptop/tag/ hoặc /solution/cate/tiptop/)
        add_rewrite_rule(
            '^([^/]+)/cate/([^/]+)(?:/tag)?/?$',
            'index.php?pagename=$matches[1]&cate=$matches[2]',
            'top'
        );

        // Rule 3: Chi tiết sản phẩm/bài viết tùy biến (Product / Detail)
        // Cấu trúc URL: /{pagename}/sp/{sp_slug}/
        add_rewrite_rule(
            '^([^/]+)/sp/([^/]+)/?$',
            'index.php?pagename=$matches[1]&sp=$matches[2]',
            'top'
        );
    }

    /**
     * [02/10/2026] - Thêm các tham số nhận từ URL vào danh sách cho phép của WP_Query.
     * Thay đổi: Dùng in_array với strict mode (true) để tránh đăng ký trùng lặp biến.
     *
     * @param array<int, string> $vars Danh sách query vars hiện có của WordPress.
     * @return array<int, string> Danh sách query vars sau khi bổ sung.
     */
    public function register_query_vars(array $vars): array
    {
        $custom_vars = ['sp', 'cate', 'cat', 'ts', 'active'];

        foreach ($custom_vars as $var) {
            if (!in_array($var, $vars, true)) {
                $vars[] = $var;
            }
        }

        return $vars;
    }

    /**
     * [02/10/2026] - Tự động flush rewrite rules khi đổi/kích hoạt theme.
     * Mục đích: Đảm bảo rules mới được ghi nhận ngay khi theme được đưa vào hoạt động.
     */
    public function flush_rules_on_activation(): void
    {
        $this->register_rewrite_rules();
        flush_rewrite_rules(false);
    }

    /**
     * [02/10/2026] - Cơ chế Auto-Flush an toàn thay thế cho việc gọi trong 'init'.
     * Mục đích: So sánh version hiện tại lưu trong database. Nếu phát hiện REWRITE_VERSION
     * trong code mới hơn, sẽ nạp rule và flush 1 lần duy nhất, sau đó cập nhật version vào DB.
     */
    public function maybe_flush_rewrite_rules(): void
    {
        $current_version = get_option('theme_rewrite_rules_version');

        if ($current_version !== self::REWRITE_VERSION) {
            $this->register_rewrite_rules();
            flush_rewrite_rules(false);
            update_option('theme_rewrite_rules_version', self::REWRITE_VERSION, false);
        }
    }
}

// [02/10/2026] - Khởi tạo thực thi class Rewrite_Url
new Rewrite_Url();