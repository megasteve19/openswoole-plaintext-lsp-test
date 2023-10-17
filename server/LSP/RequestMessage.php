<?php

namespace Server\LSP;

class RequestMessage extends Message
{
    public readonly int|string $id;

    public readonly int $socket;

    public readonly string $method;

    public readonly array|object $params;

    public function __construct(int|string $id, int $socket, string $method, array|object $params)
    {
        $this->id = $id;
        $this->socket = $socket;
        $this->method = $method;
        $this->params = $params;
    }
}
