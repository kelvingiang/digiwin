<?php
declare(strict_types=1);

namespace DGW\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Service tích hợp Google Sheets API đồng bộ dữ liệu download và đăng ký
 */
class GoogleSheetsService
{
    private static string $spreadsheetId = '11XOFnz7wWw1L3GKLKNtmrnwQu5uY-YusMKXu9L6SFkI';

    /**
     * [18/11/2026] - Đồng bộ mảng dữ liệu vào Google Sheet chỉ định
     */
    public static function sync(array $data): bool
    {
        $autoloadPath = WP_CONTENT_DIR . '/themes/dgw/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        $pathToJson = WP_CONTENT_DIR . '/google-credentials.json';
        if (!file_exists($pathToJson)) {
            error_log('Google Sheets Sync: File credentials không tồn tại tại ' . $pathToJson);
            return false;
        }

        $sheetName = !empty($data['sheet']) ? (string) $data['sheet'] : 'acList';

        if (isset($data['values']) && is_array($data['values'])) {
            $values = [$data['values']];
            $range  = $sheetName . '!A:Z';
        } else {
            $values = [
                [
                    $data['title'] ?? '',
                    $data['file'] ?? '',
                    $data['name'] ?? '',
                    $data['email'] ?? '',
                    $data['date'] ?? date('d-M-y H:i:s'),
                ],
            ];
            $range = $sheetName . '!A:E';
        }

        try {
            $client = new \Google\Client();
            $client->setAuthConfig($pathToJson);
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
