<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\ContentSnippets\Repository;


use Doctrine\ORM\EntityManagerInterface;
use Ingenerator\ContentSnippets\Entity\ContentSnippet;
use Ingenerator\ContentSnippets\UndefinedSnippetException;

class DoctrineContentSnippetRepository implements ContentSnippetRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function listAll()
    {
        return $this->em->getRepository(ContentSnippet::class)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function load($slug)
    {
        $snippet = $this->em->createQueryBuilder()
            ->select('snippet')
            ->from(ContentSnippet::class, 'snippet')
            ->where('snippet.slug = :slug')
            ->getQuery()
            ->useResultCache(TRUE, NULL, ContentSnippet::class.'-'.$slug)
            ->setParameter('slug', $slug)
            ->getOneOrNullResult();
        if ( ! $snippet) {
            throw new UndefinedSnippetException($slug);
        }
        return $snippet;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent($slug)
    {
        return $this->load($slug)->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function hasContent($slug)
    {
        return $this->load($slug)->hasContent();
    }

    /**
     * {@inheritdoc}
     */
    public function save(ContentSnippet $snippet)
    {
        $this->em->persist($snippet);
        $this->em->flush($snippet);
        $this->em->getConfiguration()->getResultCacheImpl()->delete(ContentSnippet::class.'-'.$snippet->getSlug());
    }


}
