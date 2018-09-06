<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;

class ContentSnippetContentFilter
{
    const MSG_NO_HTML = 'This snippet does not accept HTML content, please use plain text.';
    /**
     * @var \HTMLPurifier
     */
    private $purifier;

    /**
     * ContentSnippetContentFilter constructor.
     *
     * @param \HTMLPurifier $purifier
     */
    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    public function filterContent(ContentSnippet $snippet, $new_content)
    {
        if (ContentSnippet::isHtmlString($new_content)) {
            if ($snippet->allowsHtml()) {
                $cleaned = $this->cleanHtmlContent($new_content);
                return [
                    'cleaned_content' => $cleaned,
                    'is_valid'        => TRUE,
                    'error_msg'       => NULL,
                    'was_cleaned'     => ($cleaned !== $new_content)
                ];
            } else {
                return [
                    'cleaned_content' => $new_content,
                    'is_valid'        => FALSE,
                    'error_msg'       => static::MSG_NO_HTML,
                    'was_cleaned'     => FALSE,
                ];
            }
        } else {
            return [
                'cleaned_content' => $new_content,
                'is_valid'        => TRUE,
                'error_msg'       => NULL,
                'was_cleaned'     => FALSE
            ];
        }
    }

    protected function cleanHtmlContent($new_content)
    {
        return $this->purifier->purify($new_content);
    }
}
