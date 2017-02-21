<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Committee;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Search\Search;
use AppBundle\Search\SearchEngine;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;

class CommitteeRepository extends EntityRepository
{
    use NearbyTrait;

    const ONLY_APPROVED = 1;
    const INCLUDE_UNAPPROVED = 2;

    /**
     * Finds a Committee instance by its unique canonical name.
     *
     * @param string $name
     *
     * @return Committee|null
     */
    public function findOneByName(string $name): ?Committee
    {
        $canonicalName = Committee::canonicalize($name);

        return $this->findOneBy(['canonicalName' => $canonicalName]);
    }

    /**
     * Finds a Committee instance by its unique UUID.
     *
     * @param string $uuid
     *
     * @return Committee|null
     */
    public function findOneByUuid(string $uuid): ?Committee
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    /**
     * Finds Committee instances by their unique UUIDs.
     *
     * @param string[] $uuids
     *
     * @return Committee[]
     */
    public function findByUuid(array $uuids)
    {
        return $this->findBy(['uuid' => $uuids]);
    }

    /**
     * Finds approved Committee instances.
     *
     * @return Committee[]
     */
    public function findApprovedCommittees()
    {
        return $this->findBy(['status' => Committee::APPROVED]);
    }

    /**
     * Returns whether or not the given adherent has "waiting for approval"
     * committees.
     *
     * @param string $adherentUuid
     *
     * @return bool
     */
    public function hasWaitingForApprovalCommittees(string $adherentUuid): bool
    {
        $adherentUuid = Uuid::fromString($adherentUuid);

        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.createdBy = :adherent')
            ->andWhere('c.status = :status')
            ->setParameter('adherent', (string) $adherentUuid)
            ->setParameter('status', Committee::PENDING)
            ->getQuery()
        ;

        return (int) $query->getSingleScalarResult() >= 1;
    }

    /**
     * Returns the most recent created Committee.
     *
     * @return Committee|null
     */
    public function findMostRecentCommittee(): ?Committee
    {
        $query = $this
            ->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
        ;

        return $query->getOneOrNullResult();
    }

    /**
     * @param int         $count
     * @param Coordinates $coordinates
     *
     * @return Committee[]
     */
    public function findNearbyCommittees(int $count, Coordinates $coordinates)
    {
        $query = $this
            ->createNearbyQueryBuilder($coordinates)
            ->where('n.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->setMaxResults($count)
            ->getQuery()
        ;

        return $query->getResult();
    }

    /**
     * Returns the total number of approved committees.
     *
     * @return int
     */
    public function countApprovedCommittees(): int
    {
        $query = $this
            ->createQueryBuilder('c')
            ->select('COUNT(c.uuid)')
            ->where('c.status = :status')
            ->setParameter('status', Committee::APPROVED)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    public function findCommittees(array $uuids, int $statusFilter = self::ONLY_APPROVED, int $limit = 0): array
    {
        if (!$uuids) {
            return [];
        }

        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->in('c.uuid', $uuids))
            ->orderBy('c.membersCounts', 'DESC')
        ;

        if (self::ONLY_APPROVED === $statusFilter) {
            $qb
                ->andWhere('c.status = :status')
                ->setParameter('status', Committee::APPROVED)
            ;
        }

        if ($limit >= 1) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Search $search
     * @return Committee[]
     */
    public function findSearchResults(Search $search): array
    {
        $qb = $this->createSearchQueryBuilder($search);
        $qb ->select('n', $this->getNearbyExpression().' AS HIDDEN distance')
            ->orderBy('distance', 'ASC')
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
            ->where('n.status = :status')
            ->andWhere($this->getNearbyExpression().' < :distance_max')
            ->setParameter('distance_max', $search->getRadius())
            ->setParameter('status', Committee::APPROVED)
            ->setParameter('latitude', $search->getResolvedCoordinates()->getLatitude())
            ->setParameter('longitude', $search->getResolvedCoordinates()->getLongitude());

        foreach($search->getKeywords() as $i => $keyword) {
            $qb->andWhere($qb->expr()->like('LOWER(n.name)', ':keyword'.$i));
            $qb->setParameter('keyword'.$i, '%'.$keyword.'%');
        }

        return $qb;
    }
}
