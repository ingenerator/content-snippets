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

    public function __construct(
        protected readonly EntityManagerInterface $em
    ) {

    }

    /**
     * {@inheritdoc}
     */
    public function listAll(): array
    {
        return $this->em->getRepository(ContentSnippet::class)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $slug): ContentSnippet
    {
        $snippet = $this->em->createQueryBuilder()
            ->select('snippet')
            ->from(ContentSnippet::class, 'snippet')
            ->where('snippet.slug = :slug')
            ->getQuery()
            ->enableResultCache(NULL, 'cs-'.sha1($slug))
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
    public function getContent(string $slug): ?string
    {
        return $this->load($slug)->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function hasContent(string $slug): bool
    {
        return $this->load($slug)->hasContent();
    }

    /**
     * {@inheritdoc}
     */
    public function save(ContentSnippet $snippet): void
    {
        $this->em->persist($snippet);
        $this->em->flush($snippet);
        $this->em->getConfiguration()->getResultCache()->deleteItem('cs-'.sha1($snippet->getSlug()));
    }


}
