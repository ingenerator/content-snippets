<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;

interface ContentSnippetContentFilter
{
    public const string MSG_NO_HTML = 'This snippet does not accept HTML content, please use plain text.';

    public function filterContent(ContentSnippet $snippet, ?string $new_content): ContentFilterResult;

}
