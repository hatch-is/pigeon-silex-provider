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
        return json_decode($response->getContents());
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
        return json_decode($response->getContents());
    }

    public function sendEmailToRecipient($recipientId, $templateName,
        $templateVars
    ) {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/email/recipient'),
            ['Content-Type' => 'application/json'],
            json_encode(
                [
                    'recipientID' => $recipientId,
                    'template'    => $templateName,
                    'vars'        => $templateVars
                ]
            )
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents());
    }

    public function sendBatchEmails($body) {
        $client = new GuzzleClient();

        $request = new Request(
            'post',
            $this->getPath('/provider/email/recipient/batch'),
            ['Content-Type' => 'application/json'],
            json_encode($body)
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents());
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
        return json_decode($response->getContents());
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
        return json_decode($response->getContents());
    }

    public function registerDevice($recipientId, $deviceId, $deviceToken,
        $platform
    ) {
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
            json_encode(
                [
                    'deviceId' => $deviceId,
                    'token'    => $deviceToken,
                    'platform' => $platform
                ]
            )
        );

        $response = $this->send($client, $request);
        return json_decode($response->getContents());
    }

    public function unRegisterDevice($recipientId, $deviceId)
    {
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
            json_encode(['deviceId' => $deviceId])
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
