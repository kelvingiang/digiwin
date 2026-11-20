<?php
declare(strict_types=1);

namespace DGW\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * [18/11/2026] - Service gửi thông báo tự động đến Lark (Feishu) qua Webhook Bot
 */
class LarkService
{
    private static string $webhookUrl = 'https://open.larksuite.com/open-apis/bot/v2/hook/2a2da959-0b1e-4b3c-ab3f-85703d65e3b0';

    /**
     * [18/11/2026] - Gửi nội dung tin nhắn đến Lark bot
     */
    public static function send(string $message)
    {
        $data = [
            'msg_type' => 'text',
            'content'  => [
                'text' => $message,
            ],
        ];

        $response = wp_remote_post(self::$webhookUrl, [
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
