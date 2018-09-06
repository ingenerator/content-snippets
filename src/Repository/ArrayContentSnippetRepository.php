<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Repository;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\UndefinedSnippetException;
use Ingenerator\PHPUtils\Object\ObjectPropertyPopulator;
use Ingenerator\ContentSnippets\Repository\AbstractArrayRepository;

class ArrayContentSnippetRepository extends AbstractArrayRepository implements ContentSnippetRepository
{

    /**
     * @param string[] $content_strings as slug => content
     *
     * @return ArrayContentSnippetRepository
     */
    public static function withSnippetContentHash(array $content_strings)
    {
        $snippets = [];
        foreach ($content_strings as $slug => $content) {
            $snippets[] = ['slug' => $slug, 'content' => $content];
        }

        return static::withList($snippets);
    }

    protected static function getEntityBaseClass()
    {
        return ContentSnippet::class;
    }

    protected static function stubEntity(array $data)
    {
        $snippet = new ContentSnippet;
        ObjectPropertyPopulator::assignHash($snippet, $data);

        return $snippet;
    }

    public function listAll()
    {
        return $this->entities;
    }

    /**
     * @param string $slug
     *
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     *
     * @return ContentSnippet
     */
    public function load($slug)
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
     * @param string $slug
     *
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     *
     * @return string
     */
    public function getContent($slug)
    {
        return $this->load($slug)->getContent();
    }

    /**
     * @param \Ingenerator\ContentSnippets\Entity\ContentSnippet $snippet
     *
     * @return void
     */
    public function save(ContentSnippet $snippet)
    {
        throw new \BadMethodCallException(__METHOD__.' not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function hasContent($slug)
    {
        return $this->load($slug)->hasContent();
    }


}
