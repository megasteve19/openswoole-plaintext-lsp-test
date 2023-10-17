<?php

namespace Server\Document;

use JsonSerializable;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;

/**
 * @property array<int, Node> $children
 */
abstract class Node implements JsonSerializable
{
    /**
     * Parent node.
     *
     * @var Node|null
     */
    public readonly ?Node $parent;

    /**
     * Underlying value.
     *
     * @var string
     */
    public readonly string $value;

    /**
     * Start position relative to parent.
     *
     * @var integer
     */
    public readonly int $start;

    /**
     * End position relative to parent.
     *
     * @var integer
     */
    public readonly int $end;

    private readonly array $children;

    /**
     * Constructor.
     *
     * @param Node|null $parent Parent node.
     * @param string $value Underlying value.
     * @param integer $start Start position relative to parent.
     * @param integer $end End position relative to parent.
     */
    public function __construct(?Node $parent = null, string $value = '', int $start = 0, int $end = 0)
    {
        $this->parent = $parent;
        $this->value = $value;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Returns child nodes.
     *
     * @return array<Node>
     */
    abstract public function getChildNodes(): array;

    /**
     * Returns diagnostic for the node and its children.
     *
     * @return array<Diagnostic>
     */
    public function getDiagnostics(): array
    {
        return [];
    }

    public function __get($name)
    {
        if ($name === 'children')
        {
            if (isset($this->children))
            {
                return $this->children;
            }

            $this->children = $this->getChildNodes();

            return $this->children;
        }
    }

    /**
     * @return Document
     */
    public function getRoot(): Node
    {
        // ! taken
        $node = $this;

        while ($node->parent !== null)
        {
            $node = $node->parent;
        }

        return $node;
    }

    /**
     * @return string
     */
    public function getSourceText(): string
    {
        // ! taken
        return $this->getRoot()->value;
    }

    /**
     * @return Position
     */
    public function getPositionAt(int $position): Position
    {
        $text = $this->getSourceText();

        // ! taken
        $textLength = \strlen($text);

        if ($position >= $textLength)
        {
            $position = $textLength;
        }
        elseif ($position < 0)
        {
            $position = 0;
        }

        // Start strrpos check from the character before the current character,
        // in case the current character is a newline
        $startAt = max(-($textLength - $position) - 1, -$textLength);
        $lastNewlinePos = \strrpos($text, "\n", $startAt);
        $char = $position - ($lastNewlinePos === false ? 0 : $lastNewlinePos + 1);
        $line = $position > 0 ? \substr_count($text, "\n", 0, $position) : 0;

        return new Position(
            line: $line,
            character: $char
        );
    }

    public function getRange(): Range
    {
        return new Range(
            start: $this->getPositionAt($this->start),
            end: $this->getPositionAt($this->end)
        );
    }

    public function jsonSerialize()
    {
        return [class_basename($this) => [
            'value' => $this->value,
            'start' => $this->start,
            'end' => $this->end,
            'range' => $this->getRange(),
            'children' => array_values($this->getChildNodes()),
            'diagnostics' => $this->getDiagnostics(),
        ]];
    }
}
