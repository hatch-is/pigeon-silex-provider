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
     *
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
            'post',
            $this->getPath('/notification/batch'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function providerPubsub($body)
    {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/pubsub/channel'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function registerEmail($recipientId, $address)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'post',
            $this->getPath(
                sprintf(
                    '/recipients/%s/profiles/email',
                    $recipientId
                )
            ),
            ['Content-Type' => 'application/json'],
            json_encode(['address' => $address])
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function sendEmailToAddress($body)
    {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/email/address'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function sendEmailToRecipient($body)
    {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/email/recipient'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function sendBatchEmails($body)
    {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/email/recipient/batch'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function updateRecipient($recipientId, $firstName, $lastName)
    {

        $client = new GuzzleClient();

        $request = new Request(
            'post',

            $this->getPath('/recipients'),
            ['Content-Type' => 'application/json'],
            json_encode(
                [
                    'id'        => $recipientId,
                    'firstName' => $firstName,
                    'lastName'  => $lastName
                ]
            )
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function unRegisterEmail($recipientId)
    {
        $client = new GuzzleClient();
        $request = new Request(
            'delete',
            $this->getPath(
                sprintf(
                    '/recipients/%s/profiles/email',
                    $recipientId
                )
            ),
            ['Content-Type' => 'application/json']
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function registerDevice($recipientId, $deviceId, $deviceToken,
        $platform, $app = null
    ) {
        $body = [
            'deviceId' => $deviceId,
            'token'    => $deviceToken,
            'platform' => $platform
        ];
        if (!empty($app)) {
            $body['app'] = $app;
        }

        $client = new GuzzleClient();
        $request = new Request(
            'post',
            $this->getPath(
                sprintf(
                    '/recipients/%s/profiles/push/register',
                    $recipientId
                )
            ),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
    }

    public function unRegisterDevice($recipientId, $deviceId, $app = null)
    {
        $body = [
            'deviceId' => $deviceId
        ];
        if(!empty($app)) {
            $body['app'] = $app;
        }
        $client = new GuzzleClient();
        $request = new Request(
            'post',
            $this->getPath(
                sprintf(
                    '/recipients/%s/profiles/push/unregister',
                    $recipientId
                )
            ),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents(), true);
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
            $message = $this->formatErrorMessage($e);
            throw new \Exception(json_encode($message), 0, $e);
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

    protected function formatErrorMessage($httpException)
    {
        $message = [
            'message'  => 'Something bad happened with pingeon service',
            'request'  => [
                'headers' => $httpException->getRequest()->getHeaders(),
                'body'    => $httpException->getRequest()->getBody()
            ],
            'response' => [
                'headers' => $httpException->getResponse()->getHeaders(),
                'body'    => $httpException->getResponse()->getBody()
                    ->getContents(),
                'status'  => $httpException->getResponse()->getStatusCode()
            ]
        ];

        return $message;
    }
}
