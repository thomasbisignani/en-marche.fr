<?php

namespace AppBundle\Mailjet\Message;

use AppBundle\Entity\Adherent;
use Ramsey\Uuid\Uuid;

final class AdherentResetPasswordMessage extends MailjetMessage
{
    public static function createFromAdherent(Adherent $adherent, string $resetPasswordLink): self
    {
        return new static(
            Uuid::uuid4(),
            '54686',
            $adherent->getEmailAddress(),
            $adherent->getFullName(),
            'Réinitialisez votre mot de passe',
            [
                'target_firstname' => self::escape($adherent->getFirstName()),
                'reset_link' => $resetPasswordLink,
            ]
        );
    }
}
