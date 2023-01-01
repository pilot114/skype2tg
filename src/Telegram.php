<?php

namespace SkypeToTelegram;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram as TelegramBot;

class Telegram
{
    protected TelegramBot $telegram;

    public function __construct(
        protected string $botApiKey,
        protected string $botName,
    ) {
        try {
            $telegram = new TelegramBot($botApiKey, $botName);
            $telegram->useGetUpdatesWithoutDatabase();
            $this->telegram = $telegram;
        } catch (TelegramException $e) {
            dump($e);
            die();
        }
    }

    public function getUpdates(): ServerResponse
    {
        return $this->telegram->handleGetUpdates();
    }

    public function sendText(string $chatId, string $text): ServerResponse
    {
        return @Request::sendMessage([
            'chat_id' => $chatId,
            'text'    => $text,
        ]);
    }

    public function sendImage(string $chatId, string $url): ServerResponse
    {
        return @Request::sendPhoto([
            'chat_id' => $chatId,
            'photo'   => $url,
        ]);
    }
}