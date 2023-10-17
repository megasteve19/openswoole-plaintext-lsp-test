<?php

require __DIR__ . '/vendor/autoload.php';

use OpenSwoole\Server;
use Server\Dictionary\Dictionary;
use Server\LSP\NotificationMessage;
use Server\LSP\RequestMessage;
use Server\LSP\ResponseError;
use Server\LSP\ResponseMessage;
use Server\Notifications\TextDocumentDidChangeNotification;
use Server\Notifications\TextDocumentDidOpenNotification;
use Server\Notifications\TextDocumentDidSaveNotification;
use Server\Requests\CompletionRequest;
use Server\Requests\DiagnosticRequest;
use Server\Requests\HoverRequest;
use Server\Requests\InitializeRequest;
use Server\Workspace\Documents;

$server = new Server('127.0.0.1', 3000);
$dictionary = [];

Dictionary::init();
Documents::init();

$server->on('connect', function(Server $server, int $fd)
{
    // ...
});

$server->on('receive', function(Server $server, int $fd, int $from_id, string $data)
{
    $data = json_decode($data, true);

    if (!$data)
    {
        return;
    }

    if (!isset($data['id']))
    {
        echo 'Handling notification "' . $data['method'] . PHP_EOL;

        $notification = new NotificationMessage(
            method: $data['method'],
            params: $data['params']
        );

        $handler = match ($notification->method)
        {
            'textDocument/didOpen' => new TextDocumentDidOpenNotification($server, $fd),
            'textDocument/didChange' => new TextDocumentDidChangeNotification($server, $fd),
            'textDocument/didSave' => new TextDocumentDidSaveNotification($server, $fd),
            default => null
        };

        if ($handler)
        {
            $handler->handle($notification);
        }

        return;
    }

    echo 'Handling request: "' . $data['method'] . '".' . PHP_EOL;

    $start = hrtime(true);

    $request = new RequestMessage(
        id: $data['id'],
        socket: $fd,
        method: $data['method'],
        params: $data['params']
    );

    $handler = match ($request->method)
    {
        'initialize' => new InitializeRequest($server, $fd),
        'textDocument/diagnostic' => new DiagnosticRequest($server, $fd),
        'textDocument/hover' => new HoverRequest($server, $fd),
        'textDocument/completion' => new CompletionRequest($server, $fd),
        default => null
    };

    if ($handler === null)
    {
        dump($data);
    }

    $response = new ResponseMessage(
        id: $request->id,
        result: $handler?->handle($request),
        error: is_null($handler)
            ? new ResponseError(
                code: -32601,
                message: 'Not implemented.',
            ) : null,
    );

    dump($response->toJson());

    $server->send($fd, $response->toJson());

    echo 'Handled: "' . $data['method'] . '", in ' . (hrtime(true) - $start) / 1000000 . 'ms' . PHP_EOL . PHP_EOL;
});

$server->on('close', function(Server $server, string $fd)
{
    // ...
});

$server->start();
