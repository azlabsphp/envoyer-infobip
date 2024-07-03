<?php

namespace Drewlabs\Envoyer\Drivers\Infobip;

use Drewlabs\Envoyer\Contracts\NotificationResult;

class Result implements NotificationResult
{

    /** @var string */
    private $id;

    /** @var string */
    private $date;

    /** @var bool */
    private $ok;

    /** @const string[] */
    const DELIVERED_STATUS = ['DELIVERED_TO_OPERATOR', 'DELIVERED_TO_HANDSET'];


    /**
     * Message class constructor 
     * 
     * @param string|null $id 
     * @param string|null $date 
     * @param bool $ok 
     */
    public function __construct(string $id = null, string $date = null, bool $ok = true)
    {
        $this->id = $id;
        $this->date = $date;
        $this->ok = $ok;
    }

    public static function fromJson(array $attributes)
    { 
        return new static($attributes['messageId'] ?? null, isset($attributes['sentAt']) ? date('Y-m-d H:i:s', strtotime($attributes['sentAt'])) : date('Y-m-d H:i:s'), in_array(strtoupper($attributes['status']['name'] ?? ''), self::DELIVERED_STATUS));
    }

    public function date()
    {
        return $this->date;
    }

    public function id()
    {
        return $this->id;
    }

    public function isOk()
    {
        return $this->ok;
    }
}
