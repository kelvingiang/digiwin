<?php
declare(strict_types=1);

namespace DGW;

use DGW\Admin\PostColumns;
use DGW\Admin\TermMetaFields;
use DGW\Ajax\DownloadAjax;
use DGW\Ajax\LanguageAjax;
use DGW\Ajax\LoadMoreAjax;
use DGW\Ajax\MemberAuthAjax;
use DGW\Ajax\PostsAjax;
use DGW\Controllers\PostTypeRegistry;
use DGW\Core\CommentHandler;
use DGW\Core\Mailer;
use DGW\Core\MemberDownload;
use DGW\Core\RewriteRules;
use DGW\Helpers\AssetsManager;
use DGW\Helpers\EnvConfig;
use DGW\Helpers\LanguageHelper;
use DGW\Helpers\MediaUploadHelper;
use DGW\Helpers\SeoHelper;
use DGW\Helpers\ThemeTweaks;
use DGW\Metaboxes\MetaboxRegistry;
use DGW\Shortcodes\MemberShortcode;
use DGW\Taxonomies\TaxonomyRegistry;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Điểm khởi chạy trung tâm theo chuẩn PSR-4 thay thế helper/* và inc/init.php
 */
class Bootstrap
{
    /**
     * [18/11/2026] - Khởi tạo toàn bộ cấu hình, services, hooks, shortcodes, AJAX và MVC admin
     */
    public static function init(): void
    {
        EnvConfig::init();
        AssetsManager::register();
        LanguageHelper::register();
        SeoHelper::register();
        MediaUploadHelper::register();
        ThemeTweaks::register();

        // [20/11/2026] - Đồng bộ gọi qua static ::register()
        RewriteRules::register();
        Mailer::register();
        MemberDownload::register();
        CommentHandler::register();
        MemberShortcode::register();

        // [2026-11-19] - @author: Kelvin - Thay thế cơ chế glob(inc/admin/*.php) bằng các class PSR-4 trong src/Admin/
        if (is_admin() || wp_doing_ajax()) {
            PostColumns::register();
            TermMetaFields::register();
        }

        if (wp_doing_ajax()) {
            MemberAuthAjax::register();
            DownloadAjax::register();
            PostsAjax::register();
            LanguageAjax::register();
            LoadMoreAjax::register();
        }

        // [20/11/2026] - Chuyển toàn bộ bộ điều phối CPT, Admin Controller, Metabox và Taxonomy vào src/ chuẩn PSR-4
        PostTypeRegistry::register();

        if (is_admin()) {
            MetaboxRegistry::register();
        }

        TaxonomyRegistry::register();
    }
}
