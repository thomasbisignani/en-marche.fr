<?php

namespace AppBundle\TonMacron;

use Symfony\Component\Validator\Constraints as Assert;

final class InvitationProcessor
{
    const STATE_NEEDS_FRIEND_INFO = 'needs_friend_info';
    const STATE_NEEDS_FRIEND_PROJECT = 'needs_friend_project';
    const STATE_NEEDS_FRIEND_INTERESTS = 'needs_friend_interests';
    const STATE_NEEDS_SELF_REASONS = 'needs_self_reasons';
    const STATE_SUMMARY = 'summary';
    const STATE_SENT = 'sent';

    const STATES = [
        self::STATE_NEEDS_FRIEND_INFO,
        self::STATE_NEEDS_FRIEND_PROJECT,
        self::STATE_NEEDS_FRIEND_INTERESTS,
        self::STATE_NEEDS_SELF_REASONS,
        self::STATE_SUMMARY,
        self::STATE_SENT,
    ];

    const TRANSITION_FILL_INFO = 'fill_info';
    const TRANSITION_FILL_PROJECT = 'fill_project';
    const TRANSITION_FILL_INTERESTS = 'fill_interests';
    const TRANSITION_FILL_REASONS = 'fill_reasons';
    const TRANSITION_SEND = 'send';

    const TRANSITIONS = [
        self::TRANSITION_FILL_INFO,
        self::TRANSITION_FILL_PROJECT,
        self::TRANSITION_FILL_INTERESTS,
        self::TRANSITION_FILL_REASONS,
        self::TRANSITION_SEND,
    ];

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Type("string", groups={"fill_info"})
     * @Assert\Length(max=50, groups={"fill_info"})
     */
    public $friendFirstName = '';

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Type("integer", groups={"fill_info"})
     * @Assert\Range(min=17, groups={"fill_info"})
     */
    public $friendAge = 0;

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Choice(callback={"AppBundle\ValueObject\Genders", "all"}, strict=true, groups={"fill_info"})
     */
    public $friendGender;

    /**
     * @Assert\NotBlank(groups={"fill_info"})
     * @Assert\Type("AppBundle\Entity\TonMacronChoice", groups={"fill_info"})
     */
    public $friendPosition;

    /**
     * @Assert\NotBlank(groups={"fill_project"})
     * @Assert\Type("AppBundle\Entity\TonMacronChoice", groups={"fill_project"})
     */
    public $friendProject;

    /**
     * @Assert\Count(min=2, max=2, exactMessage="ton_macron.invitation.friend_interests.count", groups={"fill_interests"})
     * @Assert\All({
     *     @Assert\Type("AppBundle\Entity\TonMacronChoice")
     * }, groups={"fill_interests"})
     */
    public $friendInterests = [];

    /**
     * @Assert\Count(min=2, max=2, exactMessage="ton_macron.invitation.self_reasons.count", groups={"fill_reasons"})
     * @Assert\All({
     *     @Assert\Type("AppBundle\Entity\TonMacronChoice")
     * }, groups={"fill_reasons"})
     */
    public $selfReasons;

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=100, maxMessage="ton_macron.invitation.message_subject.max_length", groups={"send"})
     */
    public $messageSubject = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=5000, maxMessage="ton_macron.invitation.message_subject.max_length", groups={"send"})
     */
    public $messageContent = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=50, maxMessage="ton_macron.invitation.self_first_name.max_length", groups={"send"})
     */
    public $selfFirstName = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Type("string", groups={"send"})
     * @Assert\Length(max=50, maxMessage="ton_macron.invitation.self_last_name.max_length", groups={"send"})
     */
    public $selfLastName = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Email(checkHost=true, checkMX=true, groups={"send"})
     */
    public $selfEmail = '';

    /**
     * @Assert\NotBlank(groups={"send"})
     * @Assert\Email(checkHost=true, checkMX=true, groups={"send"})
     */
    public $friendEmail = '';

    /**
     * Handled by the workflow
     *
     * @var string
     */
    public $marking;
}
