<?php

namespace Server\LSP;

use JsonSerializable;

class NotificationMessage extends Message implements JsonSerializable
{
    public readonly string $method;

    public readonly array|object|null $params;

    public function __construct(string $method, array|object|null $params)
    {
        $this->method = $method;
        $this->params = $params;
    }

    public function jsonSerialize()
    {
        return [
            'method' => $this->method,
            'params' => $this->params,
        ];
    }

    public function toJson(): string
    {
        $encoded = json_encode($this);

        return "Content-Length: " . strlen($encoded) . "\r\n\r\n" . $encoded;
    }
}
