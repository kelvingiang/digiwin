<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Xử lý upload file download và cấu hình kích thước/chất lượng ảnh
 */
class MediaUploadHelper
{
    /**
     * [18/11/2026] - Đăng ký image sizes và tối ưu chất lượng nén ảnh
     * [19/11/2026] - @author: Kelvin - Gộp hook upload form enctype và lọc image sizes mặc định từ inc/admin/admin-add-filter.php
     */
    public static function register(): void
    {
        add_action('after_setup_theme', [self::class, 'registerImageSizes']);
        add_filter('wp_editor_set_quality', [self::class, 'optimizeImageQuality'], 10, 2);

        if (is_admin()) {
            add_action('post_edit_form_tag', [self::class, 'enableMultipartPostForm']);
            add_filter('intermediate_image_sizes_advanced', [self::class, 'removeDefaultImageSizes']);
        }
    }

    /**
     * [2026-11-19] - @author: Kelvin - Cho phép upload file trong form chỉnh sửa bài viết (metabox)
     */
    public static function enableMultipartPostForm(): void
    {
        echo ' enctype="multipart/form-data"';
    }

    /**
     * [2026-11-19] - @author: Kelvin - Tắt sinh các kích thước ảnh mặc định không dùng của WordPress
     */
    public static function removeDefaultImageSizes(array $sizes): array
    {
        unset(
            $sizes['large'],
            $sizes['thumbnail'],
            $sizes['medium'],
            $sizes['medium_large'],
            $sizes['1536x1536'],
            $sizes['2048x2048']
        );

        return $sizes;
    }

    /**
     * [18/11/2026] - Khai báo các kích thước thumbnail chuẩn cho desktop/mobile
     */
    public static function registerImageSizes(): void
    {
        add_image_size('casestudies-desktop', 410, 307, true);
        add_image_size('casestudies-mobile', 150, 113, true);
        add_image_size('slider-desktop', 1920, 800, true);
        add_image_size('slider-mobile', 768, 500, true);
        add_image_size('card-desktop', 300, 200, true);
        add_image_size('card-mobile', 480, 320, true);
    }

    /**
     * [18/11/2026] - Tối ưu chất lượng nén WebP/JPEG xuống 78%
     */
    public static function optimizeImageQuality(int $quality, string $mimeType): int
    {
        if (in_array($mimeType, ['image/webp', 'image/jpeg'], true)) {
            return 78;
        }
        return $quality;
    }

    /**
     * [18/11/2026] - Xử lý upload file tài liệu vào thư mục DIR_FILE
     */
    public static function uploadDownloadFile($file, $name): string
    {
        if (empty($file['file_upload']['name'])) {
            return '';
        }

        if (!empty($file['file_upload']['error']) && $file['file_upload']['error'] !== UPLOAD_ERR_OK) {
            return 'Upload error: ' . (int) $file['file_upload']['error'];
        }

        $fileName = sanitize_file_name($file['file_upload']['name']);
        $fileSize = (int) $file['file_upload']['size'];
        $fileTmp  = $file['file_upload']['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif'];
        if (!in_array($fileExt, $allowedExt, true)) {
            return '不支援的檔案格式';
        }

        if ($fileSize > 10097152) {
            return '上傳檔案容量不可大於 10 MB';
        }

        $uploadDir = untrailingslashit(DIR_FILE) . DS;
        if (!file_exists($uploadDir) && !wp_mkdir_p($uploadDir)) {
            return '無法建立上傳資料夾';
        }

        if (!empty($name) && is_file($uploadDir . $name)) {
            unlink($uploadDir . $name);
        }

        $destinationName = wp_unique_filename($uploadDir, $fileName);
        $destination     = $uploadDir . $destinationName;

        if (move_uploaded_file($fileTmp, $destination)) {
            return $destinationName;
        }

        return 'File upload failed.';
    }
}
