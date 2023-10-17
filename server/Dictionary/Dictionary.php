<?php

namespace Server\Dictionary;

class Dictionary
{
    public static array $words;

    public static function init(): void
    {
        static::$words = require __DIR__ . '/../../resources/words.php';
    }

    public static function search(string $search, int $limit = 30): array
    {
        $matches = [];

        foreach (static::$words as $word => $definition)
        {
            similar_text($search, $word, $percent);

            if ($percent >= 50 && $percent)
            {
                if (!empty($matches) && min(array_column($matches, 'percent')) > $percent)
                {
                    continue;
                }

                $matches[] = [
                    'word' => $word,
                    'definition' => $definition,
                    'percent' => $percent,
                ];

                usort($matches, fn ($a, $b) => $b['percent'] <=> $a['percent']);

                $matches = array_slice($matches, 0, $limit);
            }
        }

        return $matches;
    }
}
