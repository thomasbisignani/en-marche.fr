<?php

namespace AppBundle\Collection;

use AppBundle\Entity\CommitteeMembership;
use Doctrine\Common\Collections\ArrayCollection;

class CommitteeMembershipCollection extends ArrayCollection
{
    const INCLUDE_SUPERVISORS = 1;
    const EXCLUDE_SUPERVISORS = 2;

    public function getAdherentUuids(): array
    {
        return array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->getValues()
        );
    }

    public function getCommitteeUuids(): array
    {
        return array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getCommitteeUuid();
            },
            $this->getValues()
        );
    }

    public function countCommitteeHostMemberships(): int
    {
        return count($this->filter(function (CommitteeMembership $membership) {
            return $membership->canHostCommittee();
        }));
    }

    public function getCommitteeHostMemberships(int $withSupervisors = self::INCLUDE_SUPERVISORS): self
    {
        if (self::EXCLUDE_SUPERVISORS === $withSupervisors) {
            return $this->filter(function (CommitteeMembership $membership) {
                return $membership->isHostMember();
            });
        }

        // Supervised committees must have top priority in the list.
        $committees = $this->filter(function (CommitteeMembership $membership) {
            return $membership->isSupervisor();
        });

        // Hosted committees must have medium priority in the list.
        $committees->merge($this->filter(function (CommitteeMembership $membership) {
            return $membership->isHostMember();
        }));

        return $committees;
    }

    public function getCommitteeFollowerMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isFollower();
        });
    }

    public function getCommitteeSupervisorMemberships(): self
    {
        return $this->filter(function (CommitteeMembership $membership) {
            return $membership->isSupervisor();
        });
    }

    private function merge(self $other): void
    {
        foreach ($other as $element) {
            if (!$this->contains($element)) {
                $this->add($element);
            }
        }
    }
}
