<?php
declare(strict_types=1);

namespace DGW\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [2026-11-19] - @author: Kelvin - Helper cấu hình và điều phối render Form Thành viên (tách khỏi inc/front/front-core.php)
 */
class AuthFormHelper
{
    /**
     * [2026-11-19] - @author: Kelvin - Cấu hình danh sách trường của Form đăng ký / cập nhật thành viên
     */
    public static function getFieldsConfig(): array
    {
        $positionOptions   = array_merge(['' => __('Select Position', 'dgw')], MenuHelper::memberPositions());
        $industryOptions   = array_merge(['' => __('Select Industry', 'dgw')], MenuHelper::industrySectors());
        $departmentOptions = array_merge(['' => __('Select Department', 'dgw')], MenuHelper::departments());

        return [
            ['id' => 'email',      'label' => __('E-mail', 'dgw'),       'col' => 'two', 'type' => 'email',    'place' => 'example@email.com'],
            ['id' => 'password',   'label' => __('Password', 'dgw'),     'col' => 'two', 'type' => 'password', 'place' => '******'],
            ['type' => 'separator'],
            ['id' => 'company',    'label' => __('Company Name', 'dgw'), 'col' => 'one', 'type' => 'text'],
            ['id' => 'username',   'label' => __('Full Name', 'dgw'),    'col' => 'two', 'type' => 'text',     'place' => ''],
            ['id' => 'position',   'label' => __('Position', 'dgw'),     'col' => 'two', 'type' => 'select',   'options' => $positionOptions],
            ['id' => 'phone',      'label' => __('Phone', 'dgw'),        'col' => 'two', 'type' => 'text',     'class' => 'type-phone-more', 'max' => 15],
            ['id' => 'tax',        'label' => __('Tax Number', 'dgw'),   'col' => 'two', 'type' => 'text',     'class' => 'type-number',     'max' => 13],
            ['id' => 'industry',   'label' => __('Industry', 'dgw'),     'col' => 'two', 'type' => 'select',   'options' => $industryOptions],
            ['id' => 'department', 'label' => __('Department', 'dgw'),   'col' => 'two', 'type' => 'select',   'options' => $departmentOptions],
        ];
    }

    /**
     * [2026-11-19] - @author: Kelvin - Chuẩn bị dữ liệu và nạp View Template templates/auth-form.php
     */
    public static function renderFull(string $prefix = 'reg-', string $buttonText = '', $data = null): void
    {
        get_template_part('templates/auth-form', null, [
            'fields'      => self::getFieldsConfig(),
            'prefix'      => $prefix,
            'button_text' => $buttonText ?: __('Register', 'dgw'),
            'data'        => $data,
        ]);
    }
}
