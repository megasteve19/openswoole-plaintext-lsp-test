<?php

namespace Server\Workspace;

use OpenSwoole\Table;

class Documents
{
    protected static Table $documents;

    public static function init(): void
    {
        static::$documents = new Table(64);
        static::$documents->column('content', Table::TYPE_STRING, 8192);
        static::$documents->column('version', Table::TYPE_INT, 1024);
        static::$documents->create();
    }

    public static function get(string $uri): ?array
    {
        if (static::$documents->exists($uri))
        {
            return static::$documents->get($uri);
        }

        return null;
    }

    public static function getContent(string $uri): ?string
    {
        return ($content = static::get($uri))
            ? $content['content']
            : file_get_contents($uri);
    }

    public static function sync(string $uri, string $content, ?int $version = null): void
    {
        if ($version === null || !static::$documents->exists($uri))
        {
            static::$documents->set($uri, [
                'content' => $content,
                'version' => $version ?? 0,
            ]);

            return;
        }

        if (static::$documents->exists($uri))
        {
            $document = static::$documents->get($uri);

            if ($document['version'] < $version)
            {
                static::$documents->set($uri, [
                    'content' => $content,
                    'version' => $version,
                ]);
            }
        }
    }
}
