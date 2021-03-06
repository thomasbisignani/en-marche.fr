<?php

namespace AppBundle\Entity;

use AppBundle\Utils\EmojisRemover;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="live_links")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LiveLinkRepository")
 */
class LiveLink
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     */
    private $link;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __toString()
    {
        return $this->title ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position): LiveLink
    {
        $this->position = $position;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): LiveLink
    {
        $this->title = EmojisRemover::remove($title);

        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link): LiveLink
    {
        $this->link = $link;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): LiveLink
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
