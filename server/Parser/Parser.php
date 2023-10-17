<?php

namespace Server\Parser;

class Parser
{
    protected array $alphabet;

    public function __construct()
    {
        $alphabetLower = 'abcdefghijklmnopqrstuvwxyz';
        $this->alphabet = str_split($alphabetLower . strtoupper($alphabetLower));
    }

    /**
     * @param string $document
     *
     * @return array<Node>
     */
    public function parse(string $document): array
    {
        $nodes = [];
        $lines = $this->getDocumentLineNodes(explode("\n", $document));

        foreach ($lines as $line)
        {
            $line->children = $this->getLineTokens($line);
            $nodes[] = $line;
        }

        return $nodes;
    }

    /**
     * @param array<string> $explodedDocument
     *
     * @return array<Node>
     */
    protected function getDocumentLineNodes(array $explodedDocument): array
    {
        $nodes = [];

        foreach ($explodedDocument as $lineNumber => $line)
        {
            if (empty($line))
            {
                continue;
            }

            $nodes[] = new Node(null, NodeType::Line, $lineNumber, $lineNumber, $line);
        }

        return $nodes;
    }

    /**
     * @param Node $node
     *
     * @return array<Node>
     */
    protected function getLineTokens(Node $node): array
    {
        $childs = [];
        $chars = [...str_split($node->value), null];

        $currentNode = null;

        foreach ($chars as $currentIndex => $char)
        {
            if (!in_array($char, $this->alphabet) || $char === null)
            {
                if ($currentNode !== null)
                {
                    $currentNode->end = $currentIndex;
                    $childs[] = $currentNode;
                }

                $currentNode = null;

                continue;
            }

            if (is_null($currentNode))
            {
                $currentNode = new Node($node, NodeType::Word, $currentIndex, $currentIndex, '');
            }

            $currentNode->value .= $char;
        }

        return $childs;
    }
}
