<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TelegramBotApiClient
{
    protected string $endpoint;

    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey)
    {
        $this->endpoint = 'https://api.telegram.org/bot' . $apiKey . '/';
    }

    public function sendMessage(int $recipient, string $text)
    {
        $this->call('sendMessage', [
            'chat_id' => $recipient,
            'text' => $text
        ]);
    }

    private function call(string $method, array $params=[])
    {
        $response = Http::post($this->endpoint . $method, $params);

        if ($response->failed()) {
            throw new BadRequestException($response->body(), $response->status());
        }

        return $response->json();
    }
}
