<?php

namespace Server\Document;

use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Server\Document\Nodes\Linebreak;
use Server\Document\Nodes\Paragraph;

class Document extends Node
{
    /**
     * Parses given document.
     *
     * @param string $value Document to be parsed.
     *
     * @return self
     */
    public static function parse(string $value): self
    {
        return new self(null, $value, 0, strlen($value));
    }

    /**
     * Returns child nodes.
     *
     * @return array<Node>
     */
    public function getChildNodes(): array
    {
        $children = [];
        $lineBreakCount = 0;

        /**
         * @var Paragraph
         */
        $current = null;

        foreach ([...str_split($this->value), null] as $index => $char)
        {
            if (($char === "\n" && $lineBreakCount > 1 && !empty($current)) || is_null($char))
            {
                if (!empty($current))
                {
                    $children[] = new Paragraph($this, $current->value, $current->start, $index);
                }

                $current = null;

                continue;
            }

            $current = new Paragraph($this, $current?->value . $char, $current?->start ?? $index, $index);
        }

        usort($children, fn (Node $a, Node $b) => $a->start - $b->start);

        return $children;
    }

    public function getDiagnostics(): array
    {
        $diagnostics = [];

        foreach ($this->children as $index => $child)
        {
            if ($index === array_key_first($this->children) && $child instanceof Linebreak)
            {
                $diagnostics[] = new Diagnostic(
                    range: $this->getRange(),
                    message: 'Document starts with linebreak.',
                    severity: DiagnosticSeverity::WARNING
                );
            }

            if ($child instanceof Paragraph)
            {
                $diagnostics = array_merge($diagnostics, $child->getDiagnostics());
            }
        }

        return $diagnostics;
    }
}
