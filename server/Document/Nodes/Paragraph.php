<?php

namespace Server\Document\Nodes;

use Server\Document\Node;

class Paragraph extends Node
{
    /**
     * @return array<Node>
     */
    public function getChildNodes(): array
    {
        $children = [];

        /**
         * @var Line
         */
        $current = null;


        foreach ([...str_split($this->value), null] as $index => $char)
        {
            $index = $this->start + $index;

            if ($char === "\n")
            {
                // $children[] = new Linebreak($this, $char, $index, $index);
            }

            if (($char === "\n" && !empty($current)) || is_null($char))
            {
                if (!empty($current))
                {
                    $children[] = new Line($this, $current->value, $current->start, $index);
                }

                $current = null;

                continue;
            }

            if ($char !== "\n")
            {
                $current = new Line($this, $current?->value . $char, $current?->start ?? $index, $index);
            }
        }

        usort($children, fn (Node $a, Node $b) => $a->start - $b->start);

        return $children;
    }

    public function getDiagnostics(): array
    {
        $diagnostics = [];

        foreach ($this->children as $child)
        {
            $diagnostics = array_merge($diagnostics, $child->getDiagnostics());
        }

        return $diagnostics;
    }
}
