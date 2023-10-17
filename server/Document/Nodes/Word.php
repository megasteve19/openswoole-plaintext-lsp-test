<?php

namespace Server\Document\Nodes;

use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Server\Dictionary\Dictionary;
use Server\Document\Node;

class Word extends Node
{
    /**
     * Returns child nodes.
     *
     * @return array<Node>
     */
    public function getChildNodes(): array
    {
        return [];
    }

    public function getDiagnostics(): array
    {
        $diagnostics = [];

        if (!isset(Dictionary::$words[strtolower($this->value)]))
        {
            $matches = Dictionary::search($this->value, 3);

            if (!empty($matches))
            {
                $message = 'Did you mean: ' . implode(', ', array_column($matches, 'word')) . '?';
            }
            else
            {
                $message = 'Word not found.';
            }

            $diagnostics[] = new Diagnostic(
                range: $this->getRange(),
                message: $message,
                severity: DiagnosticSeverity::INFORMATION
            );
        }

        return $diagnostics;
    }
}
