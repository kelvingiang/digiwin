<?php
declare(strict_types=1);

namespace DGW\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Service gửi thông báo tự động đến Lark (Feishu) qua Webhook Bot
 * [25/11/2026] - Chuyển Lark Webhook URL sang đọc từ EnvConfig (LARK_WEBHOOK_URL) thay vì hardcode
 */
class LarkService
{
    /**
     * [18/11/2026] - Gửi nội dung tin nhắn đến Lark bot
     * [25/11/2026] - Kiểm tra cấu hình LARK_WEBHOOK_URL trước khi gửi request
     */
    public static function send(string $message)
    {
        $webhookUrl = defined('LARK_WEBHOOK_URL') ? (string) LARK_WEBHOOK_URL : '';
        if ($webhookUrl === '') {
            error_log('Lark Config Error: Missing LARK_WEBHOOK_URL.');
            return false;
        }

        $data = [
            'msg_type' => 'text',
            'content'  => [
                'text' => $message,
            ],
        ];

        $response = wp_remote_post($webhookUrl, [
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'body'    => wp_json_encode($data),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            error_log('Lark Notification Error: ' . $response->get_error_message());
            return false;
        }

        return wp_remote_retrieve_body($response);
    }
}
