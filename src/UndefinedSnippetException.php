<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets;


class UndefinedSnippetException extends \InvalidArgumentException
{

    public function __construct($slug)
    {
        parent::__construct('No content snippet defined with the slug '.$slug);
    }

}
