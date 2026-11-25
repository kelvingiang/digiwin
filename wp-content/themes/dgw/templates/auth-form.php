<?php
declare(strict_types=1);

/**
 * [2026-11-19] - @author: Kelvin - View Template: Render form đăng ký / cập nhật thông tin thành viên (tách khỏi inc/front/front-core.php)
 */

if (!defined('ABSPATH')) {
    exit;
}

$fields     = isset($args['fields']) && is_array($args['fields']) ? $args['fields'] : [];
$prefix     = isset($args['prefix']) ? (string) $args['prefix'] : 'reg-';
$buttonText = isset($args['button_text']) ? (string) $args['button_text'] : __('Register', 'dgw');
$data       = $args['data'] ?? null;
$currentCol = '';

foreach ($fields as $field) {
    if (isset($field['type']) && $field['type'] === 'separator') {
        if ($currentCol !== '') {
            echo '</div>';
            $currentCol = '';
        }
        echo '<hr class="hr-style">';
        continue;
    }

    $col = (string) ($field['col'] ?? 'one');
    if ($currentCol !== $col) {
        if ($currentCol !== '') {
            echo '</div>';
        }
        echo '<div class="' . esc_attr($col) . '-columns">';
        $currentCol = $col;
    }

    $id        = (string) ($field['id'] ?? '');
    $val       = ($data && $id !== '') ? (string) ($data->$id ?? '') : '';
    $type      = (string) ($field['type'] ?? 'text');
    $class     = (string) ($field['class'] ?? '');
    $max       = isset($field['max']) ? (int) $field['max'] : 0;
    $place     = (string) ($field['place'] ?? '');
    $inputMode = (strpos($class, 'number') !== false || strpos($class, 'phone') !== false) ? 'numeric' : '';
    ?>
    <div class="row-cell">
        <label for="<?php echo esc_attr($prefix . $id); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>

        <?php if ($type === 'select') : ?>
            <select id="<?php echo esc_attr($prefix . $id); ?>" class="<?php echo esc_attr($class); ?>">
                <?php
                $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
                foreach ($options as $optValue => $optLabel) {
                    $isSelected = selected($val, (string) $optValue, false);
                    echo '<option data-content="' . esc_attr((string) $optLabel) . '" value="' . esc_attr((string) $optValue) . '" ' . $isSelected . '>' . esc_html((string) $optLabel) . '</option>';
                }
                ?>
            </select>
        <?php else : ?>
            <input type="<?php echo esc_attr($type); ?>"
                   id="<?php echo esc_attr($prefix . $id); ?>"
                   class="<?php echo esc_attr($class); ?>"
                   value="<?php echo esc_attr($val); ?>"
                   <?php if ($place !== '') : ?>placeholder="<?php echo esc_attr($place); ?>"<?php endif; ?>
                   <?php if ($max > 0) : ?>maxlength="<?php echo esc_attr((string) $max); ?>"<?php endif; ?>
                   <?php if ($inputMode !== '') : ?>inputmode="<?php echo esc_attr($inputMode); ?>"<?php endif; ?>
                   autocomplete="<?php echo ($type === 'password') ? 'new-password' : 'on'; ?>" />
        <?php endif; ?>
    </div>
    <?php
}

if ($currentCol !== '') {
    echo '</div>';
}
?>
<div class="privacy-policy">
    <div>
        <input type="checkbox" id="chk-pri" checked />
        <label for="chk-pri"><?php echo esc_html(__('I agree with Digiwin\'s privacy policy', 'dgw')); ?></label>
    </div>
    <div>
        <a href="<?php echo esc_url('https://www.digiwin.com.vn/digiwinasean_privacy-policy/'); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo esc_html(__('privacy policy', 'dgw')); ?>
        </a>
    </div>
</div>
<?php if ($prefix === 'reg-') : ?>
    <?php
    // [25/11/2026] - Đọc Cloudflare Turnstile sitekey từ EnvConfig (wp-config.php / .env) thay vì hardcode
    $turnstileSiteKey = defined('TURNSTILE_SITE_KEY') ? (string) TURNSTILE_SITE_KEY : '';
    if ($turnstileSiteKey !== '') :
    ?>
        <div class="turnstile-container" style="margin-bottom: 15px; display: flex; justify-content: flex-start;">
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
            <div class="cf-turnstile" data-sitekey="<?php echo esc_attr($turnstileSiteKey); ?>" data-theme="light"></div>
        </div>
    <?php endif; ?>
<?php endif; ?>
<div class="btn-space">
    <button id="btn-register" class="btn-my-style">
        <?php echo esc_html($buttonText); ?>
    </button>
    <p id="<?php echo ($prefix === 'reg-') ? 'register' : 'change-info'; ?>-msg" class="msg"></p>
</div>
