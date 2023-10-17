<?php

namespace Server\Notifications;

use Phpactor\LanguageServerProtocol\DidChangeTextDocumentParams;
use Phpactor\LanguageServerProtocol\PublishDiagnosticsParams;
use Server\Document\Document;
use Server\LSP\NotificationMessage;
use Server\Workspace\Documents;

class TextDocumentDidChangeNotification extends Notification
{
    public function handle(NotificationMessage $notification): void
    {
        $params = DidChangeTextDocumentParams::fromArray($notification->params);

        Documents::sync(
            uri: $params->textDocument->uri,
            content: $params->contentChanges[array_key_first($params->contentChanges)]['text'],
            version: $params->textDocument->version
        );

        $document = Documents::get($params->textDocument->uri);

        if ($document['version'] >= $params->textDocument->version)
        {
            $this->notify('textDocument/publishDiagnostics', new PublishDiagnosticsParams(
                uri: $params->textDocument->uri,
                diagnostics: Document::parse($document['content'])->getDiagnostics(),
            ));
        }
    }
}
