<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets\Entity;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\PHPUtils\Object\ObjectPropertyPopulator;

class ContentSnippetTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @testWith [null, false]
     *           ["", false]
     *           ["any", true]
     */
    public function test_it_has_no_content_if_content_empty_or_null($content, $expect)
    {
        $snippet = $this->newSubject(['content' => $content]);
        $this->assertSame($expect, $snippet->hasContent());
    }

    /**
     * @testWith [{"content": "anything", "updated_at": "2016-01-01"}, "anything", "2016-01-01"]
     *           [{"content": "anything", "updated_at": "2016-01-01"}, "else", ""]
     */
    public function test_it_updates_update_time_with_content_only_if_changed(
        $orig_data,
        $content,
        $expect
    ) {
        $orig_data['updated_at'] = new \DateTimeImmutable($orig_data['updated_at']);
        $snippet                 = $this->newSubject($orig_data);
        $snippet->setContent($content);
        $this->assertEquals(
            new \DateTimeImmutable($expect),
            $snippet->getUpdatedAt(),
            'Updated time should match '.$expect.' to within 1 second',
            1
        );
    }

    /**
     * @testWith [true, "<p>My html is all good</p>", false]
     *           [true, "plain text is fine too", false]
     *           [false, "plain test is fine for plain", false]
     *           [false, "don't you give me no <strong>html</strong>", true]
     */
    public function test_it_throws_if_assigning_html_unless_html_is_allowed(
        $allowed,
        $content,
        $should_throw
    ) {
        $snippet = $this->newSubject(['allows_html' => $allowed]);
        try {
            $snippet->setContent($content);
            if ($should_throw) {
                $this->fail('Should throw when assigning '.$content);
            }
        } catch (\InvalidArgumentException $e) {
            if ( ! $should_throw) {
                $this->fail('Should not throw when assigning '.$content);
            }
        }
    }

    /**
     * @param array $properties
     *
     * @return \Ingenerator\ContentSnippets\Entity\ContentSnippet
     */
    protected function newSubject(array $properties = [])
    {
        $snippet = new ContentSnippet;
        ObjectPropertyPopulator::assignHash($snippet, $properties);
        return $snippet;
    }

}
