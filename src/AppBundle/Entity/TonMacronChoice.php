<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="ton_macron_choices", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="ton_macron_choices_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="ton_macron_choices_content_key_unique", columns="content_key")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TonMacronChoiceRepository")
 */
final class TonMacronChoice
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    const MAIL_INTRODUCTION_KEY = 'S00C01';
    const MAIL_CONCLUSION_KEY = 'S00C02';

    const STEP_FRIEND_PROFESSIONAL_POSITION = 'friend_professional_position';
    const STEP_FRIEND_PROJECT = 'friend_project';
    const STEP_FRIEND_INTERESTS = 'friend_interests';
    const STEP_SELF_REASONS = 'self_reasons';

    const STEPS = [
        self::STEP_FRIEND_PROFESSIONAL_POSITION,
        self::STEP_FRIEND_PROJECT,
        self::STEP_FRIEND_INTERESTS,
        self::STEP_SELF_REASONS,
    ];

    /**
     * @ORM\Column(type="smallint", length=1, options={"unsigned": true})
     */
    private $step;

    /**
     * @ORM\Column(length=30)
     */
    private $contentKey;

    /**
     * @ORM\Column(length=100)
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public function __construct(UuidInterface $uuid, string $step, string $contentKey, string $label, string $content)
    {
        $this->uuid = $uuid;
        $this->step = $step;
        $this->label = $label;
        $this->contentKey = $contentKey;
        $this->content = $content;
    }

    public function getStep(): string
    {
        return $this->step;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getContentKey(): string
    {
        return $this->contentKey;
    }
}
