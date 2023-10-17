<?php

namespace Server\Document\Nodes;

use Server\Document\Node;

class Linebreak extends Node
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
}
