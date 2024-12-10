<?php

namespace Ingenerator\ContentSnippets\ContentFilter;

use HTMLPurifier;
use Ingenerator\ContentSnippets\ContentFilterResult;
use Ingenerator\ContentSnippets\ContentSnippetContentFilter;
use Ingenerator\ContentSnippets\Entity\ContentSnippet;

final class HtmlPurifierContentFilter implements ContentSnippetContentFilter
{

    public function __construct(
        private readonly HTMLPurifier $purifier
    ) {
    }

    public function filterContent(ContentSnippet $snippet, ?string $new_content): ContentFilterResult
    {
        if (ContentSnippet::isHtmlString($new_content)) {
            if ($snippet->allowsHtml()) {
                $cleaned = $this->cleanHtmlContent($new_content);

                return new ContentFilterResult(
                    cleaned_content: $cleaned,
                    is_valid: TRUE,
                    error_msg: NULL,
                    was_cleaned: ($cleaned !== $new_content),
                );
            } else {
                return new ContentFilterResult(
                    cleaned_content: $new_content,
                    is_valid: FALSE,
                    error_msg: self::MSG_NO_HTML,
                    was_cleaned: FALSE
                );
            }
        } else {
            return new ContentFilterResult(
                cleaned_content: $new_content,
                is_valid: TRUE,
                error_msg: NULL,
                was_cleaned: FALSE
            );
        }
    }

    protected function cleanHtmlContent(?string $new_content): ?string
    {
        return $this->purifier->purify($new_content);
    }
}
