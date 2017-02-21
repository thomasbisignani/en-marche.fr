<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Search\Search;
use AppBundle\Search\SearchEngine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends EntityRepository
{
    use NearbyTrait;

    public function findOneBySlug(string $slug): ?Event
    {
        $query = $this
            ->createQueryBuilder('e')
            ->select('e, c, o')
            ->leftJoin('e.committee', 'c')
            ->leftJoin('e.organizer', 'o')
            ->where('e.slug = :slug')
            ->andWhere('c.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    public function findMostRecentEvent(): ?Event
    {
        $query = $this
            ->createQueryBuilder('ce')
            ->orderBy('ce.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param Search $search
     * @return Committee[]
     */
    public function findSearchResults(Search $search): array
    {
        $qb = $this->createSearchQueryBuilder($search);
        $qb ->select('n')
            ->orderBy('n.beginAt', 'ASC')
            ->setFirstResult(($search->getPage() - 1) * SearchEngine::MAX_RESULTS)
            ->setMaxResults(SearchEngine::MAX_RESULTS);

        return $qb->getQuery()->getResult();
    }

    public function countSearchResults(Search $search): int
    {
        $qb = $this->createSearchQueryBuilder($search);
        $qb->select('COUNT(n) as nb');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create a query builder suited to the API search.
     */
    private function createSearchQueryBuilder(Search $search): QueryBuilder
    {
        if (!$search->getResolvedCoordinates()) {
            throw new \LogicException('The search location is not resolved and cannot be used.');
        }

        $qb = $this
            ->createQueryBuilder('n')
            ->where('n.beginAt > :now')
            ->andWhere($this->getNearbyExpression().' < :distance_max')
            ->setParameter('distance_max', $search->getRadius())
            ->setParameter('now', new \DateTime())
            ->setParameter('latitude', $search->getResolvedCoordinates()->getLatitude())
            ->setParameter('longitude', $search->getResolvedCoordinates()->getLongitude());

        foreach($search->getKeywords() as $i => $keyword) {
            $qb->andWhere($qb->expr()->like('LOWER(n.name)', ':keyword'.$i));
            $qb->setParameter('keyword'.$i, '%'.$keyword.'%');
        }

        return $qb;
    }
}
