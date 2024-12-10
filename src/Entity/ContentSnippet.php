<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Entity;


use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ChangeTrackingPolicy;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use InvalidArgumentException;
use function strip_tags;

#[Entity]
#[ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[Table(name: 'content_snippets')]
class ContentSnippet
{

    #[Id]
    #[Column(type: Types::STRING)]
    protected string $slug;

    #[Column(type: Types::STRING)]
    protected string $display_name;

    #[Column(type: Types::TEXT, nullable: TRUE)]
    protected ?string $help_text = NULL;

    #[Column(type: Types::BOOLEAN)]
    protected bool $allows_html = FALSE;

    #[Column(type: Types::TEXT, nullable: TRUE)]
    protected ?string $content = NULL;

    #[Column(type: Types::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $updated_at;

    public static function isHtmlString(?string $content): bool
    {
        if ($content === NULL) {
            return FALSE;
        }

        return $content !== strip_tags($content);
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDisplayName(): string
    {
        return $this->display_name;
    }

    public function getHelpText(): ?string
    {
        return $this->help_text;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @throws InvalidArgumentException if passing HTML and the snippet doesn't allow it
     */
    public function setContent(?string $content): void
    {
        if (( ! $this->allowsHtml()) and static::isHtmlString($content)) {
            throw new InvalidArgumentException(
                'HTML content is not permitted for snippet '.$this->slug
            );
        }
        if ($content !== $this->content) {
            $this->content = $content;
            $this->updated_at = new DateTimeImmutable;
        }
    }

    public function allowsHtml(): bool
    {
        return $this->allows_html;
    }

    public function hasContent(): bool
    {
        return (bool) $this->content;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }


}
