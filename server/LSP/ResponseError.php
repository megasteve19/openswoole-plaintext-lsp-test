<?php

namespace Server\LSP;

class ResponseError
{
    public readonly int $code;

    public readonly string $message;

    public function __construct(int $code, string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }
}
