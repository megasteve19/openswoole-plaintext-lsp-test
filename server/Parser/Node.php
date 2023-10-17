<?php

namespace Server\Parser;

class Node
{
    public ?Node $parent;

    public NodeType $type;

    public int $start;

    public int $end;

    public string $value;

    /**
     * @var array<Node>
     */
    public array $children = [];

    public function __construct(?Node $parent = null, NodeType $type, int $start, int $end, string $value)
    {
        $this->parent = $parent;
        $this->type = $type;
        $this->start = $start;
        $this->end = $end;
        $this->value = $value;
    }
}
