<?php

namespace AppBundle\Entity;

use AppBundle\Event\EventCategories;
use AppBundle\Geocoder\GeoPointInterface;
use AppBundle\Utils\EmojisRemover;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 * @ORM\Table(
 *   name="events",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="event_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="event_slug_unique", columns="slug")
 *   }
 * )
 */
class Event implements GeoPointInterface
{
    const STATUS_SCHEDULED = 'SCHEDULED';
    const STATUS_CANCELLED = 'CANCELLED';

    const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CANCELLED,
    ];

    const ACTIVE_STATUSES = [
        self::STATUS_SCHEDULED,
    ];

    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityTimestampableTrait;

    /**
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * The event canonical name.
     *
     * @ORM\Column(length=100)
     */
    private $canonicalName;

    /**
     * @ORM\Column(length=130)
     * @Gedmo\Slug(fields={"beginAt", "canonicalName"}, dateFormat="Y-m-d")
     */
    private $slug;

    /**
     * @ORM\Column(length=5)
     */
    private $category;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $capacity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $beginAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $finishAt;

    /**
     * The adherent UUID who created this committee event.
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     */
    private $organizer;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $participantsCount;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     */
    private $committee;

    /**
     * @ORM\Column(length=20)
     */
    private $status;

    public function __construct(
        UuidInterface $uuid,
        Adherent $organizer,
        ?Committee $committee,
        string $name,
        string $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        string $slug = null,
        string $createdAt = null,
        int $participantsCount = 0
    ) {
        $this->uuid = $uuid;
        $this->organizer = $organizer;
        $this->committee = $committee;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = EmojisRemover::remove($description);
        $this->postAddress = $address;
        $this->capacity = $capacity;
        $this->participantsCount = $participantsCount;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->status = self::STATUS_SCHEDULED;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        string $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->capacity = $capacity;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = EmojisRemover::remove($description);

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    private static function canonicalize(string $name)
    {
        return mb_strtolower($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCategoryName(): string
    {
        return EventCategories::getCategoryName($this->category);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function getBeginAt(): \DateTimeInterface
    {
        return $this->beginAt;
    }

    public function getFinishAt(): \DateTimeInterface
    {
        return $this->finishAt;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getOrganizer(): ?Adherent
    {
        return $this->organizer;
    }

    public function getOrganizerName(): ?string
    {
        return $this->organizer ? $this->organizer->getFirstName() : '';
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getParticipantsCount(): int
    {
        return $this->participantsCount;
    }

    public function incrementParticipantsCount(int $increment = 1)
    {
        $this->participantsCount += $increment;
    }

    public function decrementParticipantsCount(int $increment = 1)
    {
        $this->participantsCount -= $increment;
    }

    public function updatePostAddress(PostAddress $postAddress)
    {
        if (!$this->postAddress->equals($postAddress)) {
            $this->postAddress = $postAddress;
        }
    }

    private function setName(string $name)
    {
        $this->name = EmojisRemover::remove($name);
        $this->canonicalName = static::canonicalize($name);
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function isFinished(): bool
    {
        return $this->finishAt < new \DateTime();
    }

    public function isFull(): bool
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->participantsCount >= $this->capacity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid status "%" given.', $status);
        }

        $this->status = $status;
    }

    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isCancelled(): bool
    {
        return self::STATUS_CANCELLED === $this->status;
    }
}
