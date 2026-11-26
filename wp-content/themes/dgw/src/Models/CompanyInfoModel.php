<?php
declare(strict_types=1);

namespace DGW\Models;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [25/11/2026] - Model quản lý tập trung thông tin công ty (_info_*) với L1 memory cache & Transient Cache 12 giờ
 */
class CompanyInfoModel
{
    private const POST_ID       = 1;
    private const TRANSIENT_KEY = 'dgw_company_info_all';
    private const CACHE_TTL     = 12 * HOUR_IN_SECONDS;

    /**
     * Cache tĩnh trong một request để tránh gọi get_transient() lặp lại
     *
     * @var array<string, string>|null
     */
    private static ?array $memoryCache = null;

    /**
     * [25/11/2026] - Lấy toàn bộ metadata _info_* của công ty từ cache hoặc DB (1 query duy nhất)
     *
     * @return array<string, string>
     */
    public static function getAll(): array
    {
        if (self::$memoryCache !== null) {
            return self::$memoryCache;
        }

        $cached = get_transient(self::TRANSIENT_KEY);
        if (is_array($cached)) {
            return self::$memoryCache = $cached;
        }

        $allMeta = get_post_meta(self::POST_ID);
        $info    = [];

        if (is_array($allMeta)) {
            foreach ($allMeta as $key => $values) {
                if (str_starts_with((string) $key, '_info_') && is_array($values) && isset($values[0])) {
                    $info[(string) $key] = (string) $values[0];
                }
            }
        }

        set_transient(self::TRANSIENT_KEY, $info, self::CACHE_TTL);

        return self::$memoryCache = $info;
    }

    /**
     * [25/11/2026] - Lấy giá trị một trường thông tin công ty theo meta key
     */
    public static function get(string $metaKey, string $default = ''): string
    {
        $all = self::getAll();
        return $all[$metaKey] ?? $default;
    }

    /**
     * [25/11/2026] - Lấy giá trị trường thông tin công ty theo ngôn ngữ (vd: _info_summary -> _info_summary_vn)
     */
    public static function getLocalized(string $baseKey, ?string $lang = null, string $default = ''): string
    {
        $resolvedLang = ($lang !== null && $lang !== '')
            ? $lang
            : (function_exists('dgw_get_lang') ? (string) dgw_get_lang() : 'vn');

        $all = self::getAll();
        $key = rtrim($baseKey, '_') . '_' . $resolvedLang;

        return $all[$key] ?? $default;
    }

    /**
     * [25/11/2026] - Cập nhật hàng loạt metadata thông tin công ty và xóa Transient cache ngay lập tức
     *
     * @param array<string, string> $fields
     */
    public static function updateMany(array $fields): void
    {
        foreach ($fields as $metaKey => $value) {
            if (str_starts_with((string) $metaKey, '_info_')) {
                update_post_meta(self::POST_ID, (string) $metaKey, (string) $value);
            }
        }

        self::clearCache();
    }

    /**
     * [25/11/2026] - Xóa cả L1 memory cache và Transient cache của thông tin công ty
     */
    public static function clearCache(): void
    {
        self::$memoryCache = null;
        delete_transient(self::TRANSIENT_KEY);
    }
}
