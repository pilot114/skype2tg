<?php

include './vendor/autoload.php';

use SkypeToTelegram\Skype;
use SkypeToTelegram\Telegram;

$authHeader = 'skypetoken=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEwNiIsIng1dCI6Im9QMWFxQnlfR3hZU3pSaXhuQ25zdE5PU2p2cyIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NzI2MDQyODMsImV4cCI6MTY3MjY5MDY4Miwic2t5cGVpZCI6InNldmVyYXNzZSIsInNjcCI6OTU4LCJjc2kiOiIxNjcyNjA0MjgyIiwiY2lkIjoiM2I0MjAzODFmYWQ4Nzc5MSIsImFhdCI6MTY3MjMzMjE3OX0.aCgQhpSw8DoHEmg6B_N5YEPc_9WX5CCJr8u_hHDkqdNFJkuZr1mKenQNVxIXKjvWBchsC0K1SQWZb5eAM4HkMgB9A-VOtav1E74LqCT85kyhFlWiZvYnYLP8xMmnV6dVhDp3eym_zeY2mOsviHfDmVO3thNCJo_FXCVP12pKQcUU1KSPvE_7JIGoIW7fHxDGRNCUv4pGXFMSQ-aMZp6BWEru7MwzmlkFjoicsK6-yLxPUHm7HHsGegGgoKuqk8PJoMwZq3XGWCOG2uah22G8N4zMo_Ba_Lc4--rM-6GmaEm9nYkcmlDbR_-cZaTg9PnnfuFILszg61dF4GDQ8dIB0g';
$endpointHeader = 'registrationToken=U2lnbmF0dXJlOjI6Mjg6QVFRQUFBQXFYUGxuUitQRmFXbXlZOU5wMVBYUTtWZXJzaW9uOjY6MToxO0lzc3VlVGltZTo0OjE5OjUyNDk3NjgwMjkzNDY1OTE3MzM7RXAuSWRUeXBlOjc6MTo4O0VwLklkOjI6OTpzZXZlcmFzc2U7RXAuRXBpZDo1OjM2OjdmYmQwZjRkLTAzN2UtNDc2YS05ZTEyLTQ3NjhjOTU1MDI2OTtFcC5Mb2dpblRpbWU6NzoxOjA7RXAuQXV0aFRpbWU6NDoxOTo1MjQ5NzY4MDI5MzQ2NTkxNzMzO0VwLkF1dGhUeXBlOjc6MjoxNTtFcC5FeHBUaW1lOjQ6MTk6NTI0OTc2ODg5MzI0NzM4NzkwNDtVc3IuTmV0TWFzazoxMToxOjI7VXNyLlhmckNudDo2OjE6MDtVc3IuUmRyY3RGbGc6MjowOjtVc3IuRXhwSWQ6OToxOjA7VXNyLkV4cElkTGFzdExvZzo0OjE6MDtVc2VyLkF0aEN0eHQ6MjozODA6Q2xOcmVYQmxWRzlyWlc0SmMyVjJaWEpoYzNObEFRTlZhV01VTVM4eEx6QXdNREVnTVRJNk1EQTZNREFnUVUwTVRtOTBVM0JsWTJsbWFXVmtrWGZZK29FRFFqc0FBQUFBQUFCQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUNYTmxkbVZ5WVhOelpRQUFBQUFBQUFBQUFBZE9iMU5qYjNKbEFBQUFBQVFBQUFBQUFBQUFBQUFBQUpGMzJQcUJBMEk3QUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFCQ1hObGRtVnlZWE56WlFBQUFBQUFldXF4WXdnQUFBQURWV2xqQ0Vsa1pXNTBhWFI1RGtsa1pXNTBhWFI1VlhCa1lYUmxDRU52Ym5SaFkzUnpEa052Ym5SaFkzUnpWWEJrWVhSbENFTnZiVzFsY21ObERVTnZiVzExYm1sallYUnBiMjRWUTI5dGJYVnVhV05oZEdsdmJsSmxZV1JQYm14NUFBQT07; expires=1672690682; endpointId={7fbd0f4d-037e-476a-9e12-4768c9550269}';
$cookieForApi = 'platformid_asm=1418; skplet=1672690683; skypetoken_asm=eyJhbGciOiJSUzI1NiIsImtpZCI6IjEwNiIsIng1dCI6Im9QMWFxQnlfR3hZU3pSaXhuQ25zdE5PU2p2cyIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NzI2MDQyODMsImV4cCI6MTY3MjY5MDY4Miwic2t5cGVpZCI6InNldmVyYXNzZSIsInNjcCI6OTU4LCJjc2kiOiIxNjcyNjA0MjgyIiwiY2lkIjoiM2I0MjAzODFmYWQ4Nzc5MSIsImFhdCI6MTY3MjMzMjE3OX0.aCgQhpSw8DoHEmg6B_N5YEPc_9WX5CCJr8u_hHDkqdNFJkuZr1mKenQNVxIXKjvWBchsC0K1SQWZb5eAM4HkMgB9A-VOtav1E74LqCT85kyhFlWiZvYnYLP8xMmnV6dVhDp3eym_zeY2mOsviHfDmVO3thNCJo_FXCVP12pKQcUU1KSPvE_7JIGoIW7fHxDGRNCUv4pGXFMSQ-aMZp6BWEru7MwzmlkFjoicsK6-yLxPUHm7HHsGegGgoKuqk8PJoMwZq3XGWCOG2uah22G8N4zMo_Ba_Lc4--rM-6GmaEm9nYkcmlDbR_-cZaTg9PnnfuFILszg61dF4GDQ8dIB0g';

$botApiKey = '5077359091:AAGwIoBJ4-tM9k96aB1tU0PiNsVehDIy6lM';
$botName = 'Pilot114_pybot';
$chatId = 859029886;

$skype = new Skype($authHeader, $endpointHeader, $cookieForApi);
$tg = new Telegram($botApiKey, $botName);

foreach ($skype->listenEvents(1000) as $event) {
    $message = $skype->formatEvent($event);
    if (!is_null($message)) {
        if ($message->text) {
            $tg->sendText($chatId, $message->text);
        }
        foreach ($message->images as $image) {
            $imgData = base64_decode($image);
            $tg->sendImage($chatId, $image);
        }
    }
}