<?php

namespace Server\LSP;

use JsonSerializable;

class ResponseMessage extends Message implements JsonSerializable
{
    public readonly int|string $id;

    public readonly string|int|float|bool|array|object|null $result;

    public readonly ResponseError|null $error;

    public function __construct(int|string $id, string|int|float|bool|array|object|null $result, ResponseError|null $error = null)
    {
        $this->id = $id;
        $this->result = $result;
        $this->error = $error;
    }

    public function jsonSerialize()
    {
        if ($this->error)
        {
            return [
                'id' => $this->id,
                'error' => $this->error,
            ];
        }

        return [
            'id' => $this->id,
            'result' => $this->result,
        ];
    }

    public function toJson()
    {
        $encoded = json_encode($this);

        return "Content-Length: " . strlen($encoded) . "\r\n\r\n" . $encoded;
    }
}
