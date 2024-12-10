<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Repository;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;

interface ContentSnippetRepository
{

    /**
     * @return list<ContentSnippet>
     */
    public function listAll(): array;

    /**
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     */
    public function load(string $slug): ContentSnippet;

    /**
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     */
    public function getContent(string $slug): ?string;

    public function save(ContentSnippet $snippet): void;

    public function hasContent(string $slug): bool;
}
