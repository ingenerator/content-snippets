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
     * @return ContentSnippet[]
     */
    public function listAll();

    /**
     * @param string $slug
     *
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     *
     * @return ContentSnippet
     */
    public function load($slug);

    /**
     * @param string $slug
     *
     * @throws \Ingenerator\ContentSnippets\UndefinedSnippetException
     *
     * @return string
     */
    public function getContent($slug);

    /**
     * @param \Ingenerator\ContentSnippets\Entity\ContentSnippet $snippet
     *
     * @return void
     */
    public function save(ContentSnippet $snippet);

    /**
     * @param $slug
     *
     * @return bool
     */
    public function hasContent($slug);
}
