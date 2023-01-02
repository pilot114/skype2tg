<?php

namespace SkypeToTelegram;

/**
 * event types handled of protected methods, named as these events
 * param $isFull - for print all informative data
 */
class SkypePrinter
{
    public function __construct(protected Skype $skype) {}

    public function formatEvent(array $event, bool $isDebug = false): ?SkypeMessage
    {
        $type = $event['resourceType'] ?? null;

        if ($type === 'NewMessage') {
            return $this->NewMessage($event['resource']);
        }
        if ($type === 'MessageUpdate') {
            return $this->MessageUpdate($event['resource']);
        }
        if ($isDebug) {
            dump('[DEBUG EVENT]');
            dump($event);
        }
        return null;
    }

    protected function NewMessage(array $event, $isFull = false): SkypeMessage
    {
        $message = new SkypeMessage();

        preg_match('#/v1/users/ME/contacts/.*:(.*)#', $event['from'], $matches);
        $fromLogin = $matches[1];

        if (empty($event['imdisplayname'])) {
            dump('empty imdisplayname!');
            dump($event);
            return $message;
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
            return $message;
        }
        $content = $event['content'];
        $composeTime = $this->timeFormat($event['composetime']);
        $originalArrivalTime = $this->timeFormat($event['originalarrivaltime']);

        $message->text = sprintf(
            "> %s (%s)\n%s\n",
            $fromLogin,
            $imDisplayName,
            $content
        );

        // is image
        if (str_contains($content, 'url_thumbnail')) {
            preg_match('#url_thumbnail="(.*)" type#', $content, $matches);
            $url = $matches[1];
            $imageName = $this->skype->getLocalImageName($url);
            $message->images = [$imageName];
            return $message;
        }

        if ($isFull) {
            $message->text = sprintf(
                "from: %s (%s), type: %s, arrival: %s, compose: %s\n%s\n",
                $fromLogin,
                $imDisplayName,
                $composeType,
                $originalArrivalTime,
                $composeTime,
                $content
            );
        }
        return $message;
    }

    protected function MessageUpdate(array $event): SkypeMessage
    {
        $message = new SkypeMessage();
        $content = $event['content'];
        $properties = json_encode($event['properties']);
        $message->text = "$content ($properties)\n";
        return $message;
    }

    protected function timeFormat(string $isoDate): string
    {
        return (new \DateTimeImmutable($isoDate))->format('h:i:s');
    }
}