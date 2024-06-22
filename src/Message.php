<?php

namespace Drewlabs\Envoyer\Drivers\Infobip;

use Drewlabs\Envoyer\Contracts\NotificationResult;

class Message implements NotificationResult
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
        $message = static::getFirstMessage($attributes);
        return new static($message['messageId'] ?? null, date('Y-m-d H:i:s'), in_array(strtoupper($message['status']['name'] ?? ''), self::DELIVERED_STATUS));
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

    /**
     * Returns the first message in the list of messages if messages not empty
     * 
     * @param array $values 
     * @return array 
     */
    private static function getFirstMessage(array $values)
    {
        $messages = array_values($values['messages'] ?? []);
        return $messages[0] ?? [];
    }
}
