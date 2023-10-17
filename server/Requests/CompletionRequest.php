<?php

namespace Server\Requests;

use Phpactor\LanguageServerProtocol\CompletionItem;
use Phpactor\LanguageServerProtocol\CompletionItemKind;
use Phpactor\LanguageServerProtocol\CompletionList;
use Phpactor\LanguageServerProtocol\CompletionParams;
use Server\Dictionary\Dictionary;
use Server\Document\Document;
use Server\LSP\RequestMessage;
use Server\Workspace\Documents;

class CompletionRequest extends Request
{
    public function handle(RequestMessage $request): mixed
    {
        $params = CompletionParams::fromArray($request->params);
        $node = Document::parse(Documents::getContent($params->textDocument->uri));

        while (count($node->children) >= 1)
        {
            foreach ($node->children as $child)
            {
                $range = $child->getRange();

                // First match the hovered line.
                if ($range->start->line !== $params->position->line || $range->end->line !== $params->position->line)
                {
                    if ($range->start->line <= $params->position->line && $range->end->line >= $params->position->line)
                    {
                        $node = $child;

                        break;
                    }

                    continue;
                }

                // Then match the character.
                if ($range->start->character <= $params->position->character && $range->end->character >= $params->position->character)
                {
                    $node = $child;

                    break;
                }
            }
        }

        $matches = array_map(
            // fn (array $word) => new CompletionItem(
            //     label: $word['word'],
            //     // documentation: $word['definition'],
            //     kind: CompletionItemKind::TEXT,
            // ),
            fn (array $word) => [
                'label' => $word['word'],
                'kind' => CompletionItemKind::KEYWORD,
            ],
            Dictionary::search($node->value, 5)
        );

        if (empty($matches))
        {
            return null;
        }

        return new CompletionList(
            isIncomplete: false,
            items: $matches
        );
    }
}
