<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Entity;


/**
 * @Entity
 * @ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @Table(name="content_snippets")
 */
class ContentSnippet
{

    /**
     * @var string
     * @Id
     * @Column(type="string")
     */
    protected $slug;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $display_name;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $help_text;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $allows_html;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @var \DateTimeImmutable
     * @Column(type="datetime_immutable")
     */
    protected $updated_at;

    /**
     * @param string $content
     *
     * @return bool
     */
    public static function isHtmlString($content)
    {
        if ($content === NULL) {
            return FALSE;
        }
        return $content !== strip_tags($content);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        return $this->help_text;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @throws \InvalidArgumentException if passing HTML and the snippet doesn't allow it
     */
    public function setContent($content)
    {
        if (( ! $this->allowsHtml()) AND static::isHtmlString($content)) {
            throw new \InvalidArgumentException(
                'HTML content is not permitted for snippet '.$this->slug
            );
        }
        if ($content !== $this->content) {
            $this->content    = $content;
            $this->updated_at = new \DateTimeImmutable;
        }
    }

    /**
     * @return bool
     */
    public function allowsHtml()
    {
        return $this->allows_html;
    }

    public function hasContent()
    {
        return (bool) $this->content;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


}
