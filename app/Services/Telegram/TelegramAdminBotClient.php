<?php

namespace App\Services\Telegram;

use JetBrains\PhpStorm\Pure;

class TelegramAdminBotClient extends TelegramBotApiClient
{
    protected static array $admins = [883101078, 348022984, 953989122];

    public static function createInfoMessage(string $header, array $params, string $hashtag)
    {
        $stringParams = [];

        foreach ($params as $key => $value) {
            $val = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            $stringParams[] = $key . ': ' . $val;
        }

        return $header . "\n\n" . join("\n", $stringParams) . "\n\n#" . $hashtag;
    }

    public static function sendAdminNotification(string $text)
    {
        $apiClient = self::createAdminBotApiClient();

        foreach (self::$admins as $admin) {
            $apiClient->sendMessage($admin, $text);
        }
    }

    public static function sendArray(array $arr, string $header): string
    {
        $stringParams = [];

        foreach ($arr as $key => $value) {
            $val = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            $stringParams[] = $key . ': ' . $val;
        }

        return $header . "\n\n" . join("\n", $stringParams);
    }

    #[Pure]
    public static function createAdminBotApiClient(): TelegramAdminBotClient
    {
        return new self('1953107097:AAEErtQTCkveJdLBljpyEPMzxycrGS7nKz4');
    }
}
