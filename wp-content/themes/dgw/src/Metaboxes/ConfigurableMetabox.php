<?php
declare(strict_types=1);

namespace DGW\Metaboxes;

use WP_Post;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-20] - @author: Kelvin - Class Metabox cấu hình động thay thế 15 file metabox-* rời rạc (chuẩn hóa Nonce, capability, escaping và PHP 8.3)
 */
class ConfigurableMetabox
{
    private string $id;
    private string $title;
    /** @var string[] */
    private array $screens;
    private string $context;
    private string $type;
    private array $config;
    private string $nonceAction;
    private string $nonceName;

    /**
     * [2026-11-20] - @author: Kelvin - Khởi tạo hooks add_meta_boxes và save_post (chỉ chạy trong WP-Admin)
     */
    public function __construct(string $id, array $config)
    {
        if (!is_admin()) {
            return;
        }

        $this->id          = $id;
        $this->title       = (string) ($config['title'] ?? '');
        $this->screens     = (array) ($config['screens'] ?? []);
        $this->context     = (string) ($config['context'] ?? 'advanced');
        $this->type        = (string) ($config['type'] ?? 'text_fields');
        $this->config      = $config;
        $this->nonceAction = "dgw_metabox_{$this->id}_action";
        $this->nonceName   = "dgw_metabox_{$this->id}_nonce";

        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('save_post', [$this, 'saveMetaBox']);
    }

    /**
     * [2026-11-20] - @author: Kelvin - Đăng ký Metabox lên các màn hình Post Type được cấu hình
     */
    public function registerMetaBox(): void
    {
        $translatedTitle = $this->title;
        if (!empty($this->config['title_parts']) && is_array($this->config['title_parts'])) {
            $parts = array_map(static function (string $part): string {
                return __($part, 'dgw');
            }, $this->config['title_parts']);
            $translatedTitle = implode(' - ', $parts);
        } elseif ($this->title !== '') {
            $translatedTitle = __($this->title, 'dgw');
        }

        foreach ($this->screens as $screen) {
            add_meta_box(
                $this->id,
                $translatedTitle,
                [$this, 'renderMetaBox'],
                $screen,
                $this->context,
                'default'
            );
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render giao diện Metabox theo loại cấu hình với Late Escaping
     */
    public function renderMetaBox(WP_Post $post): void
    {
        wp_nonce_field($this->nonceAction, $this->nonceName);

        switch ($this->type) {
            case 'checkbox':
                $this->renderCheckbox($post);
                break;

            case 'radio_lang':
                $this->renderLanguageRadio($post);
                break;

            case 'order':
                $this->renderOrderInput($post);
                break;

            case 'download_file':
                $this->renderDownloadFile($post);
                break;

            case 'text_fields':
                $this->renderTextFields($post);
                break;

            case 'seo':
                $this->renderSeoFields($post);
                break;

            case 'editors':
                $this->renderEditors($post);
                break;

            case 'multilang_tabs':
                $this->renderMultilangTabs($post);
                break;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Kiểm tra bảo mật (Nonce, Autosave, Revision, Post Type, Capability) và lưu dữ liệu Metabox
     */
    public function saveMetaBox(int $postId): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($postId)) {
            return;
        }

        if (
            !isset($_POST[$this->nonceName]) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST[$this->nonceName])), $this->nonceAction)
        ) {
            return;
        }

        $postType = get_post_type($postId);
        if (!is_string($postType) || !in_array($postType, $this->screens, true)) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        switch ($this->type) {
            case 'checkbox':
                $inputName = (string) ($this->config['input_name'] ?? '');
                $metaKey   = (string) ($this->config['meta_key'] ?? '');
                $isChecked = isset($_POST[$inputName]) && $_POST[$inputName] === 'on' ? '1' : '0';
                update_post_meta($postId, $metaKey, $isChecked);
                break;

            case 'radio_lang':
                if (isset($_POST['radio-lang'])) {
                    $lang = sanitize_text_field(wp_unslash((string) $_POST['radio-lang']));
                    update_post_meta($postId, '_metabox_langguage', in_array($lang, ['cn', 'vn', 'en'], true) ? $lang : 'cn');
                }
                break;

            case 'order':
                if (isset($_POST['metabox-order'])) {
                    // Luôn giữ key _metabox_order trong wp_postmeta để bài viết không bị ẩn khỏi WP_Query sort theo meta_key
                    $orderVal = sanitize_text_field(wp_unslash((string) $_POST['metabox-order']));
                    update_post_meta($postId, '_metabox_order', $orderVal);
                }
                break;

            case 'download_file':
                if (!empty($_FILES['file_upload']['name']) && function_exists('uploadFileDownLoad')) {
                    $oldFile = (string) get_post_meta($postId, '_download_file', true);
                    $upload  = uploadFileDownLoad($_FILES, $oldFile);
                    if (is_string($upload) && $upload !== '') {
                        update_post_meta($postId, '_download_file', sanitize_file_name($upload));
                    }
                }
                break;

            case 'text_fields':
            case 'seo':
                $fields = (array) ($this->config['fields'] ?? []);
                foreach ($fields as $field) {
                    $inputName = (string) ($field['name'] ?? '');
                    $metaKey   = (string) ($field['meta_key'] ?? '');
                    if ($inputName === '' || $metaKey === '' || !isset($_POST[$inputName])) {
                        continue;
                    }

                    $value = trim(sanitize_text_field(wp_unslash((string) $_POST[$inputName])));
                    if ($value !== '') {
                        update_post_meta($postId, $metaKey, $value);
                    } elseif (!empty($field['delete_when_empty'])) {
                        delete_post_meta($postId, $metaKey);
                    } else {
                        update_post_meta($postId, $metaKey, '');
                    }
                }
                break;

            case 'editors':
                $fields = (array) ($this->config['fields'] ?? []);
                foreach ($fields as $field) {
                    $inputName = (string) ($field['name'] ?? '');
                    $metaKey   = (string) ($field['meta_key'] ?? '');
                    if ($inputName !== '' && $metaKey !== '' && isset($_POST[$inputName])) {
                        update_post_meta($postId, $metaKey, wp_kses_post(wp_unslash((string) $_POST[$inputName])));
                    }
                }
                break;

            case 'multilang_tabs':
                $prefix = (string) ($this->config['meta_prefix'] ?? '');
                foreach (['cn', 'vn', 'en'] as $lang) {
                    $nameInput    = "txt-name-{$lang}";
                    $contentInput = "txt-content-{$lang}";
                    if (isset($_POST[$nameInput])) {
                        update_post_meta($postId, "_{$prefix}_name_{$lang}", sanitize_text_field(wp_unslash((string) $_POST[$nameInput])));
                    }
                    if (isset($_POST[$contentInput])) {
                        update_post_meta($postId, "_{$prefix}_content_{$lang}", wp_kses_post(wp_unslash((string) $_POST[$contentInput])));
                    }
                }
                break;
        }
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render checkbox đơn (Home Page / Sidebar)
     */
    private function renderCheckbox(WP_Post $post): void
    {
        $inputName = (string) ($this->config['input_name'] ?? '');
        $metaKey   = (string) ($this->config['meta_key'] ?? '');
        $label     = (string) ($this->config['label'] ?? $this->title);
        $checked   = (string) get_post_meta($post->ID, $metaKey, true);
        ?>
        <div class="meta-row-two">
            <div class="col">
                <div class="title-cell">
                    <input type="checkbox" id="<?php echo esc_attr($inputName); ?>" name="<?php echo esc_attr($inputName); ?>" <?php checked($checked, '1'); ?> />
                    <label for="<?php echo esc_attr($inputName); ?>" style="margin-right: 15px"><?php echo esc_html__($label, 'dgw'); ?></label>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render radio chọn ngôn ngữ bài viết (CN / VN)
     */
    private function renderLanguageRadio(WP_Post $post): void
    {
        $current = (string) get_post_meta($post->ID, '_metabox_langguage', true);
        if ($current !== 'vn' && $current !== 'cn') {
            $current = 'cn';
        }
        ?>
        <div class="meta-row-two">
            <div class="col">
                <div class="cell-text radio-space">
                    <div>
                        <input type="radio" id="radio-cn" name="radio-lang" value="cn" <?php checked($current, 'cn'); ?> />
                        <label for="radio-cn"><?php echo esc_html__('Chinese', 'dgw'); ?></label>
                    </div>
                    <div>
                        <input type="radio" id="radio-vn" name="radio-lang" value="vn" <?php checked($current, 'vn'); ?> />
                        <label for="radio-vn"><?php echo esc_html__('Vietnamese', 'dgw'); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render ô nhập thứ tự hiển thị (_metabox_order)
     */
    private function renderOrderInput(WP_Post $post): void
    {
        $val = (string) get_post_meta($post->ID, '_metabox_order', true);
        ?>
        <div class="row-four-column">
            <div class="col">
                <div class="cell-text">
                    <input type="text"
                        id="metabox-order"
                        name="metabox-order"
                        class="type-number my-input"
                        maxlength="5"
                        placeholder="<?php echo esc_attr__('The larger the number will show in front', 'dgw'); ?>"
                        value="<?php echo esc_attr($val); ?>" />
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render ô upload file download (đã sửa lỗi hằng số _download_file thiếu dấu nháy trên PHP 8.3)
     */
    private function renderDownloadFile(WP_Post $post): void
    {
        $currentFile = (string) get_post_meta($post->ID, '_download_file', true);
        ?>
        <div class="clear"></div>
        <div class="row-one-column">
            <div class="cell-title">
                <label><?php echo esc_html__('Upload File', 'dgw'); ?>
                    <i style="margin-left: 3rem; font-size: 0.8rem; color: #ccc">目前檔案 ： <?php echo esc_html($currentFile); ?></i>
                </label>
            </div>
            <div class="cell-text" style="margin: 2rem">
                <input type="file" name="file_upload" id="file_upload" />
            </div>
        </div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render danh sách các ô input text (Link, Source, YouTube, View/Like, Web/FreeBook)
     */
    private function renderTextFields(WP_Post $post): void
    {
        $rowClass = (string) ($this->config['row_class'] ?? 'row-one-column');
        $fields   = (array) ($this->config['fields'] ?? []);
        ?>
        <div class="<?php echo esc_attr($rowClass); ?>">
            <?php foreach ($fields as $field) :
                $inputName  = (string) ($field['name'] ?? '');
                $metaKey    = (string) ($field['meta_key'] ?? '');
                $label      = (string) ($field['label'] ?? '');
                $inputClass = (string) ($field['input_class'] ?? 'my-input');
                $val        = (string) get_post_meta($post->ID, $metaKey, true);
            ?>
                <div class="col">
                    <div class="cell-title">
                        <label for="<?php echo esc_attr($inputName); ?>"><?php echo $label !== '' ? esc_html__($label, 'dgw') : ''; ?></label>
                    </div>
                    <div class="cell-text">
                        <input type="text"
                            name="<?php echo esc_attr($inputName); ?>"
                            id="<?php echo esc_attr($inputName); ?>"
                            class="<?php echo esc_attr($inputClass); ?>"
                            value="<?php echo esc_attr($val); ?>" />
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="clear"></div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render các trường SEO (Title, Key, Description)
     */
    private function renderSeoFields(WP_Post $post): void
    {
        $seoTitle = (string) get_post_meta($post->ID, '_seo_title', true);
        $seoKey   = (string) get_post_meta($post->ID, '_seo_key', true);
        $seoDesc  = (string) get_post_meta($post->ID, '_seo_description', true);
        ?>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label for="txt_title" style="font-size: 13px"><?php echo esc_html__('Title SEO', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" id="txt_title" name="txt_title" class="my-input" value="<?php echo esc_attr($seoTitle); ?>" />
                </div>
            </div>
        </div>
        <div class="row-two-column">
            <div class="col">
                <div class="cell-title">
                    <label for="txt_key" style="font-size: 13px"><?php echo esc_html__('Key SEO', 'dgw'); ?></label>
                </div>
                <div class="cell-text">
                    <input type="text" id="txt_key" name="txt_key" class="my-input" value="<?php echo esc_attr($seoKey); ?>" />
                </div>
            </div>
        </div>
        <div class="row-one-column">
            <div class="cell-title">
                <label for="txt_description" style="font-size: 13px"><?php echo esc_html__('Description SEO', 'dgw'); ?></label>
            </div>
            <div class="cell-text">
                <textarea id="txt_description" name="txt_description" rows="3" cols="117" maxlength="80"><?php echo esc_textarea($seoDesc); ?></textarea>
            </div>
        </div>
        <div class="clear"></div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render danh sách wp_editor (Solutions / Industries)
     */
    private function renderEditors(WP_Post $post): void
    {
        $fields = (array) ($this->config['fields'] ?? []);
        ?>
        <div class="clear"></div>
        <div>
            <?php foreach ($fields as $field) :
                $inputName = (string) ($field['name'] ?? '');
                $metaKey   = (string) ($field['meta_key'] ?? '');
                $label     = (string) ($field['label'] ?? '');
                $content   = (string) get_post_meta($post->ID, $metaKey, true);
            ?>
                <div class="row-one-column">
                    <div class="cell-title">
                        <label><?php echo esc_html__($label, 'dgw'); ?></label>
                    </div>
                    <div class="cell-text">
                        <?php wp_editor($content, $inputName, ['wpautop' => false, 'editor_height' => '300px']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * [2026-11-20] - @author: Kelvin - Render tabs đa ngôn ngữ (Active / Service)
     */
    private function renderMultilangTabs(WP_Post $post): void
    {
        $prefix       = (string) ($this->config['meta_prefix'] ?? 'active');
        $nameLabel    = (string) ($this->config['name_label'] ?? 'Active Name');
        $contentLabel = (string) ($this->config['content_label'] ?? 'Active Content');
        $langs        = [
            '1' => ['code' => 'cn', 'label' => 'Chinese'],
            '2' => ['code' => 'vn', 'label' => 'Vietnamese'],
            '3' => ['code' => 'en', 'label' => 'English'],
        ];
        ?>
        <div class="clear"></div>
        <div id="tabs">
            <ul>
                <?php foreach ($langs as $tabIdx => $langInfo) : ?>
                    <li><a href="#tabs-<?php echo esc_attr((string) $tabIdx); ?>"><?php echo esc_html__($langInfo['label'], 'dgw'); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ($langs as $tabIdx => $langInfo) :
                $code       = $langInfo['code'];
                $langText   = __($langInfo['label'], 'dgw');
                $nameVal    = (string) get_post_meta($post->ID, "_{$prefix}_name_{$code}", true);
                $contentVal = (string) get_post_meta($post->ID, "_{$prefix}_content_{$code}", true);
            ?>
                <div id="tabs-<?php echo esc_attr((string) $tabIdx); ?>">
                    <div class="row-two-column">
                        <div class="col">
                            <div class="cell-title">
                                <label><?php echo esc_html(__($nameLabel, 'dgw') . " ({$langText})"); ?></label>
                            </div>
                            <div class="cell-text">
                                <input type="text"
                                    name="txt-name-<?php echo esc_attr($code); ?>"
                                    id="txt-name-<?php echo esc_attr($code); ?>"
                                    class="my-input"
                                    value="<?php echo esc_attr($nameVal); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="row-one-column">
                        <div class="cell-title">
                            <label><?php echo esc_html(__($contentLabel, 'dgw') . " ({$langText})"); ?></label>
                        </div>
                        <div class="cell-text">
                            <?php wp_editor($contentVal, "txt-content-{$code}", ['wpautop' => false, 'editor_height' => '300px']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <script>
            jQuery(function () {
                jQuery("#tabs").tabs();
            });
        </script>
        <?php
    }
}
