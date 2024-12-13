<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\View;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\Repository\ContentSnippetRepository;
use Ingenerator\KohanaView\ViewModel\AbstractViewModel;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function ksort;
use function preg_replace;
use function str_replace;
use function strip_tags;
use function trim;

/**
 * @package Ingenerator\ContentSnippets\View
 *
 * @property-read array $snippet_rows
 */
abstract class BaseContentSnippetsListView extends AbstractViewModel
{

    protected int $excerpt_word_limit = 15;

    public function __construct(
        protected readonly ContentSnippetRepository $snippets_repo
    ) {
        parent::__construct();
    }

    protected function var_snippet_rows(): array
    {
        $rows = [];
        foreach ($this->snippets_repo->listAll() as $snippet) {
            $rows[$snippet->getDisplayName()] = [
                'edit_url' => $this->getEditUrl($snippet),
                'display_name' => $snippet->getDisplayName(),
                'content_excerpt' => $this->formatExcerpt($snippet),
                'has_content' => $snippet->hasContent(),
                'row_class' => $snippet->hasContent() ? '' : 'warning',
            ];
        }
        ksort($rows);

        return array_values($rows);
    }

    abstract protected function getEditUrl(ContentSnippet $snippet): string;

    protected function formatExcerpt(ContentSnippet $snippet): string
    {
        $content = $snippet->getContent();
        // Add whitespace before all html tags so enclosed text doesn't bump into each other
        $content = str_replace('<', ' <', (string) $content);
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
