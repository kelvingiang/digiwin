<?php
declare(strict_types=1);

// [2026-10-05] - @author: Kelvin - Service: Tích hợp Google Sheets API đồng bộ dữ liệu download và đăng ký

if (!defined('ABSPATH')) {
    exit;
}

class Service_Google_Sheets
{
    private static string $spreadsheetId = '11XOFnz7wWw1L3GKLKNtmrnwQu5uY-YusMKXu9L6SFkI';

    /**
     * [2026-10-05] - @author: Kelvin - Đồng bộ mảng dữ liệu vào Google Sheet chỉ định
     */
    public static function sync(array $data): bool
    {
        $autoload_path = WP_CONTENT_DIR . '/themes/dgw/vendor/autoload.php';
        if (file_exists($autoload_path)) {
            require_once $autoload_path;
        }

        $path_to_json = WP_CONTENT_DIR . '/google-credentials.json';
        if (!file_exists($path_to_json)) {
            error_log('Google Sheets Sync: File credentials không tồn tại tại ' . $path_to_json);
            return false;
        }

        $sheet_name = !empty($data['sheet']) ? (string) $data['sheet'] : 'acList';

        if (isset($data['values']) && is_array($data['values'])) {
            $values = [$data['values']];
            $range  = $sheet_name . '!A:Z';
        } else {
            $values = [
                [
                    $data['title'] ?? '',
                    $data['file'] ?? '',
                    $data['name'] ?? '',
                    $data['email'] ?? '',
                    $data['date'] ?? date('d-M-y H:i:s'),
                ]
            ];
            $range = $sheet_name . '!A:E';
        }

        try {
            $client = new \Google\Client();
            $client->setAuthConfig($path_to_json);
            $client->addScope(\Google\Service\Sheets::SPREADSHEETS);

            $service = new \Google\Service\Sheets($client);
            $body    = new \Google\Service\Sheets\ValueRange(['values' => $values]);
            $params  = ['valueInputOption' => 'USER_ENTERED'];

            $service->spreadsheets_values->append(self::$spreadsheetId, $range, $body, $params);
            return true;
        } catch (\Throwable $e) {
            error_log('Google Sheets Sync Error: ' . $e->getMessage());
            return false;
        }
    }
}

/**
 * [2026-10-05] - @author: Kelvin - Wrapper function giữ nguyên tương thích ngược cho toàn bộ mã nguồn
 */
if (!function_exists('sync_to_google_sheets')) {
    function sync_to_google_sheets(array $data): bool
    {
        return Service_Google_Sheets::sync($data);
    }
}
