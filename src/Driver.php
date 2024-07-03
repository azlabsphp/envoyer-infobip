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

    /** @var string */
    private $callback_url;

    /**
     * Creates new class instance
     * 
     * @param string $endpoint
     * @param null|string $apiKey
     * @param null|string $callback_url
     *
     * @return void
     */
    public function __construct(string $endpoint, string $apiKey = null, string $callback_url = null)
    {
        # code...
        $this->apiKey = $apiKey;
        $this->callback_url = $callback_url;
        $this->curl = new Curl(rtrim($endpoint, '/'));
    }

    /**
     * Creates new class instance
     * 
     * @param string $endpoint 
     * @param string|null $apiKey 
     * @param null|string $callback_url
     * @return static 
     */
    public static function new(string $endpoint, string $apiKey = null, string $callback_url)
    {
        return new static($endpoint, $apiKey, $callback_url);
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

    /**
     * Copy current instance and update the it `callback_url` property
     * 
     * @param string $url 
     * @return static 
     */
    public function withCallbackUrl(string $url)
    {
        $self = clone $this;
        $self->callback_url = $url;

        return $self;
    }

    public function sendRequest(NotificationInterface $instance): NotificationResult
    {
        $response = $this->sendHTTPRequest($this->curl, '/sms/2/text/advanced', 'POST', [
            "messages" => [
                [
                    "destinations" => [["to" => $instance->getReceiver()->__toString()]],
                    "from" => $instance->getSender()->__toString(),
                    "text" => strval($instance->getContent()),
                    "notifyUrl" => $this->callback_url ?? null,
                    "notifyContentType" => "application/json",
                    "webhooks" => $this->callback_url ? ["delivery" => ["url" => $this->callback_url]] : [],
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
