<?php

namespace Server\Requests;

use Server\Document\Document;
use Server\LSP\RequestMessage;

class DiagnosticRequest extends Request
{
    public function handle(RequestMessage $request): mixed
    {
        $document = file_get_contents($request->params['textDocument']['uri']);

        return [
            'kind' => 'full',
            'items' => Document::parse($document)->getDiagnostics(),
        ];
    }
}
