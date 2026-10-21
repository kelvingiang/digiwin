<?php
/**
 * Date: 2026-08-24
 * Template Name: translate Page
 * Description: Custom template for Solutions Page
 */
/**
 * 2026-10-06 | Gia Minh
 * Script tự động map mảng data.php cũ sang file .po của Loco Translate
 */

/**
 * 2026-10-06 | Gia Minh
 * Fix đường dẫn tuyệt đối dựa trên vị trí file trong theme
 */
$theme_dir = dirname(__DIR__); // Trỏ về: wp-content/themes/dgw
$root_dir  = dirname(dirname(dirname($theme_dir))); // Trỏ về thư mục gốc: digiwin

// 1. Đường dẫn file mảng cũ và file .po đích
$old_file = $root_dir . '/wp-content/languages/zh_TW/data.php'; 
$po_file  = $theme_dir . '/languages/zh_TW.po';

// Kiểm tra chi tiết file nào bị thiếu
if (!file_exists($old_file)) {
    die("Lỗi: Không tìm thấy file data.php tại: " . $old_file);
}

if (!file_exists($po_file)) {
    die("Lỗi: Không tìm thấy file zh_TW.po tại: " . $po_file);
}

if (!file_exists($old_file) || !file_exists($po_file)) {
    die("Không tìm thấy file nguồn hoặc file .po đích!");
}

// 1. Nạp mảng dữ liệu cũ ($data)
$old_data = include $old_file;
if (!is_array($old_data)) {
    // Nếu trong file data.php là hàm hoặc biến $data, require và lấy biến
    require_once $old_file;
    if (isset($data) && is_array($data)) {
        $old_data = $data;
    } elseif (function_exists('getTranslate')) {
        $old_data = getTranslate();
    }
}

// 2. Đọc nội dung file .po hiện tại
$po_content = file_get_contents($po_file);

// 3. Quét và cập nhật bản dịch vào msgstr
$count = 0;
foreach ($old_data as $english_key => $translated_val) {
    $english_key = addcslashes($english_key, '"\\');
    $translated_val = addcslashes($translated_val, '"\\');

    // Pattern tìm msgid rỗng msgstr
    $pattern = '/(msgid\s+"' . preg_quote($english_key, '/') . '"\s*\nmsgstr\s+)"(?:.*?)"/';
    $replacement = '${1}"' . $translated_val . '"';

    $new_content = preg_replace($pattern, $replacement, $po_content);
    if ($new_content !== null && $new_content !== $po_content) {
        $po_content = $new_content;
        $count++;
    }
}

// 4. Lưu lại file .po
file_put_contents($po_file, $po_content);

echo "Đã chuyển thành công {$count} từ sang file {$po_file}!";