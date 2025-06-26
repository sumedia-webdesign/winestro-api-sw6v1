<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Request\RequestInterface;
use Sumedia\Wbo\Service\Wbo\Response\ResponseInterface;

class Connector implements ConnectorInterface {

    protected Credentials $credentials;
    protected string $language = 'DE';
    protected Client $client;
    protected string $encoding = 'utf-8';
    protected WboConfig $config;
    protected LoggerInterface $logger;
    protected array $cache = [];

    public function __construct(
        Credentials $credentials,
        string $language,
        string $encoding,
        WboConfig $config,
        LoggerInterface $logger
    ) {
        $this->credentials = $credentials;
        $this->language = $language;
        $this->client = new Client();
        $this->encoding = $encoding;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(RequestInterface $request): ResponseInterface
    {
        $url = $request->getUrl();
        $urlPath = $url->getUrlPath();

        $data = array_merge_recursive(
            $url->getUrlParams(),
            $request->getUrlParams(),
            $this->credentials->getUrlParams(),
            $request->getData()
        );

        $responseClass = $request->getResponseClass();
        return new $responseClass($this->send($urlPath, $data));
    }

    protected function send(string $url, array $data): string
    {
        $logTransmittion = $this->config->get(WboConfig::DEBUG_TRANSMITTION_ENABLED);
        if ($logTransmittion) {
            $exportData = $data;
            if (isset($exportData['apiCODE'])) {
                $exportData['apiCODE'] = 'xxx';
            }
            if (isset($exportData['kto'])) {
                $exportData['kto'] = 'xxx';
            }
            if (isset($exportData['iban'])) {
                $exportData['iban'] = 'xxx';
            }

            $this->logger->debug('Connector request:' . var_export($exportData, true));
            $this->logger->debug('For WBO Contact: ' . $url . '?' . http_build_query($exportData, '', '&', PHP_QUERY_RFC3986));
        }

        $response = $this->client->post($url, [
            'query' => $data,
            'allow_redirects' => [
                'strict' => true,
                'protocols' => ['https']
            ]
        ]);
        $content = $response->getBody()->getContents();

        if ($logTransmittion) {
            $this->logger->debug('Connection response: ' . $content);
        }

        if (false === preg_match('/(<\?xml.*)$/uism', $content, $matches) || 0 == count($matches)) {
            throw new \RuntimeException('Invalid content return from connection, XML expected.');
        }
        $content = $matches[1];

        if ($logTransmittion) {
            $this->logger->debug('Connector filtered XML response: ' . $content);
        }

        if (is_bool($content)) {
            return '';
        }

        return $content;
    }
}
