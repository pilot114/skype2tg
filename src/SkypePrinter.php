<?php

namespace SkypeToTelegram;

/**
 * event types handled of protected methods, named as these events
 * param $isFull - for print all informative data
 */
class SkypePrinter
{
    public function __construct(protected Skype $skype) {}

    public function formatEvent(array $event): ?SkypeMessage
    {
        $type = $event['resourceType'];

        $messageEvents = [
            'NewMessage',
            'MessageUpdate',
        ];

        if (in_array($type, $messageEvents)) {
            return $this->$type($event['resource']);
        }
        return null;
    }

    protected function NewMessage(array $event, $isFull = false): string | SkypeMessage
    {
        preg_match('#/v1/users/ME/contacts/.*:(.*)#', $event['from'], $matches);
        $fromLogin = $matches[1];

        if (empty($event['imdisplayname'])) {
            dump('empty imdisplayname!');
            dump($event);
            return '';
        }

        $imDisplayName = $event['imdisplayname'];

        $composeType = sprintf(
            '%s | %s | %s',
            $event['type'],
            $event['messagetype'],
            $event['contenttype'],
        );
        if (empty($event['content'])) {
            dump('empty content!');
            dump($event);
            return '';
        }
        $content = $event['content'];
        $composeTime = $this->timeFormat($event['composetime']);
        $originalArrivalTime = $this->timeFormat($event['originalarrivaltime']);

        // is image
        if (str_contains($content, 'url_thumbnail')) {
            $message = '<URIObject uri="https://api.asm.skype.com/v1/objects/0-weu-d1-d175d28a7cb9877d791df72d0d418468" url_thumbnail="https://api.asm.skype.com/v1/objects/0-weu-d1-d175d28a7cb9877d791df72d0d418468/views/imgt1_anim" type="Picture.1" doc_id="0-weu-d1-d175d28a7cb9877d791df72d0d418468" width="1536.8462809917355" height="2048">Чтобы просмотреть эту общую фотографию, перейдите к: <a href="https://login.skype.com/login/sso?go=xmmfallback?pic=0-weu-d1-d175d28a7cb9877d791df72d0d418468">https://login.skype.com/login/sso?go=xmmfallback?pic=0-weu-d1-d175d28a7cb9877d791df72d0d418468</a><OriginalName v="4615E659-7F12-4912-BCB2-27CED7694D27.jpg"></OriginalName><FileSize v="1084901"></FileSize><meta type="photo" originalName="4615E659-7F12-4912-BCB2-27CED7694D27.jpg"></meta></URIObject>';
            preg_match('#url_thumbnail="(.*)" type# ', $message, $matches);
            $url = $matches[1];
            $imageName = $this->skype->getLocalImageName($url);
            $message = new SkypeMessage();
            $message->images = [$imageName];
            return $message;
        }

        if ($isFull) {
            return sprintf(
                "from: %s (%s), type: %s, arrival: %s, compose: %s\n%s\n",
                $fromLogin,
                $imDisplayName,
                $composeType,
                $originalArrivalTime,
                $composeTime,
                $content
            );
        }
        return sprintf(
            "> %s (%s)\n%s\n",
            $fromLogin,
            $imDisplayName,
            $content
        );
    }

    protected function MessageUpdate(array $event): string
    {
        $content = $event['content'];
        $properties = json_encode($event['properties']);
        return "$content ($properties)\n";
    }

    protected function timeFormat(string $isoDate): string
    {
        return (new \DateTimeImmutable($isoDate))->format('h:i:s');
    }

    /*
    // UserPresence - присутствие пользователя
    protected function UserPresence(array $event): string
    {
        preg_match('#/v1/users/.*:(.*)/presenceDocs#', $event['selfLink'], $matches);
        $userLogin = $matches[1];
        $lastSeen = empty($event['lastSeenAt']) ? '-' : $this->timeFormat($event['lastSeenAt']);
        return sprintf(
            "login: %s, seen: %s, status: %s, availability: %s\n",
            $userLogin,
            $lastSeen,
            $event['status'],
            $event['availability'],
        );
    }

    // EndpointPresence - эндпоинты пользователя
    protected function EndpointPresence(array $event): string
    {
        preg_match('#/v1/users/.*:(.*)/endpoints/(.*)/presenceDocs#', $event['selfLink'], $matches);
//    $userLogin = $matches[1];
        $endpoint = trim($matches[2], '{}');
        $info = sprintf(
            'type: %s, version: %s, name: %s',
            $event['publicInfo']['typ'],
            $event['publicInfo']['version'],
            $event['publicInfo']['skypeNameVersion'],
        );
        return sprintf(
            "%s (%s)\n",
            $endpoint,
            $info,
        );
    }

    protected function ConversationUpdate(array $event): string
    {
        return "- \n";
    }
    */
}