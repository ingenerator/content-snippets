<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\BehatContexts;


use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Step\Given;
use Ingenerator\ContentSnippets\Repository\ContentSnippetRepository;
use PHPUnit\Framework\Assert;
use function trim;

class ContentSnippetsContext extends RawMinkContext
{

    protected ContentSnippetRepository $repo;

    public function __construct(ContentSnippetRepository $repo)
    {
        $this->repo = $repo;
    }

    #[Given('/^the (?P<slug>[^ ]+) snippet has the following content:$/')]
    public function givenContent(string $slug, PyStringNode $content): void
    {
        $snippet = $this->repo->load($slug);
        $snippet->setContent($content->getRaw());
        $this->repo->save($snippet);
    }

    #[Given('/^the (?P<slug>[^ ]+) snippet is empty$/')]
    public function givenEmpty(string $slug): void
    {
        $snippet = $this->repo->load($slug);
        $snippet->setContent(NULL);
        $this->repo->save($snippet);
    }

    #[When('/^I try to update the "(?P<display_name>[^"]+)" snippet with:$/')]
    public function tryToUpdateContent(string $display_name, PyStringNode $new_content): void
    {
        $assert = $this->assertSession();
        $table = $assert->elementExists('css', '[data-content-snippets-list]');
        $table->clickLink('Edit '.$display_name);
        $page = $this->getSession()->getPage();
        $page->fillField('Content', $new_content->getRaw());
        $page->pressButton('Save changes');
    }

    #[Then('/^the (?P<selector>.+?) element should have this exact HTML:$/')]
    public function assertElementExactHtml(string $selector, PyStringNode $expect): void
    {
        $element = $this->assertSession()->elementExists('css', $selector);
        $actual = trim($element->getHtml());
        Assert::assertEquals($expect->getRaw(), $actual);
    }

}
