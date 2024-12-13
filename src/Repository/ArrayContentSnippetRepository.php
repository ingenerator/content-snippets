<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Repository;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\UndefinedSnippetException;
use Ingenerator\PHPUtils\Repository\AbstractArrayRepository;

class ArrayContentSnippetRepository extends AbstractArrayRepository implements ContentSnippetRepository
{

    /**
     * @param array<string,string> $content_strings as slug => content
     */
    public static function withSnippetContentHash(array $content_strings): self
    {
        $snippets = [];
        foreach ($content_strings as $slug => $content) {
            $snippets[] = ['slug' => $slug, 'content' => $content];
        }

        return static::withList($snippets);
    }

    protected static function getEntityBaseClass(): string
    {
        return ContentSnippet::class;
    }

    /**
     * {@inheritdoc}
     */
    public function listAll(): array
    {
        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $slug): ContentSnippet
    {
        $entity = $this->findWith(
            function (ContentSnippet $snippet) use ($slug) { return $snippet->getSlug() === $slug; }
        );
        if ( ! $entity) {
            throw new UndefinedSnippetException($slug);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(string $slug): ?string
    {
        return $this->load($slug)->getContent();
    }

    public function save(ContentSnippet $snippet): void
    {
        throw new \BadMethodCallException(__METHOD__.' not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function hasContent(string $slug): bool
    {
        return $this->load($slug)->hasContent();
    }


}
