<?php
declare(strict_types=1);

namespace DGW\Helpers;

use DGW\Models\ProductModel;
use WP_Term;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Quản lý Technical SEO: document title, meta description, canonical, hreflang, Open Graph, Twitter Card và noindex member
 * [2026-11-20] - @author: Kelvin - Hợp nhất toàn bộ hệ thống Technical SEO đa ngôn ngữ (vi-VN, zh-TW, zh-CN), Open Graph & RSS Feed từ nhánh language theo chuẩn PSR-4
 */
class SeoHelper
{
    /**
     * [18/11/2026] - Đăng ký hook SEO vào WordPress
     */
    public static function register(): void
    {
        add_filter('document_title_parts', [self::class, 'filterDocumentTitleParts']);
        add_action('wp_head', [self::class, 'renderMetaTags'], 2);
        add_action('wp_head', [self::class, 'renderNoindexMemberPage']);
        add_filter('option_blogname', [self::class, 'filterOptionBlogname']);
        add_filter('feed_links_args', [self::class, 'filterFeedLinksArgs']);
        add_filter('feed_links_extra_args', [self::class, 'filterFeedLinksExtraArgs']);
        add_action('init', static function (): void {
            if (!defined('RANK_MATH_VERSION')) {
                remove_action('wp_head', 'rel_canonical');
            }
        });
    }

    /**
     * [16/11/2026] - Ánh xạ slug trang module sang Taxonomy danh mục tương ứng
     */
    public static function detectCurrentModuleTaxonomy(?string $pageSlug = null): ?string
    {
        $pageTaxMap = [
            'cases'        => 'casestudies_category',
            'casestudies'  => 'casestudies_category',
            'solution'     => 'solutions_category',
            'solutions'    => 'solutions_category',
            'resource'     => 'resources_category',
            'resources'    => 'resources_category',
            'activities'   => 'active_category',
            'active'       => 'active_category',
            'service'      => 'services_category',
            'services'     => 'services_category',
            'industry'     => 'industries_category',
            'industries'   => 'industries_category',
            'join-digiwin' => 'joinus_category',
            'joinus'       => 'joinus_category',
        ];

        if ($pageSlug === null || $pageSlug === '') {
            if (!empty($GLOBALS['wp_query'])) {
                $pageSlug = (string) get_query_var('pagename');
            }
            if (empty($pageSlug) && !empty($_SERVER['REQUEST_URI'])) {
                $requestPath = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                $homePath    = trim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');
                if ($homePath !== '' && str_starts_with($requestPath . '/', $homePath . '/')) {
                    $requestPath = trim(substr($requestPath, strlen($homePath)), '/');
                }
                if ($requestPath !== '') {
                    $pathParts = explode('/', $requestPath);
                    $pageSlug  = $pathParts[0] ?? '';
                }
            }
        }

        $normalizedSlug = sanitize_title((string) $pageSlug);
        return $pageTaxMap[$normalizedSlug] ?? null;
    }

    /**
     * [18/11/2026] - Lấy đối tượng WP_Term an toàn theo slug hoặc term_id, ưu tiên taxonomy của module đang xem
     */
    public static function getCategoryByParam($cate, ?string $preferredTaxonomy = null): ?WP_Term
    {
        if (empty($cate)) {
            return null;
        }

        $preferredTaxonomy = $preferredTaxonomy ?: self::detectCurrentModuleTaxonomy();

        if (is_numeric($cate)) {
            if (!empty($preferredTaxonomy)) {
                $term = get_term((int) $cate, $preferredTaxonomy);
                if ($term instanceof WP_Term) {
                    return $term;
                }
            }
            $term = get_term((int) $cate);
            return ($term instanceof WP_Term) ? $term : null;
        }

        $slug       = sanitize_title((string) $cate);
        $taxonomies = [
            'solutions_category',
            'casestudies_category',
            'resources_category',
            'active_category',
            'services_category',
            'industries_category',
            'joinus_category',
            'advertising_category',
            'category',
        ];

        if (!empty($preferredTaxonomy)) {
            $taxonomies = array_values(array_unique(array_merge([$preferredTaxonomy], $taxonomies)));
        }

        foreach ($taxonomies as $taxonomy) {
            $term = get_term_by('slug', $slug, $taxonomy);
            if ($term instanceof WP_Term) {
                return $term;
            }
        }

        return null;
    }

    /**
     * [16/11/2026] - Đồng bộ giá trị blogname ở Frontend theo ngôn ngữ đang chọn
     */
    public static function filterOptionBlogname($value)
    {
        if (is_admin() || !did_action('wp')) {
            return $value;
        }

        static $isResolving = false;
        if ($isResolving) {
            return $value;
        }

        $isResolving = true;
        $lang        = LanguageHelper::getLang();
        $site        = self::getSeoSiteInfo($lang);
        $isResolving = false;

        return ($site['name'] !== '') ? $site['name'] : $value;
    }

    /**
     * [16/11/2026] - Đồng bộ mẫu tiêu đề RSS Feed chính (feed_links) cho cả 3 ngôn ngữ (vn, cn, sc)
     */
    public static function filterFeedLinksArgs(array $args): array
    {
        $lang      = LanguageHelper::getLang();
        $templates = [
            'vn' => [
                'feedtitle' => '%1$s %2$s Dòng tin',
                'comstitle' => '%1$s %2$s Dòng bình luận',
            ],
            'cn' => [
                'feedtitle' => '%1$s %2$s 訂閱',
                'comstitle' => '%1$s %2$s 留言訂閱',
            ],
            'sc' => [
                'feedtitle' => '%1$s %2$s 订阅',
                'comstitle' => '%1$s %2$s 评论订阅',
            ],
        ];

        return isset($templates[$lang]) ? array_merge($args, $templates[$lang]) : $args;
    }

    /**
     * [16/11/2026] - Đồng bộ mẫu tiêu đề RSS Feed mở rộng (feed_links_extra) cho cả 3 ngôn ngữ (vn, cn, sc)
     */
    public static function filterFeedLinksExtraArgs(array $args): array
    {
        $lang      = LanguageHelper::getLang();
        $templates = [
            'vn' => [
                'singletitle'   => '%1$s %2$s %3$s Dòng bình luận',
                'cattitle'      => '%1$s %2$s %3$s Chuyên mục',
                'tagtitle'      => '%1$s %2$s %3$s Thẻ',
                'taxtitle'      => '%1$s %2$s %3$s %4$s',
                'authortitle'   => '%1$s %2$s Bài viết bởi %3$s',
                'searchtitle'   => '%1$s %2$s Kết quả tìm kiếm cho &#8220;%3$s&#8221;',
                'posttypetitle' => '%1$s %2$s %3$s',
            ],
            'cn' => [
                'singletitle'   => '%1$s %2$s %3$s 留言訂閱',
                'cattitle'      => '%1$s %2$s %3$s 分類訂閱',
                'tagtitle'      => '%1$s %2$s %3$s 標籤訂閱',
                'taxtitle'      => '%1$s %2$s %3$s %4$s 訂閱',
                'authortitle'   => '%1$s %2$s %3$s 的文章訂閱',
                'searchtitle'   => '%1$s %2$s &#8220;%3$s&#8221; 的搜尋結果訂閱',
                'posttypetitle' => '%1$s %2$s %3$s 訂閱',
            ],
            'sc' => [
                'singletitle'   => '%1$s %2$s %3$s 评论订阅',
                'cattitle'      => '%1$s %2$s %3$s 分类订阅',
                'tagtitle'      => '%1$s %2$s %3$s 标签订阅',
                'taxtitle'      => '%1$s %2$s %3$s %4$s 订阅',
                'authortitle'   => '%1$s %2$s %3$s 的文章订阅',
                'searchtitle'   => '%1$s %2$s &#8220;%3$s&#8221; 的搜索结果订阅',
                'posttypetitle' => '%1$s %2$s %3$s 订阅',
            ],
        ];

        return isset($templates[$lang]) ? array_merge($args, $templates[$lang]) : $args;
    }

    /**
     * [16/11/2026] - Lấy thông tin tên, địa chỉ, mô tả giá trị doanh nghiệp và từ khóa của website theo ngôn ngữ
     *
     * @return array{name: string, address: string, description: string, keywords: string}
     */
    public static function getSeoSiteInfo(string $lang): array
    {
        $langSuffix = in_array($lang, ['cn', 'sc'], true) ? $lang : 'vn';

        // [25/11/2026] - Đọc thông tin công ty qua CompanyInfoModel có Transient Cache
        $siteName = trim(\DGW\Models\CompanyInfoModel::getLocalized('_info_name', $langSuffix));
        if ($siteName === '' && $langSuffix === 'sc') {
            $siteName = '鼎捷软件(越南)股份公司';
        }
        if ($siteName === '') {
            $siteName = trim((string) get_option('company_name_' . $langSuffix));
        }
        if ($siteName === '') {
            $siteName = get_bloginfo('name');
        }

        $siteAddress = trim(\DGW\Models\CompanyInfoModel::getLocalized('_info_address', $langSuffix));
        if ($siteAddress === '' && $langSuffix === 'sc') {
            $siteAddress = trim(\DGW\Models\CompanyInfoModel::getLocalized('_info_address', 'cn'));
        }
        if ($siteAddress === '') {
            $siteAddress = trim((string) get_option('company_address_' . $langSuffix));
        }

        $siteDescriptions = [
            'vn' => 'Digiwin Việt Nam cung cấp giải pháp chuyển đổi số cho doanh nghiệp sản xuất: phần mềm ERP, MES, WMS và nhà máy thông minh AIoT giúp tối ưu vận hành.',
            'cn' => '鼎捷軟件(越南)提供製造業全方位數位轉型方案，涵蓋 ERP、MES、WMS 與 AIoT 智慧工廠整合服務，助力企業優化生產效率與營運管理。',
            'sc' => '鼎捷软件(越南)提供制造业全方位数字化转型方案，涵盖 ERP、MES、WMS 与 AIoT 智慧工厂整合服务，助力企业优化生产效率与运营管理。',
        ];
        $siteDescription = $siteDescriptions[$langSuffix] ?? $siteDescriptions['vn'];

        return [
            'name'        => wp_strip_all_tags($siteName),
            'address'     => wp_strip_all_tags($siteAddress),
            'description' => wp_strip_all_tags($siteDescription),
            'keywords'    => wp_strip_all_tags($siteName),
        ];
    }

    /**
     * [16/11/2026] - Trả về mô tả giá trị SEO riêng cho từng trang Page chính theo ngôn ngữ hiện hành
     */
    public static function getPageSeoDescription(string $pageSlug, int $postId, string $lang, array $site): string
    {
        $langKey        = in_array($lang, ['cn', 'sc'], true) ? $lang : 'vn';
        $normalizedSlug = sanitize_title($pageSlug);

        $aboutDesc = [
            'vn' => 'Tìm hiểu về Digiwin Việt Nam – đơn vị cung cấp phần mềm ERP II, MES, WMS và giải pháp nhà máy thông minh uy tín từ năm 2008 cho doanh nghiệp sản xuất.',
            'cn' => '認識鼎捷軟件(越南)股份公司，自 2008 年深耕越南市場，專注提供 ERP II、MES、WMS 與智慧製造數位轉型解決方案，協助企業提升競爭力。',
            'sc' => '认识鼎捷软件(越南)股份公司，自 2008 年深耕越南市场，专注提供 ERP II、MES、WMS 与智能制造数字化转型解决方案，助力企业提升竞争力。',
        ];
        $contactDesc = [
            'vn' => 'Liên hệ Digiwin Việt Nam tại TP.HCM và Hà Nội để được tư vấn chuyên sâu giải pháp ERP, MES, WMS, AIoT và lộ trình chuyển đổi số cho doanh nghiệp.',
            'cn' => '聯絡鼎捷軟件(越南)胡志明市與河內服務據點，取得 ERP、MES、WMS、AIoT 智慧工廠顧問諮詢與企業數位轉型規劃服務。',
            'sc' => '联络鼎捷软件(越南)胡志明市与河内服务据点，获取 ERP、MES、WMS、AIoT 智慧工厂顾问咨询与企业数字化转型规划服务。',
        ];
        $solutionDesc = [
            'vn' => 'Hệ sinh thái giải pháp Digiwin: TIPTOP GP, Workflow ERP, MES/SFT, WMS và AIoT giúp doanh nghiệp sản xuất tối ưu quy trình, kiểm soát chi phí và chất lượng.',
            'cn' => '鼎捷軟件(越南)完整解決方案：TIPTOP GP、Workflow ERP、MES/SFT、WMS 與 AIoT 智慧工廠，全面提升製造營運效能。',
            'sc' => '鼎捷软件(越南)完整解决方案：TIPTOP GP、Workflow ERP、MES/SFT、WMS 与 AIoT 智慧工厂，全面提升制造运营效能。',
        ];
        $industriesDesc = [
            'vn' => 'Giải pháp chuyển đổi số chuyên sâu theo từng ngành: cơ khí, điện – điện tử, nhựa cao su, gỗ nội thất và thiết bị y tế từ Digiwin Việt Nam.',
            'cn' => '針對電子、機械、塑橡膠、家具與醫療器材等產業量身打造的鼎捷智慧製造解決方案，精準克服各行業管理痛點。',
            'sc' => '针对电子、机械、塑橡胶、家具与医疗器材等产业量身打造的鼎捷智能制造解决方案，精准克服各行业管理痛点。',
        ];
        $serviceDesc = [
            'vn' => 'Dịch vụ tư vấn, triển khai ERP/MES, đào tạo vận hành và hỗ trợ kỹ thuật e-Service chuyên nghiệp, đồng hành dài lâu cùng doanh nghiệp Việt Nam.',
            'cn' => '提供專業 ERP/MES 顧問導入、系統培訓與 e-Service 線上技術支援，全方位陪伴越南企業穩健推進數位轉型。',
            'sc' => '提供专业 ERP/MES 顾问实施、系统培训与 e-Service 在线技术支持，全方位陪伴越南企业稳健推进数字化转型。',
        ];

        $pageDescriptions = [
            'about-vn'     => $aboutDesc,
            'about-cn'     => $aboutDesc,
            'about-sc'     => $aboutDesc,
            'about-zh'     => $aboutDesc,
            'contact-vn'   => $contactDesc,
            'contact-cn'   => $contactDesc,
            'contact-sc'   => $contactDesc,
            'contact-zh'   => $contactDesc,
            'cases'        => [
                'vn' => 'Khám phá các dự án chuyển đổi số thành công cùng Digiwin Việt Nam trong ngành chế tạo, điện tử, nhựa, cơ khí và nhiều lĩnh vực sản xuất tiêu biểu.',
                'cn' => '瀏覽鼎捷軟件(越南)跨產業成功案例，涵蓋製造業、電子、塑膠、金屬加工與醫療器材等領域的 ERP、MES 數位轉型實績。',
                'sc' => '浏览鼎捷软件(越南)跨行业成功案例，涵盖制造业、电子、塑胶、金属加工与医疗器材等领域的 ERP、MES 数字化转型实绩。',
            ],
            'solution'     => $solutionDesc,
            'solutions'    => $solutionDesc,
            'resource'     => [
                'vn' => 'Tổng hợp bài viết chuyên sâu, tin tức ngành sản xuất, sách trắng chuyển đổi số và tài liệu giải pháp ERP, MES miễn phí từ chuyên gia Digiwin Việt Nam.',
                'cn' => '匯集鼎捷軟件(越南)專欄文章、產業趨勢洞察、數位轉型白皮書與 ERP/MES 專業資源下載，助企業掌握智慧製造新趨勢。',
                'sc' => '汇集鼎捷软件(越南)专栏文章、行业趋势洞察、数字化转型白皮书与 ERP/MES 专业资源下载，助企业掌握智能制造新趋势。',
            ],
            'activities'   => [
                'vn' => 'Cập nhật hội thảo trực tuyến (Webinar), triển lãm công nghệ và các sự kiện chuyển đổi số mới nhất do Digiwin Việt Nam tổ chức cho doanh nghiệp.',
                'cn' => '掌握鼎捷軟件(越南)最新線上研討會、產業科技展會與企業數位轉型交流活動資訊，與專家面對面探討智慧製造實務。',
                'sc' => '掌握鼎捷软件(越南)最新在线研讨会、产业科技展会与企业数字化转型交流活动信息，与专家面对面探讨智能制造实务。',
            ],
            'industries'   => $industriesDesc,
            'industry'     => $industriesDesc,
            'service'      => $serviceDesc,
            'services'     => $serviceDesc,
            'join-digiwin' => [
                'vn' => 'Gia nhập đội ngũ Digiwin Việt Nam để phát triển sự nghiệp trong lĩnh vực tư vấn triển khai ERP, MES và giải pháp công nghệ chuyển đổi số hàng đầu.',
                'cn' => '加入鼎捷軟件(越南)專業團隊，在 ERP、MES 與智慧製造數位轉型領域發揮專長，與亞太區頂尖顧問共同成長。',
                'sc' => '加入鼎捷软件(越南)专业团队，在 ERP、MES 与智能制造数字化转型领域发挥专长，与亚太区顶尖顾问共同成长。',
            ],
            'partner'      => [
                'vn' => 'Hợp tác cùng Digiwin Việt Nam mở rộng hệ sinh thái công nghệ và giải pháp chuyển đổi số toàn diện cho cộng đồng doanh nghiệp sản xuất.',
                'cn' => '與鼎捷軟件(越南)攜手成為策略合作夥伴，共同建構製造業數位轉型生態系，為企業客戶創造更高營運價值。',
                'sc' => '与鼎捷软件(越南)携手成为战略合作伙伴，共同构建制造业数字化转型生态系，为企业客户创造更高运营价值。',
            ],
        ];

        if (isset($pageDescriptions[$normalizedSlug][$langKey])) {
            return $pageDescriptions[$normalizedSlug][$langKey];
        }

        if ($postId > 0) {
            $excerpt = self::extractCleanExcerpt($postId, 155);
            if ($excerpt !== '') {
                return $excerpt;
            }
        }

        return (string) ($site['description'] ?? '');
    }

    /**
     * [16/11/2026] - Lấy tiêu đề trang đã dịch theo locale hiện hành
     */
    public static function getTranslatedPageTitle(string $pageSlug, int $postId = 0): string
    {
        $normalizedSlug = sanitize_title($pageSlug);
        $lang           = LanguageHelper::getLang();

        if ($lang === 'sc' && !LanguageHelper::hasSimplifiedChineseLocale()) {
            if (in_array($normalizedSlug, ['about-sc', 'about-zh'], true)) {
                return '关于我们';
            }
            if (in_array($normalizedSlug, ['contact-sc', 'contact-zh'], true)) {
                return '联络我们';
            }
        }

        if ($normalizedSlug !== '') {
            $menuItems = MenuHelper::mainList();
            if (isset($menuItems[$normalizedSlug]['name'])) {
                return wp_strip_all_tags((string) $menuItems[$normalizedSlug]['name']);
            }
        }

        $extraPageTitles = [
            'about-vn'   => __('About Digiwin', 'dgw'),
            'about-cn'   => __('About Digiwin', 'dgw'),
            'about-sc'   => __('About Digiwin', 'dgw'),
            'about-zh'   => __('About Digiwin', 'dgw'),
            'contact-vn' => __('Contact Us', 'dgw'),
            'contact-cn' => __('Contact Us', 'dgw'),
            'contact-sc' => __('Contact Us', 'dgw'),
            'contact-zh' => __('Contact Us', 'dgw'),
            'industry'   => __('Industries', 'dgw'),
            'industries' => __('Industries', 'dgw'),
            'service'    => __('Service', 'dgw'),
            'services'   => __('Service', 'dgw'),
            'download'   => __('Download File digiwin', 'dgw'),
            'member'     => __('Member', 'dgw'),
            'news'       => __('Latest News', 'dgw'),
        ];

        if (isset($extraPageTitles[$normalizedSlug])) {
            return wp_strip_all_tags((string) $extraPageTitles[$normalizedSlug]);
        }

        if ($postId > 0) {
            $postTitle = trim((string) get_the_title($postId));
            if ($postTitle !== '' && $postTitle !== '...') {
                return wp_strip_all_tags($postTitle);
            }
        }

        return $normalizedSlug !== '' ? ucwords(str_replace(['-', '_'], ' ', $normalizedSlug)) : '';
    }

    /**
     * [16/11/2026] - Trích xuất đoạn mô tả sạch từ excerpt hoặc nội dung bài viết (loại bỏ shortcode Divi & HTML)
     */
    public static function extractCleanExcerpt(int $postId, int $maxLen = 155): string
    {
        $raw = '';
        if (has_excerpt($postId)) {
            $raw = (string) get_post_field('post_excerpt', $postId);
        }
        if (trim($raw) === '') {
            $raw = (string) get_post_field('post_content', $postId);
        }

        $clean = preg_replace('/\[\/?et_pb_[^\]]*\]/', ' ', $raw);
        $clean = strip_shortcodes((string) $clean);
        $clean = wp_strip_all_tags($clean, true);
        $clean = trim((string) preg_replace('/\s+/', ' ', $clean));

        if ($clean === '') {
            return '';
        }

        return wp_html_excerpt($clean, $maxLen, '...');
    }

    /**
     * [16/11/2026] - Lấy dữ liệu SEO (title, description, keywords) cho một đối tượng WP_Term theo ngôn ngữ hiện hành
     *
     * @return array{title: string, description: string, keywords: string}
     */
    public static function getTermSeoData(WP_Term $term, string $lang, array $site, bool $allowTranslate = true): array
    {
        if ($allowTranslate) {
            $translatedTerm = LanguageHelper::getTranslatedTerm($term, $lang, $term->taxonomy);
            if ($translatedTerm instanceof WP_Term) {
                $term = $translatedTerm;
            }
        }

        $termMeta = get_option('taxonomy_' . $term->term_id);
        $seoTitle = is_array($termMeta) ? trim((string) ($termMeta['txtTitleSeo'] ?? '')) : '';
        $seoDesc  = is_array($termMeta) ? trim((string) ($termMeta['strDescriptionSeo'] ?? '')) : '';
        $seoKey   = is_array($termMeta) ? trim((string) ($termMeta['seo_keywords'] ?? '')) : '';
        $termDesc = trim((string) wp_strip_all_tags((string) $term->description));

        $title = ($seoTitle !== '') ? $seoTitle : $term->name;
        if ($seoDesc !== '') {
            $description = $seoDesc;
        } elseif ($termDesc !== '') {
            $description = wp_html_excerpt($termDesc, 155, '...');
        } else {
            if ($lang === 'cn') {
                $description = sprintf('探索%s「%s」相關解決方案與成功實務資訊，協助企業提升營運效能並推動智慧製造轉型。', $site['name'], $term->name);
            } elseif ($lang === 'sc') {
                $description = sprintf('探索%s「%s」相关解决方案与成功实务信息，助力企业提升运营效能并推动智能制造转型。', $site['name'], $term->name);
            } else {
                $description = sprintf('Khám phá giải pháp và nội dung chuyên sâu về %s từ %s, đồng hành tối ưu vận hành và chuyển đổi số doanh nghiệp.', $term->name, $site['name']);
            }
        }
        $keywords = ($seoKey !== '') ? $seoKey : trim($term->name . ', ' . $site['name'], ', ');

        return [
            'title'       => $title,
            'description' => $description,
            'keywords'    => $keywords,
        ];
    }

    /**
     * [16/11/2026] - Tổng hợp ngữ cảnh SEO (title, description, keywords, site_name, site_address) theo ngôn ngữ hiện tại
     *
     * @return array{title: string, description: string, keywords: string, site_name: string, site_address: string}
     */
    public static function getSeoContext(): array
    {
        $lang = LanguageHelper::getLang();
        $site = self::getSeoSiteInfo($lang);

        $title       = '';
        $description = $site['description'];
        $keywords    = $site['keywords'];

        if (is_home() || is_front_page()) {
            $title = $site['name'];
        } elseif (is_page()) {
            $postId   = (int) get_queried_object_id();
            $pageSlug = (string) (get_query_var('pagename') ?: ($postId ? get_post_field('post_name', $postId) : ''));
            $cate     = get_query_var('cate');
            $sp       = get_query_var('sp');

            if (!empty($cate)) {
                $preferredTax = self::detectCurrentModuleTaxonomy($pageSlug);
                $cateObj      = self::getCategoryByParam($cate, $preferredTax);
                if ($cateObj instanceof WP_Term) {
                    $termSeo     = self::getTermSeoData($cateObj, $lang, $site, true);
                    $title       = $termSeo['title'];
                    $description = $termSeo['description'];
                    $keywords    = $termSeo['keywords'];
                }
            } elseif (!empty($sp)) {
                $proArr = ProductModel::getById((int) $sp);
                if (!empty($proArr) && is_array($proArr)) {
                    $title       = trim((string) ($proArr['seo_title'] ?? '')) ?: ($postId ? get_the_title($postId) : '');
                    $description = trim((string) ($proArr['seo_description'] ?? '')) ?: $site['description'];
                    $keywords    = trim((string) ($proArr['seo_key'] ?? '')) ?: $site['keywords'];
                }
            } else {
                $seoTitle      = $postId ? trim((string) get_post_meta($postId, '_seo_title', true)) : '';
                $seoDesc       = $postId ? trim((string) get_post_meta($postId, '_seo_description', true)) : '';
                $seoKey        = $postId ? trim((string) get_post_meta($postId, '_seo_key', true)) : '';
                $fallbackTitle = self::getTranslatedPageTitle($pageSlug, $postId);

                $title       = ($seoTitle !== '') ? $seoTitle : $fallbackTitle;
                $description = ($seoDesc !== '') ? $seoDesc : self::getPageSeoDescription($pageSlug, $postId, $lang, $site);
                $keywords    = ($seoKey !== '') ? $seoKey : trim($fallbackTitle . ', ' . $site['name'], ', ');
            }
        } elseif (is_single()) {
            $postId    = (int) get_queried_object_id();
            $seoTitle  = $postId ? trim((string) get_post_meta($postId, '_seo_title', true)) : '';
            $seoDesc   = $postId ? trim((string) get_post_meta($postId, '_seo_description', true)) : '';
            $seoKey    = $postId ? trim((string) get_post_meta($postId, '_seo_key', true)) : '';
            $postTitle = $postId ? trim((string) get_the_title($postId)) : '';
            $excerpt   = $postId ? self::extractCleanExcerpt($postId) : '';

            $title       = ($seoTitle !== '') ? $seoTitle : $postTitle;
            $description = ($seoDesc !== '') ? $seoDesc : ($excerpt !== '' ? $excerpt : $site['description']);
            $keywords    = ($seoKey !== '') ? $seoKey : trim($postTitle . ', ' . $site['name'], ', ');
        } elseif (is_tax() || is_tag() || is_category()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $termSeo     = self::getTermSeoData($term, $lang, $site, !is_tag());
                $title       = $termSeo['title'];
                $description = $termSeo['description'];
                $keywords    = $termSeo['keywords'];
            }
        }

        return [
            'title'        => wp_strip_all_tags($title),
            'description'  => wp_strip_all_tags($description),
            'keywords'     => wp_strip_all_tags($keywords),
            'site_name'    => $site['name'],
            'site_address' => $site['address'],
        ];
    }

    /**
     * [16/11/2026] - Tự động cập nhật <title> theo ngôn ngữ đang chọn qua filter document_title_parts
     */
    public static function filterDocumentTitleParts($titleParts)
    {
        $seo = self::getSeoContext();

        if (is_home() || is_front_page()) {
            $titleParts['title'] = $seo['site_name'];
            unset($titleParts['tagline'], $titleParts['site']);
            return $titleParts;
        }

        if ($seo['title'] !== '') {
            $titleParts['title'] = $seo['title'];
        }

        if ($seo['site_name'] !== '' && $seo['title'] !== $seo['site_name']) {
            $titleParts['site'] = $seo['site_name'];
        } else {
            unset($titleParts['site']);
        }

        return $titleParts;
    }

    /**
     * [16/11/2026] - Tạo URL chuẩn phân cấp cho trang danh mục dạng /{pagename}/cate/{cate_slug}/[tag/{tag_slug}/]
     */
    public static function buildCatePageUrl(string $pagename, string $cateSlug, string $tagSlug = ''): string
    {
        $path = trim($pagename, '/') . '/cate/' . $cateSlug;
        if ($tagSlug !== '') {
            $path .= '/tag/' . $tagSlug;
        }

        return home_url(user_trailingslashit($path));
    }

    /**
     * [16/11/2026] - Tạo bộ canonical và hreflang cho các trang dùng chung URL giữa các ngôn ngữ
     *
     * @return array{canonical: string, hreflang: array<string, string>}
     */
    public static function buildSharedLangSeoUrls(string $baseUrl, string $urlLang): array
    {
        if ($baseUrl === '') {
            return ['canonical' => '', 'hreflang' => []];
        }

        $cnUrl = add_query_arg('lang', 'cn', $baseUrl);
        $scUrl = add_query_arg('lang', 'sc', $baseUrl);

        $canonical = $baseUrl;
        if ($urlLang === 'cn') {
            $canonical = $cnUrl;
        } elseif ($urlLang === 'sc') {
            $canonical = $scUrl;
        }

        $hreflang = [
            'vi-VN' => $baseUrl,
            'zh-TW' => $cnUrl,
        ];

        if (LanguageHelper::hasSimplifiedChineseLocale() || $urlLang === 'sc') {
            $hreflang['zh-CN'] = $scUrl;
        }

        $hreflang['x-default'] = $baseUrl;

        return [
            'canonical' => $canonical,
            'hreflang'  => $hreflang,
        ];
    }

    /**
     * [16/11/2026] - Tìm danh sách URL hreflang (vi-VN, zh-TW, zh-CN, x-default) cho một đối tượng WP_Term dựa trên Translation Group
     *
     * @return array<string, string>
     */
    public static function buildTermHreflangUrls(WP_Term $term, callable $urlResolver, string $fallbackUrl, string $urlLang): array
    {
        $termLang  = get_term_meta($term->term_id, 'term_lang', true);
        $localeMap = LanguageHelper::getSupportedLocalesMap();
        $hreflang  = [];

        foreach ($localeMap as $langKey => $localeInfo) {
            $translated = LanguageHelper::getTranslatedTerm($term, $langKey, $term->taxonomy);
            if (!($translated instanceof WP_Term) && $termLang === $langKey) {
                $translated = $term;
            }
            if ($translated instanceof WP_Term) {
                $resolved = (string) $urlResolver($translated);
                if ($resolved !== '') {
                    $hreflang[$localeInfo['hreflang']] = $resolved;
                }
            }
        }

        if (empty($hreflang)) {
            $shared = self::buildSharedLangSeoUrls($fallbackUrl, $urlLang);
            return $shared['hreflang'];
        }

        $hreflang['x-default'] = $hreflang['vi-VN'] ?? reset($hreflang);
        return $hreflang;
    }

    /**
     * [16/11/2026] - Tính toán thẻ canonical và tập hợp hreflang (vi-VN, zh-TW, zh-CN, x-default) cho trang hiện tại
     *
     * @return array{canonical: string, hreflang: array<string, string>}
     */
    public static function getSeoUrls(): array
    {
        if (is_404()) {
            return ['canonical' => '', 'hreflang' => []];
        }

        $urlLang = '';
        if (isset($_GET['lang'])) {
            $urlLang = LanguageHelper::normalizeLangCode(sanitize_text_field(wp_unslash((string) $_GET['lang']))) ?? 'vn';
        }

        if (is_home() || is_front_page()) {
            $baseUrl = is_paged()
                ? get_pagenum_link(max(1, (int) get_query_var('paged')))
                : home_url('/');
            return self::buildSharedLangSeoUrls((string) $baseUrl, $urlLang);
        }

        if (is_page()) {
            $postId   = (int) get_queried_object_id();
            $pagename = (string) (get_query_var('pagename') ?: ($postId ? get_post_field('post_name', $postId) : ''));
            $cate     = get_query_var('cate');
            $sp       = get_query_var('sp');
            $tagSlug  = sanitize_title((string) get_query_var('tag'));

            if (!empty($cate)) {
                $preferredTax = self::detectCurrentModuleTaxonomy($pagename);
                $cateObj      = self::getCategoryByParam($cate, $preferredTax);
                if ($cateObj instanceof WP_Term) {
                    $canonical = self::buildCatePageUrl($pagename, $cateObj->slug, $tagSlug);
                    $hreflang  = self::buildTermHreflangUrls(
                        $cateObj,
                        static fn (WP_Term $t): string => self::buildCatePageUrl($pagename, $t->slug, $tagSlug),
                        $canonical,
                        $urlLang
                    );
                    return ['canonical' => $canonical, 'hreflang' => $hreflang];
                }

                $fallbackUrl = self::buildCatePageUrl($pagename, sanitize_title((string) $cate), $tagSlug);
                return self::buildSharedLangSeoUrls($fallbackUrl, $urlLang);
            }

            if (!empty($sp)) {
                $baseUrl = home_url(user_trailingslashit(trim($pagename, '/') . '/sp/' . sanitize_title((string) $sp)));
                return self::buildSharedLangSeoUrls($baseUrl, $urlLang);
            }

            $baseUrl = (string) (wp_get_canonical_url($postId) ?: get_permalink($postId));

            if (is_page('member')) {
                return ['canonical' => $baseUrl, 'hreflang' => []];
            }

            $pairedUrls = LanguageHelper::getPairedPageUrls($pagename);
            if (!empty($pairedUrls)) {
                $localeMap = LanguageHelper::getSupportedLocalesMap();
                $hreflang  = [];

                foreach ($localeMap as $langKey => $localeInfo) {
                    if (!empty($pairedUrls[$langKey])) {
                        $hreflang[$localeInfo['hreflang']] = $pairedUrls[$langKey];
                    }
                }
                if (!empty($hreflang)) {
                    $hreflang['x-default'] = $hreflang['vi-VN'] ?? reset($hreflang);
                    return ['canonical' => $baseUrl, 'hreflang' => $hreflang];
                }
            }

            return self::buildSharedLangSeoUrls($baseUrl, $urlLang);
        }

        if (is_single()) {
            $postId    = (int) get_queried_object_id();
            $canonical = (string) (wp_get_canonical_url($postId) ?: get_permalink($postId));
            if ($canonical === '') {
                return ['canonical' => '', 'hreflang' => []];
            }

            $postLang     = (string) get_post_meta($postId, '_metabox_langguage', true);
            $localeMap    = LanguageHelper::getSupportedLocalesMap();
            $hreflangCode = $localeMap[$postLang]['hreflang'] ?? 'vi-VN';

            return [
                'canonical' => $canonical,
                'hreflang'  => [
                    $hreflangCode => $canonical,
                    'x-default'   => $canonical,
                ],
            ];
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $termLink  = get_term_link($term);
                $canonical = (!is_wp_error($termLink) && !empty($termLink)) ? (string) $termLink : '';
                if ($canonical === '') {
                    return ['canonical' => '', 'hreflang' => []];
                }

                if (is_tag()) {
                    return self::buildSharedLangSeoUrls($canonical, $urlLang);
                }

                $hreflang = self::buildTermHreflangUrls(
                    $term,
                    static function (WP_Term $t): string {
                        $link = get_term_link($t);
                        return (!is_wp_error($link) && !empty($link)) ? (string) $link : '';
                    },
                    $canonical,
                    $urlLang
                );
                return ['canonical' => $canonical, 'hreflang' => $hreflang];
            }
        }

        if (is_search()) {
            return ['canonical' => (string) get_search_link(), 'hreflang' => []];
        }

        if (is_archive()) {
            $paged   = max(1, (int) get_query_var('paged'));
            $baseUrl = (string) get_pagenum_link($paged);
            return self::buildSharedLangSeoUrls($baseUrl, $urlLang);
        }

        return ['canonical' => '', 'hreflang' => []];
    }

    /**
     * [16/11/2026] - Lấy URL ảnh đại diện cho Open Graph (og:image)
     */
    public static function getOgImageUrl(): string
    {
        if (is_singular()) {
            $postId = (int) get_queried_object_id();
            if ($postId > 0 && has_post_thumbnail($postId)) {
                $thumbUrl = get_the_post_thumbnail_url($postId, 'large');
                if (!empty($thumbUrl)) {
                    return (string) $thumbUrl;
                }
            }
        }

        if (defined('PART_IMAGES')) {
            return PART_IMAGES . 'logo.png';
        }

        return get_stylesheet_directory_uri() . '/images/logo.png';
    }

    /**
     * [16/11/2026] - Xuất các thẻ Open Graph và Twitter Card
     */
    public static function renderOpenGraphTags(array $seo, array $urls): void
    {
        $lang      = LanguageHelper::getLang();
        $localeMap = LanguageHelper::getSupportedLocalesMap();
        $ogLocale  = $localeMap[$lang]['og_locale'] ?? 'vi_VN';
        $ogType    = is_single() ? 'article' : 'website';
        $ogTitle   = ($seo['title'] !== '') ? $seo['title'] : $seo['site_name'];
        $ogUrl     = ($urls['canonical'] !== '') ? $urls['canonical'] : home_url('/');
        $ogImage   = self::getOgImageUrl();

        $metaProperties = [
            'og:locale'      => $ogLocale,
            'og:type'        => $ogType,
            'og:title'       => $ogTitle,
            'og:description' => $seo['description'],
            'og:url'         => $ogUrl,
            'og:site_name'   => $seo['site_name'],
            'og:image'       => $ogImage,
        ];

        foreach ($metaProperties as $property => $content) {
            if ($content === '') {
                continue;
            }
            $escapedContent = ($property === 'og:url' || $property === 'og:image')
                ? esc_url($content)
                : esc_attr($content);
            echo '<meta property="' . esc_attr($property) . '" content="' . $escapedContent . '" />' . "\n";
        }

        foreach ($localeMap as $langKey => $localeInfo) {
            if ($langKey === $lang) {
                continue;
            }
            if (!empty($urls['hreflang'][$localeInfo['hreflang']])) {
                echo '<meta property="og:locale:alternate" content="' . esc_attr($localeInfo['og_locale']) . '" />' . "\n";
            }
        }

        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    }

    /**
     * [18/11/2026] - Render thẻ meta description, canonical, hreflang và Open Graph khi không dùng Rank Math
     */
    public static function renderMetaTags(): void
    {
        if (defined('RANK_MATH_VERSION')) {
            return;
        }

        $seo  = self::getSeoContext();
        $urls = self::getSeoUrls();

        if ($seo['description'] !== '') {
            echo '<meta name="description" content="' . esc_attr($seo['description']) . '" />' . "\n";
        }
        if ($urls['canonical'] !== '') {
            echo '<link rel="canonical" href="' . esc_url($urls['canonical']) . '" />' . "\n";
        }
        foreach ($urls['hreflang'] as $langCode => $altUrl) {
            if ($altUrl !== '') {
                echo '<link rel="alternate" hreflang="' . esc_attr($langCode) . '" href="' . esc_url($altUrl) . '" />' . "\n";
            }
        }

        self::renderOpenGraphTags($seo, $urls);
    }

    /**
     * [18/11/2026] - Chặn Google index trang member riêng tư
     */
    public static function renderNoindexMemberPage(): void
    {
        if (is_page('member')) {
            echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
        }
    }
}
