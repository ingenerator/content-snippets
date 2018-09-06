<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\View;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\Repository\ContentSnippetRepository;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;

/**
 * @package Ingenerator\ContentSnippets\View
 *
 * @property-read array $snippet_rows
 */
abstract class BaseContentSnippetsListView extends AbstractViewModel
{

    /**
     * @var \Ingenerator\ContentSnippets\Repository\ContentSnippetRepository
     */
    protected $snippets_repo;

    /**
     * @var int
     */
    protected $excerpt_word_limit = 15;

    public function __construct(ContentSnippetRepository $snippets_repo)
    {
        $this->snippets_repo = $snippets_repo;
        parent::__construct();
    }

    protected function var_snippet_rows()
    {
        $rows = [];
        foreach ($this->snippets_repo->listAll() as $snippet) {
            $rows[$snippet->getDisplayName()] = [
                'edit_url'        => $this->getEditUrl($snippet),
                'display_name'    => $snippet->getDisplayName(),
                'content_excerpt' => $this->formatExcerpt($snippet),
                'has_content'     => $snippet->hasContent(),
                'row_class'       => $snippet->hasContent() ? '' : 'warning'
            ];
        }
        ksort($rows);
        return array_values($rows);
    }

    abstract protected function getEditUrl(ContentSnippet $snippet);

    protected function formatExcerpt(ContentSnippet $snippet)
    {
        $content = $snippet->getContent();
        // Add whitespace before all html tags so enclosed text doesn't bump into each other
        $content = str_replace('<', ' <', $content);
        // Remove the tags
        $content = strip_tags($content);
        // Close up double spaces and remove leading and trailing space
        $content = trim(preg_replace('/\s+/', ' ', $content));
        // Reduce it to the word limit if required
        $words = explode(' ', $content);
        if (count($words) > $this->excerpt_word_limit) {
            return implode(' ', array_slice($words, 0, $this->excerpt_word_limit)).'…';
        } else {
            return $content;
        }
    }
}

