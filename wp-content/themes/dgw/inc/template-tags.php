<?php
declare(strict_types=1);

/**
 * [18/11/2026] - Tập hợp các hàm wrapper global và class alias tương thích ngược cho templates, controllers và views
 * Toàn bộ logic lõi nằm trong thư mục src/ (namespace DGW\) theo chuẩn PSR-4
 */

use DGW\Ajax\DownloadAjax;
use DGW\Ajax\MemberAuthAjax;
use DGW\Core\CommentHandler;
use DGW\Core\Mailer;
use DGW\Core\MediaMetaBox;
use DGW\Core\MemberDictionary;
use DGW\Core\MemberDownload;
use DGW\Core\RewriteRules;
use DGW\Core\UploadHandler;
use DGW\Helpers\AuthFormHelper;
use DGW\Helpers\EnvConfig;
use DGW\Helpers\LanguageHelper;
use DGW\Helpers\MediaUploadHelper;
use DGW\Helpers\MenuHelper;
use DGW\Helpers\PostViewHelper;
use DGW\Helpers\RequestHelper;
use DGW\Helpers\SeoHelper;
use DGW\Helpers\ThemeTweaks;
use DGW\Models\CategoryModel;
use DGW\Models\CustomPostModel;
use DGW\Models\LogoListModel;
use DGW\Models\LogoModel;
use DGW\Models\MemberDownloadListModel;
use DGW\Models\MemberDownloadModel;
use DGW\Models\PopupListModel;
use DGW\Models\PopupModel;
use DGW\Models\ProductModel;
use DGW\Services\GoogleSheetsService;
use DGW\Services\LarkService;
use DGW\Shortcodes\MemberShortcode;

if (!defined('ABSPATH')) {
    exit;
}

// ===== LEGACY CLASS ALIASES =====
if (!class_exists('Service_Google_Sheets', false)) {
    class_alias(GoogleSheetsService::class, 'Service_Google_Sheets');
}
if (!class_exists('Service_Lark', false)) {
    class_alias(LarkService::class, 'Service_Lark');
}
if (!class_exists('Rewrite_Url', false)) {
    class_alias(RewriteRules::class, 'Rewrite_Url');
}
if (!class_exists('Model_Custom_Post', false)) {
    class_alias(CustomPostModel::class, 'Model_Custom_Post');
}
if (!class_exists('Model_Download', false)) {
    class_alias(MemberDownloadListModel::class, 'Model_Download');
}
if (!class_exists('Model_Download_Function', false)) {
    class_alias(MemberDownloadModel::class, 'Model_Download_Function');
}
if (!class_exists('Model_Logo', false)) {
    class_alias(LogoListModel::class, 'Model_Logo');
}
if (!class_exists('Model_Logo_Function', false)) {
    class_alias(LogoModel::class, 'Model_Logo_Function');
}
if (!class_exists('Model_Pop_up', false)) {
    class_alias(PopupListModel::class, 'Model_Pop_up');
}
if (!class_exists('Model_Popup_Function', false)) {
    class_alias(PopupModel::class, 'Model_Popup_Function');
}

// ===== SERVICES =====
if (!function_exists('sync_to_google_sheets')) {
    function sync_to_google_sheets(array $data): bool
    {
        return GoogleSheetsService::sync($data);
    }
}

if (!function_exists('sendNotificationToLark')) {
    function sendNotificationToLark(string $message)
    {
        return LarkService::send($message);
    }
}

// ===== SHORTCODES =====
if (!function_exists('member_forgot_password_form')) {
    function member_forgot_password_form()
    {
        return MemberShortcode::forgotPasswordForm();
    }
}

if (!function_exists('member_login_register_form')) {
    function member_login_register_form()
    {
        return MemberShortcode::loginRegisterForm();
    }
}

// ===== MEMBER & DOWNLOAD HELPERS =====
if (!function_exists('dgw_generate_expiry_time')) {
    function dgw_generate_expiry_time(): string
    {
        return MemberDownload::generateExpiryTime();
    }
}

if (!function_exists('dgw_is_token_valid')) {
    function dgw_is_token_valid(string $expiry_from_db): bool
    {
        return MemberDownload::isTokenValid($expiry_from_db);
    }
}

if (!function_exists('convert_gdrive_to_download')) {
    function convert_gdrive_to_download(string $url): string
    {
        return MemberDownload::convertGdriveToDownload($url);
    }
}

if (!function_exists('dgw_convert_gdrive_url')) {
    function dgw_convert_gdrive_url(string $url): string
    {
        return DownloadAjax::convertGdriveUrl($url);
    }
}

if (!function_exists('dgw_detect_file_info')) {
    function dgw_detect_file_info(string $url, string $fallback_title = ''): array
    {
        return DownloadAjax::detectFileInfo($url, $fallback_title);
    }
}

if (!function_exists('get_member_information')) {
    function get_member_information(string $session_key)
    {
        return MemberDownload::getMemberInformation($session_key);
    }
}

if (!function_exists('get_current_custom_user')) {
    function get_current_custom_user()
    {
        return MemberDownload::getCurrentCustomUser();
    }
}

if (!function_exists('is_member_logged_in')) {
    function is_member_logged_in()
    {
        return MemberDownload::isMemberLoggedIn();
    }
}

if (!function_exists('get_model')) {
    function get_model()
    {
        return MemberAuthAjax::getModel();
    }
}

if (!function_exists('get_member_messages')) {
    function get_member_messages(string $lang = 'vi'): array
    {
        return MemberAuthAjax::getMessages($lang);
    }
}

// ===== DICTIONARIES =====
if (!function_exists('get_industry_dictionary')) {
    function get_industry_dictionary(): array
    {
        return MemberDictionary::industries();
    }
}

if (!function_exists('get_position_dictionary')) {
    function get_position_dictionary(): array
    {
        return MemberDictionary::positions();
    }
}

if (!function_exists('get_department_dictionary')) {
    function get_department_dictionary(): array
    {
        return MemberDictionary::departments();
    }
}

// ===== COMMENTS, MEDIA & UPLOADS =====
if (!function_exists('my_custom_comment')) {
    function my_custom_comment($comment, $args, $depth): void
    {
        CommentHandler::renderComment($comment, $args, $depth);
    }
}

if (!function_exists('add_wp_media')) {
    function add_wp_media($item): void
    {
        MediaMetaBox::render($item);
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($name, $File)
    {
        return UploadHandler::uploadFile($name, $File);
    }
}

if (!function_exists('uploadLogo')) {
    function uploadLogo($name, $File)
    {
        return UploadHandler::uploadLogo($name, $File);
    }
}

if (!function_exists('uploadFileDownLoad')) {
    function uploadFileDownLoad($File, $name)
    {
        return MediaUploadHelper::uploadDownloadFile($File, $name);
    }
}

if (!function_exists('custom_phpmailer_smtp_config')) {
    function custom_phpmailer_smtp_config($phpmailer): void
    {
        Mailer::configureSmtp($phpmailer);
    }
}

// ===== LANGUAGE & TRANSLATION =====
if (!function_exists('dgw_get_supported_locales_map')) {
    function dgw_get_supported_locales_map(): array
    {
        return LanguageHelper::getSupportedLocalesMap();
    }
}

if (!function_exists('dgw_normalize_lang_code')) {
    function dgw_normalize_lang_code(string $raw_lang): ?string
    {
        return LanguageHelper::normalizeLangCode($raw_lang);
    }
}

if (!function_exists('dgw_has_simplified_chinese_locale')) {
    function dgw_has_simplified_chinese_locale(): bool
    {
        return LanguageHelper::hasSimplifiedChineseLocale();
    }
}

if (!function_exists('dgw_get_paired_pages_map')) {
    function dgw_get_paired_pages_map(): array
    {
        return LanguageHelper::getPairedPagesMap();
    }
}

if (!function_exists('dgw_detect_paired_page_slug')) {
    function dgw_detect_paired_page_slug(): ?string
    {
        return LanguageHelper::detectPairedPageSlug();
    }
}

if (!function_exists('dgw_detect_paired_page_language')) {
    function dgw_detect_paired_page_language(): ?string
    {
        return LanguageHelper::detectPairedPageLanguage();
    }
}

if (!function_exists('dgw_get_paired_page_urls')) {
    function dgw_get_paired_page_urls(string $page_slug): array
    {
        return LanguageHelper::getPairedPageUrls($page_slug);
    }
}

if (!function_exists('dgw_detect_current_category_slug')) {
    function dgw_detect_current_category_slug(): ?string
    {
        return LanguageHelper::detectCurrentCategorySlug();
    }
}

if (!function_exists('dgw_detect_category_language')) {
    function dgw_detect_category_language($cate_param = null): ?string
    {
        return LanguageHelper::detectCategoryLanguage($cate_param);
    }
}

if (!function_exists('dgw_get_translated_term')) {
    function dgw_get_translated_term($term, string $target_lang, string $taxonomy = 'solutions_category'): ?WP_Term
    {
        return LanguageHelper::getTranslatedTerm($term, $target_lang, $taxonomy);
    }
}

if (!function_exists('dgw_get_lang')) {
    function dgw_get_lang()
    {
        return LanguageHelper::getLang();
    }
}

if (!function_exists('dgw_reload_theme_textdomain')) {
    function dgw_reload_theme_textdomain(string $locale): void
    {
        LanguageHelper::reloadThemeTextdomain($locale);
    }
}

// ===== REQUEST & ADMIN HELPERS =====
if (!function_exists('dgw_load_env')) {
    function dgw_load_env($filePath): void
    {
        EnvConfig::loadEnv((string) $filePath);
    }
}

if (!function_exists('cmp')) {
    function cmp($a, $b)
    {
        return RequestHelper::cmp($a, $b);
    }
}

if (!function_exists('dgw_sanitize_request_value')) {
    function dgw_sanitize_request_value($value)
    {
        return RequestHelper::sanitizeValue($value);
    }
}

if (!function_exists('getParams')) {
    function getParams($name = null, $default = '')
    {
        return RequestHelper::getParams($name, $default);
    }
}

if (!function_exists('custom_redirect')) {
    function custom_redirect($location = '')
    {
        return RequestHelper::customRedirect($location);
    }
}

if (!function_exists('isPost')) {
    function isPost()
    {
        return RequestHelper::isPost();
    }
}

if (!function_exists('toBack')) {
    function toBack($num): void
    {
        RequestHelper::toBack($num);
    }
}

// ===== SEO & THEME TWEAKS =====
if (!function_exists('dgw_detect_current_module_taxonomy')) {
    function dgw_detect_current_module_taxonomy(?string $page_slug = null): ?string
    {
        return SeoHelper::detectCurrentModuleTaxonomy($page_slug);
    }
}

if (!function_exists('dgw_get_category_by_param')) {
    function dgw_get_category_by_param($cate, ?string $preferred_taxonomy = null)
    {
        return SeoHelper::getCategoryByParam($cate, $preferred_taxonomy);
    }
}

if (!function_exists('dgw_get_seo_site_info')) {
    function dgw_get_seo_site_info(string $lang): array
    {
        return SeoHelper::getSeoSiteInfo($lang);
    }
}

if (!function_exists('dgw_get_seo_context')) {
    function dgw_get_seo_context(): array
    {
        return SeoHelper::getSeoContext();
    }
}

if (!function_exists('dgw_get_seo_urls')) {
    function dgw_get_seo_urls(): array
    {
        return SeoHelper::getSeoUrls();
    }
}

if (!function_exists('dgw_seo_document_title_parts')) {
    function dgw_seo_document_title_parts($title_parts)
    {
        return SeoHelper::filterDocumentTitleParts($title_parts);
    }
}

if (!function_exists('dgw_seo_meta_tags')) {
    function dgw_seo_meta_tags(): void
    {
        SeoHelper::renderMetaTags();
    }
}

if (!function_exists('custom_noindex_member_page')) {
    function custom_noindex_member_page(): void
    {
        SeoHelper::renderNoindexMemberPage();
    }
}

if (!function_exists('custom_login_logo')) {
    function custom_login_logo(): void
    {
        ThemeTweaks::renderLoginLogo();
    }
}

if (!function_exists('remove_wp_block_library_css')) {
    function remove_wp_block_library_css(): void
    {
        ThemeTweaks::removeBlockLibraryCss();
    }
}

if (!function_exists('digiwin_optimize_image_quality')) {
    function digiwin_optimize_image_quality(int $quality, string $mime_type): int
    {
        return MediaUploadHelper::optimizeImageQuality($quality, $mime_type);
    }
}

// ===== CATEGORIES, MENU & OPTION LISTS (2026-11-19) =====
if (!function_exists('getCategories')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho CategoryModel::getCategories()
    function getCategories(string $cate, ?string $lang = null): array
    {
        return CategoryModel::getCategories($cate, $lang);
    }
}

if (!function_exists('getAllCategories')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho CategoryModel::getAllCategories()
    function getAllCategories(string $cate, $parent, string $page, ?string $lang = null): array
    {
        return CategoryModel::getAllCategories($cate, (int) $parent, $page, $lang);
    }
}

if (!function_exists('get_product')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho ProductModel::getById()
    function get_product($id)
    {
        return ProductModel::getById((int) $id);
    }
}

if (!function_exists('dgw_get_auth_fields_config')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho AuthFormHelper::getFieldsConfig()
    function dgw_get_auth_fields_config(): array
    {
        return AuthFormHelper::getFieldsConfig();
    }
}

if (!function_exists('dgw_render_auth_form_full')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho AuthFormHelper::renderFull()
    function dgw_render_auth_form_full(string $prefix = 'reg-', string $button_text = '', $data = null): void
    {
        AuthFormHelper::renderFull($prefix, $button_text, $data);
    }
}

if (!function_exists('menu_home_list')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho MenuHelper::homeList()
    function menu_home_list(): array
    {
        return MenuHelper::homeList();
    }
}

if (!function_exists('menu_main_list')) {
    // [2026-11-19] - @author: Kelvin - Wrapper tương thích ngược cho MenuHelper::mainList()
    function menu_main_list(): array
    {
        return MenuHelper::mainList();
    }
}

if (!function_exists('member_position_list')) {
    function member_position_list(): array
    {
        return MenuHelper::memberPositions();
    }
}

if (!function_exists('education_list')) {
    function education_list(): array
    {
        return MenuHelper::educations();
    }
}

if (!function_exists('experience_list')) {
    function experience_list(): array
    {
        return MenuHelper::experiences();
    }
}

if (!function_exists('activities_list')) {
    function activities_list(): array
    {
        return MenuHelper::activities();
    }
}

if (!function_exists('department_list')) {
    function department_list(): array
    {
        return MenuHelper::departments();
    }
}

if (!function_exists('resources_list')) {
    function resources_list(): array
    {
        return MenuHelper::resources();
    }
}

if (!function_exists('services_list')) {
    function services_list(): array
    {
        return MenuHelper::services();
    }
}

if (!function_exists('working_form_list')) {
    function working_form_list(): array
    {
        return MenuHelper::workingForms();
    }
}

if (!function_exists('industry_sector_list')) {
    function industry_sector_list(): array
    {
        return MenuHelper::industrySectors();
    }
}

if (!function_exists('get_industry_sector')) {
    function get_industry_sector($key): string
    {
        return MenuHelper::getIndustrySector((string) $key);
    }
}

if (!function_exists('get_member_position')) {
    function get_member_position($key): string
    {
        return MenuHelper::getMemberPosition((string) $key);
    }
}

if (!function_exists('get_department_name')) {
    function get_department_name($key): string
    {
        return MenuHelper::getDepartmentName((string) $key);
    }
}

// ===== CUSTOM POST & NAVIGATION VIEW HELPERS (2026-11-19) =====
if (!function_exists('renderCustomPostList')) {
    function renderCustomPostList(WP_Query $custom_query): void
    {
        PostViewHelper::renderList($custom_query);
    }
}

if (!function_exists('getCustomsPost')) {
    function getCustomsPost(string $postType, $postCount): void
    {
        PostViewHelper::renderPosts($postType, (int) $postCount);
    }
}

if (!function_exists('getCustomsPostByCate')) {
    function getCustomsPostByCate(string $postType, $cate, $postCount, string $taxonomy): void
    {
        PostViewHelper::renderPostsByCate($postType, $cate, (int) $postCount, $taxonomy);
    }
}

if (!function_exists('getCustomsPostCate')) {
    function getCustomsPostCate(array $param): void
    {
        PostViewHelper::renderPostCateNav($param);
    }
}

if (!function_exists('getCustomPostAtHome')) {
    function getCustomPostAtHome(string $postType, $postCount): WP_Query
    {
        return PostViewHelper::getAtHome($postType, (int) $postCount);
    }
}

if (!function_exists('getCustomPostCateAtHome')) {
    function getCustomPostCateAtHome(string $postType, string $cateSlug, $postCount): WP_Query
    {
        return PostViewHelper::getCateAtHome($postType, $cateSlug, (int) $postCount);
    }
}

if (!function_exists('getCustomPostAtSideCate')) {
    function getCustomPostAtSideCate(string $postType, $postCount, string $taxonomy, $cate): WP_Query
    {
        return PostViewHelper::getAtSideCate($postType, (int) $postCount, $taxonomy, $cate);
    }
}

if (!function_exists('getCustomPostAtSide')) {
    function getCustomPostAtSide(string $postType, $postCount): WP_Query
    {
        return PostViewHelper::getAtSide($postType, (int) $postCount);
    }
}

if (!function_exists('getCustomPostShowSidebar')) {
    function getCustomPostShowSidebar(string $postType): WP_Query
    {
        return PostViewHelper::getShowSidebar($postType);
    }
}

if (!function_exists('getPostCategory')) {
    function getPostCategory(string $cate, $postCount): WP_Query
    {
        return PostViewHelper::getPostCategory($cate, (int) $postCount);
    }
}

if (!function_exists('getPostCategoryAtHome')) {
    function getPostCategoryAtHome(string $cate, $postCount): WP_Query
    {
        return PostViewHelper::getPostCategoryAtHome($cate, (int) $postCount);
    }
}

if (!function_exists('pageImg')) {
    function pageImg($id): void
    {
        PostViewHelper::renderPageImage((int) $id);
    }
}

if (!function_exists('menuSub')) {
    function menuSub(string $cate, string $page): void
    {
        PostViewHelper::renderMenuSub($cate, $page);
    }
}

if (!function_exists('menuSide')) {
    function menuSide(string $cate, string $page): void
    {
        PostViewHelper::renderMenuSide($cate, $page);
    }
}
