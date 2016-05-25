<?php

namespace Pigeon;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Psr7\Request;

/**
 * Class Processor
 *
 * @package Pigeon
 */
class Processor
{
    protected $endpoint;


    /**
     * Processor constructor.
     *
     * @param $endpoint
     * @throws \Exception
     */
    public function __construct($endpoint)
    {
        if (null === $endpoint) {
            throw new \Exception(
                'Pigeon service: endpoint is null'
            );
        }

        $this->endpoint = $endpoint;
    }

    /**
     * @param $body
     *
     * @return mixed
     * @throws \Exception
     */
    public function providerBatch($body)
    {
        $client = new GuzzleClient();

        $request = new Request(
            'get',
            $this->getPath('/notification/batch'),
            ['Content-Type' => 'application/json'],
            $body
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents());
    }

    /**
     * @param GuzzleClient $client
     * @param Request      $request
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws \Exception
     */
    public function send(GuzzleClient $client, Request $request)
    {
        try {
            $response = $client->send($request);
            return $response->getBody();
        } catch (GuzzleClientException $e) {
            throw new \Exception(
                'Something bad happened with pigeon service', 0, $e
            );
        }
    }

    /**
     * @param $path
     *
     * @return string
     */
    protected function getPath($path)
    {
        return $this->endpoint . $path;
    }
}
