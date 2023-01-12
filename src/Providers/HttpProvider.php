<?php

namespace Llabbasmkhll\LaravelTron\Providers;

use GuzzleHttp\{Client, ClientInterface, Psr7\Request};
use IEXBase\TronAPI\Exception\{NotFoundException, TronException};
use IEXBase\TronAPI\Provider\HttpProviderInterface;
use IEXBase\TronAPI\Support\Utils;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\StreamInterface;

class HttpProvider implements HttpProviderInterface
{
    /**
     * HTTP Client Handler
     *
     * @var ClientInterface.
     */
    protected ClientInterface $httpClient;

    /**
     * Server or RPC URL
     *
     * @var string
     */
    protected string $host;

    /**
     * Waiting time
     *
     * @var int
     */
    protected int $timeout = 30000;

    /**
     * Get custom headers
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * Get the pages
     *
     * @var string
     */
    protected string $statusPage = '/';

    /**
     * Create an HttpProvider object
     *
     * @param  string  $host
     * @param  int  $timeout
     * @param  bool  $user
     * @param  bool  $password
     * @param  array  $headers
     * @param  string  $statusPage
     *
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function __construct(
        string $host,
        int $timeout = 30000,
        bool $user = false,
        bool $password = false,
        array $headers = [],
        string $statusPage = '/'
    ) {
        if ( ! Utils::isValidUrl($host)) {
            throw new TronException('Invalid URL provided to HttpProvider');
        }

        if (is_nan($timeout) || $timeout < 0) {
            throw new TronException('Invalid timeout duration provided');
        }

        if ( ! Utils::isArray($headers)) {
            throw new TronException('Invalid headers array provided');
        }

        $this->host       = $host;
        $this->timeout    = $timeout;
        $this->statusPage = $statusPage;
        $this->headers    = $headers;

        $this->httpClient = new Client([
            'base_uri' => $host,
            'timeout'  => $timeout,
            'auth'     => $user && [$user, $password],
        ]);
    }

    /**
     * Enter a new page
     *
     * @param  string  $page
     */
    public function setStatusPage(string $page = '/'): void
    {
        $this->statusPage = $page;
    }

    /**
     * Check connection
     *
     * @return bool
     * @throws TronException|\GuzzleHttp\Exception\GuzzleException
     */
    public function isConnected(): bool
    {
        $response = $this->request($this->statusPage);

        if (array_key_exists('blockID', $response)) {
            return true;
        } elseif (array_key_exists('status', $response)) {
            return true;
        }

        return false;
    }

    /**
     * We send requests to the server
     *
     * @param $url
     * @param  array  $payload
     * @param  string  $method
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    public function request($url, array $payload = [], string $method = 'GET'): array
    {
        $method = strtoupper($method);

        if ( ! in_array($method, ['GET', 'POST'])) {
            throw new TronException('The method is not defined');
        }

        $time = time();
        Cache::forget('tron_api_limit:'.($time - 1));
        if ( ! empty(config('tron.key')) && Cache::increment('tron_api_limit:'.$time) > 15) {
            throw new TronException('The api access limit reached');
        }

        $options = [
            'headers' => $this->headers,
        ];

        if ($method === 'GET') {
            $options['query'] = $payload;
        } elseif ($method === 'POST') {
            $options['body'] = json_encode($payload);
        }

        $request     = new Request($method, $url, $options['headers'], $options['body'] ?? null);
        $rawResponse = $this->httpClient->send($request, $options);

        return $this->decodeBody(
            $rawResponse->getBody(),
            $rawResponse->getStatusCode()
        );
    }

    /**
     * Convert the original answer to an array
     *
     * @param  StreamInterface  $stream
     * @param  int  $status
     *
     * @return array
     */
    protected function decodeBody(StreamInterface $stream, int $status): array
    {
        $decodedBody = json_decode($stream->getContents(), true);

        if ((string)$stream == 'OK') {
            $decodedBody = [
                'status' => 1,
            ];
        } elseif ($decodedBody == null or ! is_array($decodedBody)) {
            $decodedBody = [];
        }

        if ($status == 404) {
            throw new NotFoundException('Page not found');
        }

        return $decodedBody;
    }

    /**
     * Getting a host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Getting timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
