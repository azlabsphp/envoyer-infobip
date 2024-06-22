<?php

namespace Drewlabs\Envoyer\Drivers\Infobip;

use Drewlabs\Envoyer\Contracts\ClientInterface;
use Drewlabs\Envoyer\Contracts\NotificationInterface;
use Drewlabs\Envoyer\Contracts\NotificationResult;
use Drewlabs\Curl\Client as Curl;
use Drewlabs\Envoyer\Drivers\Infobip\Exceptions\RequestException;

class Driver implements ClientInterface
{
    use SendsHTTPRequest;

    /** @var Curl */
    private $curl;

    /** @var string */
    private $apiKey;

    /**
     * Creates new class instance
     * 
     * @param string $endpoint
     * @param string $apiKey
     *
     * @return void
     */
    public function __construct(string $endpoint, string $apiKey = null)
    {
        # code...
        $this->apiKey = $apiKey;
        $this->curl = new Curl(rtrim($endpoint, '/'));
    }

    /**
     * Creates new class instance
     * 
     * @param string $endpoint 
     * @param string|null $apiKey 
     * @return static 
     */
    public static function new(string $endpoint, string $apiKey = null)
    {
        return new static($endpoint, $apiKey);
    }

    /**
     * Updates the driver with apikey property
     * 
     * @param string $apiKey
     * 
     * @return static 
     */
    public function withAPIKey(string $apiKey)
    {
        $self = clone $this;
        $self->apiKey = $apiKey;

        return $self;
    }

    public function sendRequest(NotificationInterface $instance): NotificationResult
    {
        $response = $this->sendHTTPRequest($this->curl, '/sms/2/text/advanced', 'POST', [
            "messages" => [
                [
                    "destinations" => [["to" => $instance->getReceiver()->__toString()]],
                    "from" => $instance->getSender()->__toString(),
                    "text" => strval($instance->getContent())
                ]
            ]
        ], [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('App %s', $this->apiKey)
        ]);
        if (($statusCode  = $response->getStatusCode()) && (200 > $statusCode || 204 < $statusCode)) {
            throw new RequestException(sprintf("/POST /sms/2/text/advanced fails with status %d -  %s", $statusCode, $response->getBody()));
        }
		return Result::fromJson($response->json()->getBody());
    }
}
