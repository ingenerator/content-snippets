<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\BehatContexts;


use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Ingenerator\ContentSnippets\Repository\ContentSnippetRepository;

class ContentSnippetsContext extends RawMinkContext
{

    /**
     * @var \Ingenerator\ContentSnippets\Repository\ContentSnippetRepository
     */
    protected $repo;

    public function __construct(ContentSnippetRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @Given /^the (?P<slug>[^ ]+) snippet has the following content:$/
     */
    public function givenContent($slug, PyStringNode $content)
    {
        $snippet = $this->repo->load($slug);
        $snippet->setContent($content->getRaw());
        $this->repo->save($snippet);
    }

    /**
     * @Given /^the (?P<slug>[^ ]+) snippet is empty$/
     */
    public function givenEmpty($slug)
    {
        $snippet = $this->repo->load($slug);
        $snippet->setContent(NULL);
        $this->repo->save($snippet);
    }

    /**
     * @When /^I try to update the "(?P<display_name>[^"]+)" snippet with:$/
     */
    public function tryToUpdateContent($display_name, PyStringNode $new_content)
    {
        $assert = $this->assertSession();
        $table  = $assert->elementExists('css', '[data-content-snippets-list]');
        $table->clickLink('Edit '.$display_name);
        $page = $this->getSession()->getPage();
        $page->fillField('Content', $new_content->getRaw());
        $page->pressButton('Save changes');
    }

    /**
     * @Then /^the (?P<selector>.+?) element should have this exact HTML:$/
     */
    public function assertElementExactHtml($selector, PyStringNode $expect)
    {
        $element = $this->assertSession()->elementExists('css', $selector);
        $actual  = trim($element->getHtml());
        \PHPUnit_Framework_Assert::assertEquals($expect->getRaw(), $actual);
    }

}
