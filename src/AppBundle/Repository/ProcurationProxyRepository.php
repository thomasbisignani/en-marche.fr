<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProcurationProxyRepository extends EntityRepository
{
    /**
     * @param Adherent $procurationManager
     *
     * @return ProcurationProxy[]
     */
    public function findManagedBy(Adherent $procurationManager): array
    {
        if (!$procurationManager->isProcurationManager()) {
            return [];
        }

        $qb = $this->createQueryBuilder('pp')
            ->addOrderBy('pp.createdAt', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC');

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return $qb->getQuery()->getResult();
    }

    public function isManagedBy(Adherent $procurationManager, ProcurationProxy $proxy): bool
    {
        if (!$procurationManager->isProcurationManager()) {
            return false;
        }

        $qb = $this->createQueryBuilder('pp')
            ->select('COUNT(pp)')
            ->where('pp.id = :id')
            ->setParameter('id', $proxy->getId());

        $this->addAndWhereManagedBy($qb, $procurationManager);

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param ProcurationRequest $procurationRequest
     *
     * @return array
     */
    public function findMatchingProxies(ProcurationRequest $procurationRequest)
    {
        $qb = $this->createQueryBuilder('pp');
        $qb->select('pp AS data', $this->createMatchingScore($qb, $procurationRequest).' + pp.reliability AS score')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    'pp.voteCountry = \'FR\'',
                    'SUBSTRING(pp.votePostalCode, 1, 2) = SUBSTRING(:votePostalCode, 1, 2)',
                    'pp.voteCityName = :voteCityName'
                ),
                $qb->expr()->andX(
                    'pp.voteCountry != \'FR\'',
                    'pp.voteCountry = :voteCountry'
                )
            ))
            ->andWhere('pp.foundRequest IS NULL')
            ->andWhere('pp.disabled = 0')
            ->setParameter('votePostalCode', $procurationRequest->getVotePostalCode())
            ->setParameter('voteCityName', $procurationRequest->getVoteCityName())
            ->setParameter('voteCountry', $procurationRequest->getVoteCountry())
            ->orderBy('score', 'DESC')
            ->addOrderBy('pp.lastName', 'ASC');

        if ($procurationRequest->getElectionPresidentialFirstRound()) {
            $qb->andWhere('pp.electionPresidentialFirstRound = TRUE');
        }

        if ($procurationRequest->getElectionPresidentialSecondRound()) {
            $qb->andWhere('pp.electionPresidentialSecondRound = TRUE');
        }

        if ($procurationRequest->getElectionLegislativeFirstRound()) {
            $qb->andWhere('pp.electionLegislativeFirstRound = TRUE');
        }

        if ($procurationRequest->getElectionLegislativeSecondRound()) {
            $qb->andWhere('pp.electionLegislativeSecondRound = TRUE');
        }

        return $qb->getQuery()->getResult();
    }

    private function addAndWhereManagedBy(QueryBuilder $qb, Adherent $procurationManager)
    {
        $codesFilter = $qb->expr()->orX();

        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codesFilter->add(
                    $qb->expr()->andX(
                        'pp.voteCountry = \'FR\'',
                        $qb->expr()->like('pp.votePostalCode', ':code'.$key)
                    )
                );

                $qb->setParameter('code'.$key, $code.'%');
            } else {
                // Country
                $codesFilter->add($qb->expr()->eq('pp.voteCountry', ':code'.$key));
                $qb->setParameter('code'.$key, $code);
            }
        }

        $qb->andWhere($codesFilter);
    }

    private function createMatchingScore(QueryBuilder $qb, ProcurationRequest $procurationRequest)
    {
        $elections = [
            'electionPresidentialFirstRound',
            'electionPresidentialSecondRound',
            'electionLegislativeFirstRound',
            'electionLegislativeSecondRound',
        ];

        $score = [];

        foreach ($elections as $election) {
            $score[] = sprintf('(CASE WHEN (pp.%s = :%s) THEN 1 ELSE 0 END)', $election, $election);
        }

        $qb->setParameter('electionPresidentialFirstRound', $procurationRequest->getElectionPresidentialFirstRound());
        $qb->setParameter('electionPresidentialSecondRound', $procurationRequest->getElectionPresidentialSecondRound());
        $qb->setParameter('electionLegislativeFirstRound', $procurationRequest->getElectionLegislativeFirstRound());
        $qb->setParameter('electionLegislativeSecondRound', $procurationRequest->getElectionLegislativeSecondRound());

        return implode(' + ', $score);
    }
}
