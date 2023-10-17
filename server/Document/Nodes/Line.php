<?php

namespace Server\Document\Nodes;

use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Server\Document\Node;

class Line extends Node
{
    /**
     * Returns child nodes.
     *
     * @return array<Node>
     */
    public function getChildNodes(): array
    {
        $children = [];

        /**
         * @var Sentence
         */
        $current = null;

        foreach ([...str_split($this->value), null] as $index => $char)
        {
            $index = $this->start + $index;

            if (in_array($char, ['.', '!', '?']))
            {
                $children[] = match ($char)
                {
                    '.' => new Period($this, $char, $index, $index + 1),
                    '!' => new ExclamationMark($this, $char, $index, $index + 1),
                    '?' => new QuestionMark($this, $char, $index, $index + 1),
                };
            }

            if (in_array($char, ['.', '!', '?']) || is_null($char))
            {
                if (!empty($current))
                {
                    $children[] = new Sentence($this, $current->value, $current->start, $index);
                }

                $current = null;

                continue;
            }

            $current = new Sentence($this, $current?->value . $char, $current?->start ?? $index, $index);
        }

        usort($children, fn (Node $a, Node $b) => $a->start - $b->start);

        return $children;
    }

    public function getDiagnostics(): array
    {
        $diagnostics = [];
        $punctuations = [];

        foreach ($this->children as $index => $child)
        {
            $isPunctuation = in_array(get_class($child), [Period::class, ExclamationMark::class, QuestionMark::class]);

            if (array_key_first($this->children) === $index && $isPunctuation)
            {
                $diagnostics[] = new Diagnostic(
                    range: $child->getRange(),
                    message: 'Sentence cannot start with punctuation.',
                );
            }

            if ($isPunctuation)
            {
                $punctuations[] = $child;
            }

            if ($child instanceof Sentence)
            {
                $diagnostics = array_merge($diagnostics, $child->getDiagnostics());
            }

            if (array_key_last($this->children) === $index && $child instanceof Sentence)
            {
                $diagnostics[] = new Diagnostic(
                    range: $child->getRange(),
                    message: 'Sentence ends without punctuation.',
                    severity: DiagnosticSeverity::WARNING
                );
            }
        }

        return $diagnostics;
    }
}
