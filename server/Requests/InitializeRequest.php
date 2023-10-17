<?php

namespace Server\Requests;

use Phpactor\LanguageServerProtocol\CompletionOptions;
use Phpactor\LanguageServerProtocol\InitializeParams;
use Phpactor\LanguageServerProtocol\InitializeResult;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\TextDocumentSyncKind;
use Server\LSP\RequestMessage;

class InitializeRequest extends Request
{
    public function handle(RequestMessage $request): mixed
    {
        // $params = InitializeParams::fromArray($request->params);

        return new InitializeResult(
            capabilities: new ServerCapabilities(
                positionEncoding: 'utf-16',
                textDocumentSync: TextDocumentSyncKind::FULL,
                completionProvider: new CompletionOptions(),
                hoverProvider: true,
                signatureHelpProvider: null,
                diagnosticProvider: [],
            )
        );
    }
}
