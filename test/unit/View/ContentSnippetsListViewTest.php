<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets\View;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\Repository\ArrayContentSnippetRepository;
use Ingenerator\ContentSnippets\View\BaseContentSnippetsListView;

class ContentSnippetsListViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ingenerator\ContentSnippets\Repository\ArrayContentSnippetRepository
     */
    protected $snippets_repo;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(BaseContentSnippetsListView::class, $this->newSubject());
    }

    public function test_it_lists_all_snippets_alphabetically_with_excerpt_and_edit_url()
    {
        $this->snippets_repo = ArrayContentSnippetRepository::with(
            [
                'slug'         => 'something',
                'display_name' => 'Our - First one',
                'content'      => '<p>Here is the introductory text</p>',
            ],
            [
                'slug'         => 'something-else',
                'display_name' => 'First alphabetically',
                'content'      => '<h3>This one has</h3><p>A lot of longer text and it goes on and on for ages with loads of content that would blow up the table</p>',
            ],
            [
                'slug'         => 'new-one',
                'display_name' => 'New',
                'content'      => NULL,
            ]
        );

        $this->assertEquals(
            [
                [
                    'edit_url'        => '/edit/something-else',
                    'display_name'    => 'First alphabetically',
                    'content_excerpt' => 'This one has A lot of longer text and it goes on and on for…',
                    'has_content'     => TRUE,
                    'row_class'       => '',
                ],
                [
                    'edit_url'        => '/edit/new-one',
                    'display_name'    => 'New',
                    'content_excerpt' => '',
                    'has_content'     => FALSE,
                    'row_class'       => 'warning',
                ],
                [
                    'edit_url'        => '/edit/something',
                    'display_name'    => 'Our - First one',
                    'content_excerpt' => 'Here is the introductory text',
                    'has_content'     => TRUE,
                    'row_class'       => '',
                ],
            ],
            $this->newSubject()->snippet_rows
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->snippets_repo = ArrayContentSnippetRepository::withNothing();
    }

    protected function newSubject()
    {
        return new ImplementedContentSnippetsListView($this->snippets_repo);
    }


}

class ImplementedContentSnippetsListView extends BaseContentSnippetsListView
{
    protected function getEditUrl(ContentSnippet $snippet)
    {
        return '/edit/'.$snippet->getSlug();
    }

}
