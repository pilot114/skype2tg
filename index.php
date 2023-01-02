<?php

include './vendor/autoload.php';

use SkypeToTelegram\Skype;
use SkypeToTelegram\Telegram;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$authHeader = $_ENV['AUTH_HEADER'];
$epHeader   = $_ENV['EP_HEADER'];
$cookie     = $_ENV['COOKIE'];

$botApiKey  = $_ENV['BOT_API_KEY'];
$botName    = $_ENV['BOT_NAME'];
$chatId     = $_ENV['CHAT_ID'];

$skype = new Skype($authHeader, $epHeader, $cookie);
$skype->setDebug(true);
$tg = new Telegram($botApiKey, $botName);

foreach ($skype->listenEvents(1000) as $event) {
    $message = $skype->formatEvent($event);
    if (is_null($message)) {
        continue;
    }
    if ($message->text) {
        $tg->sendText($chatId, $message->text);
    }
    foreach ($message->images as $image) {
        $imgData = base64_decode($image);
        $tg->sendImage($chatId, $image);
    }
}