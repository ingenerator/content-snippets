<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\ContentSnippets\Entity;


use DateTimeImmutable;
use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\PHPUtils\Object\ObjectPropertyPopulator;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ContentSnippetTest extends TestCase
{

    #[TestWith([null, false])]
    #[TestWith(["", false])]
    #[TestWith(["any", true])]
    public function test_it_has_no_content_if_content_empty_or_null($content, $expect)
    {
        $snippet = $this->newSubject(['content' => $content]);
        $this->assertSame($expect, $snippet->hasContent());
    }


    #[TestWith([["content" => "anything", "updated_at" => "2016-01-01"], "anything", "2016-01-01"])]
    #[TestWith([["content" => "anything", "updated_at" => "2016-01-01"], "else", ""])]
    public function test_it_updates_update_time_with_content_only_if_changed(
        $orig_data,
        $content,
        $expect
    ) {
        $orig_data['updated_at'] = new DateTimeImmutable($orig_data['updated_at']);
        $snippet                 = $this->newSubject($orig_data);
        $snippet->setContent($content);
        $this->assertEqualsWithDelta(
            new DateTimeImmutable($expect),
            $snippet->getUpdatedAt(),
            1,
            'Updated time should match '.$expect.' to within 1 second'
        );
    }

    #[TestWith([true, "<p>My html is all good</p>", false])]
    #[TestWith([true, "plain text is fine too", false])]
    #[TestWith([false, "plain test is fine for plain", false])]
    #[TestWith([false, "don't you give me no <strong>html</strong>", true])]
    public function test_it_throws_if_assigning_html_unless_html_is_allowed(
        $allowed,
        $content,
        $should_throw
    ) {
        $e = NULL;
        $snippet = $this->newSubject(['allows_html' => $allowed, 'slug' => 'whatever']);
        try {
            $snippet->setContent($content);
        } catch (\InvalidArgumentException $e) {
            // Just capture it
        }

        if ($should_throw) {
            $this->assertInstanceOf(
                \InvalidArgumentException::class,
                $e,
                'Should throw when assigning '.$content
            );
        } else {
            $this->assertNull($e, 'Should not throw when assigning '.$content);
        }
    }

    protected function newSubject(array $properties = []): ContentSnippet
    {
        $snippet = new ContentSnippet;
        ObjectPropertyPopulator::assignHash($snippet, $properties);

        return $snippet;
    }

}
