<?php
declare(strict_types=1);

namespace DGW\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Xử lý upload ảnh thông thường và ảnh logo (chuyển từ inc/code/code-upload-file.php)
 */
class UploadHandler
{
    /**
     * [18/11/2026] - Upload file ảnh vào thư mục DIR_IMAGES
     */
    public static function uploadFile($name, $file)
    {
        if (!empty($file['name'])) {
            $errors    = [];
            $fileSize  = $file['size'];
            $fileTmp   = $file['tmp_name'];
            $fileTrim  = explode('.', $file['name']);
            $trimType  = strtolower($fileTrim[1] ?? '');
            $cusName   = $name . '.' . $trimType;
            $allowed   = ['jpeg', 'jpg', 'png', 'bmp', 'webp', 'avif'];

            if (!in_array($trimType, $allowed, true)) {
                $errors[] = '上傳照片檔案是 JPEG, PNG, BMP.';
            }
            if ($fileSize > 2097152) {
                $errors[] = '上傳檔案容量不可大於 2 MB';
            }

            $path = DIR_IMAGES;
            if (empty($errors)) {
                if (is_file($path . $name)) {
                    unlink($path . $name);
                }
                move_uploaded_file($fileTmp, $path . $cusName);
                return $cusName;
            }

            return $errors;
        }

        return null;
    }

    /**
     * [18/11/2026] - Upload file ảnh logo vào thư mục DIR_IMAGES/logo
     */
    public static function uploadLogo($name, $file)
    {
        if (!empty($file['file-logo']['name'])) {
            $errors   = [];
            $fileSize = $file['file-logo']['size'];
            $fileTmp  = $file['file-logo']['tmp_name'];
            $fileTrim = explode('.', $file['file-logo']['name']);
            $trimType = strtolower($fileTrim[1] ?? '');
            $cusName  = $name . '.' . $trimType;
            $allowed  = ['jpeg', 'jpg', 'png', 'bmp', 'webp', 'avif'];

            if (!in_array($trimType, $allowed, true)) {
                $errors[] = '上傳照片檔案是 JPEG, PNG, BMP, WEBP, AVIF';
            }
            if ($fileSize > 2097152) {
                $errors[] = '上傳檔案容量不可大於 2 MB';
            }

            $path = DIR_IMAGES . 'logo' . DS;
            if (empty($errors)) {
                if (is_file($path . $name)) {
                    unlink($path . $name);
                }
                move_uploaded_file($fileTmp, $path . $cusName);
                return $cusName;
            }

            return $errors;
        }

        return null;
    }

    /**
     * [2026-11-20] - @author: Kelvin - Upload ảnh Pop-up vào thư mục DIR_IMAGES/pop-up (thay thế hàm global uploadImg trong controller-popup.php)
     *
     * @return string|string[]|null Tên file đã lưu, mảng lỗi, hoặc null nếu không có file upload
     */
    public static function uploadPopupImage(string $name, array $file)
    {
        if (empty($file['name'])) {
            return null;
        }

        $errors   = [];
        $fileSize = (int) ($file['size'] ?? 0);
        $fileTmp  = (string) ($file['tmp_name'] ?? '');
        $fileName = (string) $file['name'];
        $fileErr  = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($fileErr !== UPLOAD_ERR_OK || $fileTmp === '' || !is_uploaded_file($fileTmp)) {
            $errors[] = '檔案上傳失敗!';
            return $errors;
        }

        $trimType  = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
        $safeName  = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $fileName);
        $safeTitle = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $name);
        $cusName   = $safeTitle . '-' . $safeName;

        $extensions = ['jpeg', 'jpg', 'png', 'bmp', 'webp', 'avif'];
        if (!in_array($trimType, $extensions, true)) {
            $errors[] = '上傳照片檔案僅限JPEG, PNG, BMP.';
        }

        if ($fileSize > 2097152) {
            $errors[] = '上傳檔案容量不可大於2MB';
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/bmp', 'image/webp', 'image/avif'];
        $detectedMime = function_exists('mime_content_type') ? (string) mime_content_type($fileTmp) : '';
        if ($detectedMime === '' || !in_array($detectedMime, $allowedTypes, true)) {
            $errors[] = '檔案類型不正確!';
        }

        $path = DIR_IMAGES . 'pop-up' . DS;
        if (!is_dir($path)) {
            wp_mkdir_p($path);
        }

        if (empty($errors)) {
            if (move_uploaded_file($fileTmp, $path . $cusName)) {
                return $cusName;
            }
            $errors[] = '檔案移動失敗';
        }

        return $errors;
    }
}
