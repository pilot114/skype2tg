<?php

namespace SkypeToTelegram;

use GuzzleHttp\Client;

class Skype
{
    protected string $entryPoint;
    protected Client $client;
    protected array $options = [];

    public function __construct(
        protected string $authHeader,
        protected string $endpointHeader,
        protected string $cookieForApi,
    ) {
        preg_match('#endpointId=(.*)$#', $endpointHeader, $matches);
        $this->entryPoint = $matches[1];
        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36';

        $this->options['headers'] = [
            'Authentication'    => $authHeader,
            'RegistrationToken' => $endpointHeader,
            'User-Agent'        => $userAgent,
        ];
        $this->client = new Client([
            'base_uri' => 'https://azeus1-client-s.gateway.messenger.live.com',
            'timeout'  => 1.0,
            // 'debug' => true,
        ]);
    }

    protected function callPost(string $url): array
    {
        $response = $this->client->post($url, $this->options);
        $data = $response->getBody()->getContents();
        return json_decode($data, true)['eventMessages'] ?? [];
    }

    public function getLocalImageName(string $url): string
    {
        $this->options['headers']['Cookie'] = $this->cookieForApi;
        $response = $this->client->get($url, $this->options);
        $data = $response->getBody()->getContents();
        $fileId = uniqid();
        $fileName = "./cache/$fileId.jpeg";
        file_put_contents($fileName, $data);
        return $fileName;
    }

    # т.к. данные получаем постранично, может быть получено >= $count событий
    public function listenEvents(int $count = 100): \Generator
    {
        $this->options['timeout'] = 90.0;
        $id = 1000;

        $eventIndex = 0;
        while($eventIndex < $count) {
            $url = "/v1/users/ME/endpoints/$this->entryPoint/subscriptions/0/poll?ackId=$id";
            $events = $this->callPost($url);
            foreach ($events as $event) {
                $id = $event['id'];
                yield $event;
            }
            $eventIndex += count($events);
        }
    }

    public function formatEvent(array $event): ?SkypeMessage
    {
        return (new SkypePrinter($this))->formatEvent($event);
    }
}