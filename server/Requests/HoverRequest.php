<?php

namespace Server\Requests;

use Phpactor\LanguageServerProtocol\Hover;
use Phpactor\LanguageServerProtocol\HoverParams;
use Phpactor\LanguageServerProtocol\MarkupContent;
use Phpactor\LanguageServerProtocol\MarkupKind;
use Server\Dictionary\Dictionary;
use Server\Document\Document;
use Server\Document\Nodes\ExclamationMark;
use Server\Document\Nodes\Line;
use Server\Document\Nodes\Period;
use Server\Document\Nodes\QuestionMark;
use Server\Document\Nodes\Space;
use Server\Document\Nodes\Word;
use Server\LSP\RequestMessage;
use Server\Workspace\Documents;

class HoverRequest extends Request
{
    public function handle(RequestMessage $request): mixed
    {
        $params = HoverParams::fromArray($request->params);
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

        $result = '';

        if ($node instanceof Word)
        {
            $key = strtolower($node->value);

            if (isset(Dictionary::$words[$key]))
            {
                $result = Dictionary::$words[$key];
            }
            else
            {
                $matches = Dictionary::search($node->value, 3);

                if (!empty($matches))
                {
                    $result = 'Did you mean: ' . implode(', ', array_column($matches, 'word')) . '?';
                }
                else
                {
                    $result = 'Word not found.';
                }
            }
        }
        elseif (in_array(get_class($node), [Period::class, QuestionMark::class, ExclamationMark::class]))
        {
            $result = 'Punctuation mark.';
        }
        elseif ($node instanceof Space)
        {
            $result = 'Space.';
        }
        else
        {
            $result = 'Unknown.';
        }

        return new Hover(new MarkupContent(MarkupKind::MARKDOWN, $result), $range);
    }
}
