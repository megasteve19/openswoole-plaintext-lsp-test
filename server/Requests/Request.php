<?php

namespace Server\Requests;

use OpenSwoole\Server;
use Server\LSP\NotificationMessage;
use Server\LSP\RequestMessage;

abstract class Request
{
    protected readonly Server $server;

    protected readonly int $socket;

    public function __construct(Server $server, int $socket)
    {
        $this->server = $server;
        $this->socket = $socket;
    }

    protected function notify(string $method, array|object $params): void
    {
        $notification = new NotificationMessage(
            method: $method,
            params: $params
        );

        $this->server->send($this->socket, $notification->toJson());
    }

    abstract public function handle(RequestMessage $request): mixed;
}
