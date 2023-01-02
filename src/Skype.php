<?php

namespace SkypeToTelegram;

use GuzzleHttp\Client;

class Skype
{
    protected string $entryPoint;
    protected int $subscriptionId = 0;
    protected Client $client;
    protected array $options = [];
    protected bool $isDebug = false;

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
//             'debug' => true,
        ]);

//        $this->auth();
    }

    public function setDebug(bool $isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function getLocalImageName(string $url): string
    {
        $this->options['headers']['Cookie'] = $this->cookieForApi;
        $response = $this->client->get($url, $this->options);
        $data = $response->getBody()->getContents();
        $file = tmpfile();
        fwrite($file, $data);
        return stream_get_meta_data($file)['uri'];
    }

    # т.к. данные получаем постранично, может быть получено >= $count событий
    public function listenEvents(int $count = 100): \Generator
    {
        $this->options['timeout'] = 90.0;
        $id = 1000;

        $eventIndex = 0;
        while($eventIndex < $count) {
            $url = "/v1/users/ME/endpoints/$this->entryPoint/subscriptions/$this->subscriptionId/poll?ackId=$id";
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
        return (new SkypePrinter($this))->formatEvent($event, $this->isDebug);
    }

    protected function callPost(string $url): array
    {
        $response = $this->client->post($url, $this->options);
        $data = $response->getBody()->getContents();
        return json_decode($data, true)['eventMessages'] ?? [];
    }

    protected function auth(): bool
    {
        $skypeToken = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjEwNiIsIng1dCI6Im9QMWFxQnlfR3hZU3pSaXhuQ25zdE5PU2p2cyIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE2NzI2OTUxNzgsImV4cCI6MTY3Mjc4MTU3Nywic2t5cGVpZCI6InNldmVyYXNzZSIsInNjcCI6OTU4LCJjc2kiOiIxNjcyNjk1MTc3IiwiY2lkIjoiM2I0MjAzODFmYWQ4Nzc5MSIsImFhdCI6MTY3MjY3NjY3NX0.LJaXQzdN0wjPSFlKin_skDxPfsqa4MlW3y4tacMdfb3C2_F7MfYgBlgwAdZ-6LNoRN-CvUrP6HeOMNapWk9DiFT7uVC9TqAN5BVY29wAItar6HxYRna_ZZYndQ-xnE3i3Fwrlw3ZVFKZ3bjZMRrshLQq_B2jGS9YSYAL9HXYwnRB1TCwNF6nt2FQuLpbirAOCs7_WYIA4dBuNPEP6RzW2cJvAIjMyrFXjVlnyndGrwaXb3xpmiTffC2Hr-trG30uuGIbftq6cgdI-OcNUVIDlSetEEBgoNgtWJ2XBLrUk9060F0peIXI56dvmYg1XH2UBcR2wIkSuZMRkFv5ZRYnfg';

        // 1 getCookies
        $url = 'https://api.asm.skype.com/v1/skypetokenauth';
        $options = [
            'headers' => [
                'Authorization' => "skype_token $skypeToken",
            ],
            'form_params' => [
                'skypetoken' => $skypeToken,
            ],
        ];
        $response = $this->client->post($url, $options);
        $this->cookieForApi = $response->getHeader('Set-Cookie')[0] ?? '';

        // 2 ?
//        $url = 'https://edge.skype.com/trap/tokens';
//        $options = [
//            'headers' => [
//                'x-skypetoken' => $skypeToken,
//            ],
//        ];
//        $response = $this->client->get($url, $options);
//        $tokens = $response->getBody()->getContents();
//        dump($tokens);

        // 3 getRegToken
        $url = 'https://client-s.gateway.messenger.live.com/v1/users/ME/properties';
        $options = [
            'headers' => [
                'Authentication' => "skypetoken=$skypeToken",
            ],
        ];
        $response = $this->client->get($url, $options);
        $regTokenString = $response->getHeader('Set-RegistrationToken')[0];
//        preg_match('#registrationToken=(.*);#', $regTokenString, $matches);
//        $regToken = $matches[1];
        $this->endpointHeader = $regTokenString;

        // 4 getEndpoint
        $url = 'https://client-s.gateway.messenger.live.com/v1/users/ME/endpoints';
        $options = [
            'headers' => [
                'Authentication' => "skypetoken=$skypeToken",
                'RegistrationToken' => $regTokenString,
            ],
            'json' => [
                'endpointFeatures' => 'Agent,Presence2015,MessageProperties,CustomUserProperties,Casts,ModernBots,AutoIdleForWebApi,secureThreads,notificationStream,InviteFree,SupportsReadReceipts',
            ],
//            'allow_redirects' => false,
        ];
        $response = $this->client->post($url, $options);
        $endpoints = json_decode($response->getBody()->getContents(), true);
        $endpoint = array_filter($endpoints, fn($x) => $x['isActive'] === true)[0] ?? null;

        $endpointId = $endpoint['id'];
        $subscriptionId = $endpoint['subscriptions'][0]['id'] ?? 0;

        if (!$endpointId) {
            dump('fail');
            die();
        }

        $this->authHeader = "skypetoken=$skypeToken";
        $this->endpointHeader .= sprintf('; endpointId={%s}', $endpointId);
        $this->entryPoint = sprintf("{%s}", $endpointId);
        $this->subscriptionId = $subscriptionId;

        $this->options['headers'] = [
            'Authentication' => "skypetoken=$skypeToken",
            'RegistrationToken' => $this->endpointHeader,
        ];
        $this->options['timeout'] = 90.0;

        foreach ($this->listenEvents(1000) as $event) {
            dump($event);
        }

        return true;
    }
}