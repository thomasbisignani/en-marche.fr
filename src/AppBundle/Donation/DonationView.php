<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Ramsey\Uuid\UuidInterface;

final class DonationView
{
    private $uuid;
    private $amount;
    private $transaction;
    private $cardType;

    private function __construct(UuidInterface $uuid, float $amount, string $transaction = null, string $cardType = null)
    {
        $this->uuid = $uuid;
        $this->amount = $amount;
        $this->transaction = $transaction ?: '';
        $this->cardType = $cardType ?: '';
    }

    public static function createFromDonationRequest(DonationRequest $donation): self
    {
        return new self($donation->getUuid(), $donation->getAmount());
    }

    public static function createFromDonation(Donation $donation): self
    {
        return new self($donation->getUuid(), $donation->getAmountInEuros(), $donation->getTransactionId(), $donation->getCardType());
    }

    public function getUuid(): string
    {
        return $this->uuid->toString();
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getTransaction(): string
    {
        return $this->transaction;
    }

    public function getCardType(): string
    {
        return $this->cardType;
    }

    public function getDonorProfile(): string
    {
        if ($this->amount >= 5000) {
            return 'mecenes';
        }

        if ($this->amount >= 500) {
            return 'ambassadeurs';
        }

        if ($this->amount >= 50) {
            return 'bienfaiteurs';
        }

        return 'soutiens';
    }

    public function getCurrency(): string
    {
        return 'EUR';
    }
}
