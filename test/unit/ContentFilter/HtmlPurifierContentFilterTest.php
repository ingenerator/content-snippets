<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentFilter\ContentSnippets;


use HTMLPurifier;
use Ingenerator\ContentSnippets\ContentFilter\HtmlPurifierContentFilter;
use Ingenerator\ContentSnippets\ContentFilterResult;
use Ingenerator\ContentSnippets\ContentSnippetContentFilter;
use Ingenerator\ContentSnippets\ContentSnippetsDependencyFactory;
use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\PHPUtils\Object\ObjectPropertyPopulator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class HtmlPurifierContentFilterTest extends TestCase
{

    private HtmlPurifier $purifier;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(ContentSnippetContentFilter::class, $this->newSubject());
    }

    public function test_it_returns_unmodified_valid_input_for_null()
    {
        $this->assertFiltersValidAndNotModified(NULL, new ContentSnippet);
    }

    #[TestWith([TRUE])]
    #[TestWith([FALSE])]
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
            new ContentFilterResult(
                cleaned_content: '<p>this should not be HTML!</p>',
                is_valid: FALSE,
                error_msg: ContentSnippetContentFilter::MSG_NO_HTML,
                was_cleaned: FALSE,
            ),
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

    public static function provider_invalid_html(): array
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

    #[DataProvider('provider_invalid_html')]
    public function test_it_returns_modified_valid_input_for_tidied_html_to_html_snippet(
        $input,
        $expect
    ) {
        $result = $this->newSubject()->filterContent(
            $this->givenSnippet(['allows_html' => TRUE]),
            $input
        );
        $this->assertEquals(
            new ContentFilterResult(
                cleaned_content: $expect,
                is_valid: TRUE,
                error_msg: NULL,
                was_cleaned: TRUE,
            ),
            $result
        );
    }


    #[TestWith(["http://some.where/else?foo=bar"])]
    #[TestWith(["https://some.where/else?foo=bar"])]
    #[TestWith(["mailto:me@home.net"])]
    #[TestWith(["tel:01315100271"])]
    #[TestWith(["/a/local/page"])]
    public function test_it_allows_external_local_tel_and_mailto_links($link_url)
    {
        $this->assertFiltersValidAndNotModified(
            '<p><a href="'.$link_url.'">Take me somewhere!</a></p>',
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    #[TestWith(['assets/an/image.jpg'])]
    public function test_it_allows_local_images($img_src)
    {
        $this->assertFiltersValidAndNotModified(
            '<p><img src="'.$img_src.'" alt="I have an alt"></p>',
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    #[TestWith(["http://external.domain/assets/an/image.jpg"])]
    #[TestWith(["https://external.domain/assets/an/image.jpg"])]
    #[TestWith(["//scheme.relative/but/still/external"])]
    #[TestWith(["http://any.domain/my.jpg"])]
    public function test_it_does_not_allow_remote_or_http_images($img_src)
    {
        $result = $this->newSubject()->filterContent(
            $this->givenSnippet(['allows_html' => TRUE]),
            '<p><img src="'.$img_src.'" alt="I have an alt">but this image is offsite</p>'
        );
        $this->assertEquals(
            new ContentFilterResult(
                cleaned_content: '<p>but this image is offsite</p>',
                is_valid: TRUE,
                error_msg: NULL,
                was_cleaned: TRUE,
            ),
            $result
        );
    }

    public function test_it_allows_complex_html_content()
    {
        // to resolve: inline-block on the links
        $html = <<<'HTML'
<p>Welcome to our site! <strong>We provide support and the like</strong></p>
<p>
    For more info visit us <a href="https://our.site.com/learn/work-with-us">www.oursite.com</a>
    or
    <a href="https://our.site.com/learn/work-with-us" class="btn btn-primary btn-block">Find out more <i class="fa fa-external-link"></i></a>
    or email
    <a href="mailto:us@oursite.com">us@oursite.com</a> or call (+44) 0131 123 1234
</p>
<hr><div>
<img alt="us" src="/assets/sites/us/our_logo.png" style="width:auto;height:90px;margin-right:20px;"><a href="http://www.gov.uk/government/collections/disability-confident-campaign" target="_blank" style="margin-right:20px;margin-bottom:20px;" rel="noreferrer noopener">
<img alt="Disability Confident Employer" src="/assets/scheme_logos/disability_confident_employer.png" style="width:auto;height:90px;"></a>
<a href="http://www.livingwage.org.uk/" target="_blank" style="margin-bottom:20px;" rel="noreferrer noopener"><img alt="Living Wage Employer" src="/assets/scheme_logos/living_wage_employer.jpeg" style="width:auto;height:90px;"></a>
</div>
HTML;
        $this->assertFiltersValidAndNotModified(
            $html,
            $this->givenSnippet(['allows_html' => TRUE])
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->purifier = new HTMLPurifier(ContentSnippetsDependencyFactory::makePurifierConfig());
    }

    public function newSubject(): HtmlPurifierContentFilter
    {
        return new HtmlPurifierContentFilter($this->purifier);
    }

    protected function assertFiltersValidAndNotModified(?string $content, ContentSnippet $snippet)
    {
        $this->assertEquals(
            new ContentFilterResult(
                cleaned_content: $content,
                is_valid: TRUE,
                error_msg: NULL,
                was_cleaned: FALSE
            ),
            $this->newSubject()->filterContent($snippet, $content)
        );
    }

    protected function givenSnippet(array $properties): ContentSnippet
    {
        $snippet = new ContentSnippet;
        ObjectPropertyPopulator::assignHash($snippet, $properties);

        return $snippet;
    }


}
