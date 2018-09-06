<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets;


use Ingenerator\ContentSnippets\ContentSnippetContentFilter;
use Ingenerator\ContentSnippets\ContentSnippetsDependencyFactory;
use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\PHPUtils\Object\ObjectPropertyPopulator;

class ContentSnippetContentFilterTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \HTMLPurifier
     */
    protected $purifier;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(ContentSnippetContentFilter::class, $this->newSubject());
    }

    public function test_it_returns_unmodified_valid_input_for_null()
    {
        $this->assertFiltersValidAndNotModified(NULL, new ContentSnippet);
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_it_returns_unmodified_valid_input_for_plain_text_to_plain_text_or_html_snippet(
        $allow_html
    ) {
        $this->assertFiltersValidAndNotModified(
            'any old string',
            $this->givenSnippet(['allows_html' => $allow_html])
        );
    }

    public function test_it_returns_validation_error_for_html_submitted_to_plain_text_snippet()
    {
        $result = $this->newSubject()->filterContent(
            $this->givenSnippet(['allows_html' => FALSE]),
            '<p>this should not be HTML!</p>'
        );
        $this->assertEquals(
            [
                'cleaned_content' => '<p>this should not be HTML!</p>',
                'is_valid'        => FALSE,
                'error_msg'       => ContentSnippetContentFilter::MSG_NO_HTML,
                'was_cleaned'     => FALSE,
            ],
            $result
        );

    }

    public function test_it_returns_unmodified_valid_input_for_valid_html_to_html_snippet()
    {
        $this->assertFiltersValidAndNotModified(
            '<p>All fine and dandy!</p>',
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    public function provider_invalid_html()
    {
        return [
            [
                '<div>unclosed tags',
                '<div>unclosed tags</div>',
            ],
            [
                '<p><a href="foo"><div>Invalid block child</div></a></p>',
                '<p><a href="foo"></a></p><div><a href="foo">Invalid block child</a></div><a href="foo"></a>',
            ],
            [
                'extra closing tag</div>',
                'extra closing tag',
            ],
        ];
    }

    /**
     * @dataProvider provider_invalid_html
     */
    public function test_it_returns_modified_valid_input_for_tidied_html_to_html_snippet(
        $input,
        $expect
    ) {
        $result = $this->newSubject()->filterContent(
            $this->givenSnippet(['allows_html' => TRUE]),
            $input
        );
        $this->assertEquals(
            [
                'cleaned_content' => $expect,
                'is_valid'        => TRUE,
                'error_msg'       => NULL,
                'was_cleaned'     => TRUE,
            ],
            $result
        );
    }

    /**
     * @testWith ["http://some.where/else?foo=bar"]
     *           ["https://some.where/else?foo=bar"]
     *           ["mailto:me@home.net"]
     *           ["tel:01315100271"]
     *           ["/a/local/page"]
     */
    public function test_it_allows_external_local_tel_and_mailto_links($link_url)
    {
        $this->assertFiltersValidAndNotModified(
            '<p><a href="'.$link_url.'">Take me somewhere!</a></p>',
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    /**
     * @testWith ["/assets/an/image.jpg"]
     */
    public function test_it_allows_local_images_and_from_teamdetails_domains($img_src)
    {
        $this->assertFiltersValidAndNotModified(
            '<p><img src="'.$img_src.'" alt="I have an alt"></p>',
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    /**
     * @testWith ["http://external.domain/assets/an/image.jpg"]
     *           ["https://external.domain/assets/an/image.jpg"]
     *           ["//scheme.relative/but/still/external"]
     *           ["http://demo.teamdetails.com/my.jpg"]
     */
    public function test_it_does_not_allow_remote_or_http_images($img_src)
    {
        $result = $this->newSubject()->filterContent(
            $this->givenSnippet(['allows_html' => TRUE]),
            '<p><img src="'.$img_src.'" alt="I have an alt">but this image is offsite</p>'
        );
        $this->assertEquals(
            [
                'cleaned_content' => '<p>but this image is offsite</p>',
                'is_valid'        => TRUE,
                'error_msg'       => NULL,
                'was_cleaned'     => TRUE,
            ],
            $result
        );
    }

    public function test_it_allows_complex_current_fringe_homepage_content()
    {
        // to resolve: inline-block on the links
        $html = <<<'HTML'
<p>Welcome to the Fringe! <strong>We provide support and the like</strong></p>
<p>
    For more info visit us <a href="https://www.edfringe.com/learn/work-with-us">www.edfringe.com</a>
    or
    <a href="https://www.edfringe.com/learn/work-with-us" class="btn btn-primary btn-block">Find out more <i class="fa fa-external-link"></i></a>
    or email
    <a href="mailto:recruitment@edfringe.com">recruitment@edfringe.com</a> or call (+44) 0131 226 0026
</p>
<hr><div>
<img alt="edfringe" src="/assets/sites/edfringe/edfringe_highlighter_logo.png" style="width:auto;height:90px;margin-right:20px;"><a href="http://www.gov.uk/government/collections/disability-confident-campaign" target="_blank" style="margin-right:20px;margin-bottom:20px;" rel="noreferrer noopener">
<img alt="Disability Confident Employer" src="/assets/scheme_logos/disability_confident_employer.png" style="width:auto;height:90px;"></a>
<a href="http://www.livingwage.org.uk/" target="_blank" style="margin-bottom:20px;" rel="noreferrer noopener"><img alt="Living Wage Employer" src="/assets/scheme_logos/living_wage_employer.jpeg" style="width:auto;height:90px;"></a>
</div>
HTML;
        $this->assertFiltersValidAndNotModified(
            $html,
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->purifier = new \HTMLPurifier(ContentSnippetsDependencyFactory::makePurifierConfig());
    }

    /**
     * @return \Ingenerator\ContentSnippets\ContentSnippetContentFilter
     */
    public function newSubject()
    {
        return new ContentSnippetContentFilter($this->purifier);
    }

    /**
     * @param $expect_content
     * @param $snippet
     */
    protected function assertFiltersValidAndNotModified($expect_content, $snippet)
    {
        $this->assertEquals(
            [
                'cleaned_content' => $expect_content,
                'is_valid'        => TRUE,
                'error_msg'       => NULL,
                'was_cleaned'     => FALSE,
            ],
            $this->newSubject()->filterContent($snippet, $expect_content)
        );
    }

    /**
     * @param $properties
     *
     * @return \Ingenerator\ContentSnippets\Entity\ContentSnippet
     */
    protected function givenSnippet($properties)
    {
        $snippet = new ContentSnippet;
        ObjectPropertyPopulator::assignHash($snippet, $properties);

        return $snippet;
    }


}
