<?php

namespace AppBundle\Repository;

use AppBundle\Entity\TonMacronChoice;
use Doctrine\ORM\EntityRepository;

class TonMacronChoiceRepository extends EntityRepository
{
    public function findMailIntroduction(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_INTRODUCTION_KEY]);
    }

    public function findMailConclusion(): ?TonMacronChoice
    {
        return $this->findOneBy(['contentKey' => TonMacronChoice::MAIL_CONCLUSION_KEY]);
    }
}
