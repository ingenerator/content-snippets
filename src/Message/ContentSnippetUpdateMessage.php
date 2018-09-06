<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Message;


use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\Pigeonhole\Message;

class ContentSnippetUpdateMessage extends Message
{

    public static function updated(ContentSnippet $snippet)
    {
        return new static(
            'Success',
            'The '.$snippet->getDisplayName().' snippet was updated.',
            static::SUCCESS
        );
    }

    public static function updatedCleaned(ContentSnippet $snippet)
    {
        return new static(
            'Updated after tidying',
            'Your changes were saved, but we have automatically tidied the HTML content you submitted. Please double check the result',
            static::WARNING
        );
    }

}
