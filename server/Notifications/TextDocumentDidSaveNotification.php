<?php

namespace Server\Notifications;

use Phpactor\LanguageServerProtocol\DidSaveTextDocumentParams;
use Phpactor\LanguageServerProtocol\PublishDiagnosticsParams;
use Server\Document\Document;
use Server\LSP\NotificationMessage;
use Server\Workspace\Documents;

class TextDocumentDidSaveNotification extends Notification
{
    public function handle(NotificationMessage $notification): void
    {
        $params = DidSaveTextDocumentParams::fromArray($notification->params);

        Documents::sync(
            uri: $params->textDocument->uri,
            content: file_get_contents($params->textDocument->uri),
            version: null
        );

        $this->notify('textDocument/publishDiagnostics', new PublishDiagnosticsParams(
            uri: $params->textDocument->uri,
            diagnostics: Document::parse(Documents::getContent($params->textDocument->uri))->getDiagnostics(),
        ));
    }
}
