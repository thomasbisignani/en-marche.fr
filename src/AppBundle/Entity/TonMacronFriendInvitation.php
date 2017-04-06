<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="ton_macron_friend_invitations", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="ton_macron_friend_invitations_uuid_unique", columns="uuid")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TonMacronFriendInvitationRepository")
 */
final class TonMacronFriendInvitation
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\Column(length=50)
     */
    private $friendFirstName;

    /**
     * @ORM\Column(type="smallint", length=3, options={"unsigned": true})
     */
    private $friendAge;

    /**
     * @ORM\Column(length=6)
     */
    private $friendGender;

    /**
     * @ORM\Column(length=50)
     */
    private $friendPosition;

    /**
     * @ORM\Column(nullable=true)
     */
    private $friendEmailAddress;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $authorFirstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $authorLastName;

    /**
     * @ORM\Column(nullable=true)
     */
    private $authorEmailAddress;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    private $mailSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mailBody;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\TonMacronChoice", fetch="EAGER")
     * @ORM\JoinTable(
     *   name="ton_macron_friend_invitation_has_choices",
     *   joinColumns={
     *     @ORM\JoinColumn(name="invitation_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="choice_id", referencedColumnName="id")
     *   }
     * )
     */
    private $choices;

    public function __construct(UuidInterface $uuid, string $friendFirstName, string $friendAge, string $friendGender, string $createdAt = 'now')
    {
        $this->uuid = $uuid;
        $this->friendFirstName = $friendFirstName;
        $this->friendAge = $friendAge;
        $this->friendGender = $friendGender;
        $this->choices = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable($createdAt);
    }

    public function getFriendFirstName(): string
    {
        return $this->friendFirstName;
    }

    public function getFriendAge(): int
    {
        return $this->friendAge;
    }

    public function getFriendGender(): string
    {
        return $this->friendGender;
    }

    public function getFriendPosition(): ?string
    {
        return $this->friendPosition;
    }

    public function getFriendEmailAddress(): ?string
    {
        return $this->friendEmailAddress;
    }

    public function getAuthorFirstName(): ?string
    {
        return $this->authorFirstName;
    }

    public function getAuthorLastName(): ?string
    {
        return $this->authorLastName;
    }

    public function getAuthorEmailAddress(): ?string
    {
        return $this->authorEmailAddress;
    }

    public function getMailSubject(): ?string
    {
        return $this->mailSubject;
    }

    public function getMailBody(): ?string
    {
        return $this->mailBody;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }

    /**
     * @return TonMacronChoice[]
     */
    public function getArgumentsList(): array
    {
        $choices = [];
        foreach ([4, 1, 3, 2] as $step) {
            $choices = array_merge($choices, $this->getStepChoices($step));
        }

        return $choices;
    }

    /**
     * @return TonMacronChoice[]
     */
    private function getStepChoices(int $step): array
    {
        return $this
            ->choices
            ->filter(function (TonMacronChoice $choice) use ($step) {
                return $step == $choice->getStep();
            })
            ->toArray()
        ;
    }

    public function setFriendEmailAddress(string $emailAddress = null): void
    {
        $this->friendEmailAddress = $emailAddress;
    }

    public function setAuthor(string $firstName, string $lastName, string $emailAddress): void
    {
        $this->authorFirstName = $firstName;
        $this->authorLastName = $lastName;
        $this->authorEmailAddress = $emailAddress;
    }

    public function setAuthorFirstName(string $authorFirstName = null): void
    {
        $this->authorFirstName = $authorFirstName;
    }

    public function setAuthorLastName(string $authorLastName = null): void
    {
        $this->authorLastName = $authorLastName;
    }

    public function setAuthorEmailAddress(string $authorEmailAddress = null): void
    {
        $this->authorEmailAddress = $authorEmailAddress;
    }

    public function setMailSubject(string $subject = null): void
    {
        $this->mailSubject = $subject;
    }

    public function setMailBody(string $content = null): void
    {
        $this->mailBody = $content;
    }

    public function addChoice(TonMacronChoice $choice): void
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
        }
    }
}
