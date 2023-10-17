<?php

namespace Server\Document\Nodes;

use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Server\Document\Node;

class Sentence extends Node
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
         * @var Word
         */
        $current = null;

        foreach ([...str_split($this->value), null] as $index => $char)
        {
            $index = $this->start + $index;

            if ($char === ' ')
            {
                $children[] = new Space($this, $char, $index, $index + 1);
            }

            if (!ctype_alnum($char) || is_null($char))
            {
                if (!empty($current))
                {
                    $children[] = new Word($this, $current->value, $current->start, $index);
                }

                $current = null;

                continue;
            }

            $current = new Word($this, $current?->value . $char, $current?->start ?? $index, $index);
        }

        usort($children, fn (Node $a, Node $b) => $a->start - $b->start);

        return $children;
    }

    public function getDiagnostics(): array
    {
        $diagnostics = [];
        $spaces = [];
        $wordCount = 0;

        foreach ($this->children as $index => $child)
        {
            if ($child instanceof Space)
            {
                $spaces[] = $child;
            }

            if ($child instanceof Word)
            {
                $wordCount++;
            }

            if ($child instanceof Word)
            {
                if (count($spaces) > 1)
                {
                    $diagnostics[] = new Diagnostic(
                        range: new Range(
                            start: new Position(
                                line: min(array_map(fn (Node $space) => $space->getRange()->start->line, $spaces)),
                                character: min(array_map(fn (Node $space) => $space->getRange()->start->character, $spaces))
                            ),
                            end: new Position(
                                line: max(array_map(fn (Node $space) => $space->getRange()->end->line, $spaces)),
                                character: max(array_map(fn (Node $space) => $space->getRange()->end->character, $spaces))
                            )
                        ),
                        message: 'Multiple spaces between words.',
                        severity: DiagnosticSeverity::WARNING,
                    );
                }

                $spaces = [];

                if ($wordCount === 1 && ucfirst($child->value) !== $child->value)
                {
                    $diagnostics[] = new Diagnostic(
                        range: $child->getRange(),
                        message: 'Sentence must start with a capital letter.',
                        severity: DiagnosticSeverity::ERROR,
                    );

                    continue;
                }

                if (ucfirst(strtolower($child->value)) !== $child->value && !(ctype_upper($child->value) || ctype_lower($child->value)))
                {
                    $diagnostics[] = new Diagnostic(
                        range: $child->getRange(),
                        message: 'Mixed case is not allowed.',
                        severity: DiagnosticSeverity::ERROR,
                    );
                }

                $diagnostics = array_merge($diagnostics, $child->getDiagnostics());
            }
        }

        return $diagnostics;
    }
}
