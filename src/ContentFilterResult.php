<?php

namespace Ingenerator\ContentSnippets;

readonly final class ContentFilterResult
{

    public function __construct(
        public ?string $cleaned_content,
        public bool $is_valid,
        public ?string $error_msg,
        public bool $was_cleaned,
    ) {

    }

}
