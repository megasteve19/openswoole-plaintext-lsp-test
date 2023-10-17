<?php

namespace Server\Notifications;

use Phpactor\LanguageServerProtocol\DidOpenTextDocumentParams;
use Server\LSP\NotificationMessage;
use Server\Workspace\Documents;

class TextDocumentDidOpenNotification extends Notification
{
    public function handle(NotificationMessage $notification): void
    {
        $params = DidOpenTextDocumentParams::fromArray($notification->params);

        Documents::sync(
            uri: $params->textDocument->uri,
            content: file_get_contents($params->textDocument->uri),
            version: $params->textDocument->version
        );
    }
}
