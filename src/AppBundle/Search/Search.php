<?php

namespace AppBundle\Search;

use AppBundle\Geocoder\Coordinates;
use Symfony\Component\Validator\Constraints as Assert;

class Search
{
    const TYPE_COMMITTEES = 'committees';
    const TYPE_EVENTS = 'events';

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $keywords;

    /**
     * @var int
     *
     * @Assert\Choice(callback="getRadii")
     */
    private $radius;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $location;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=1)
     */
    private $page;

    /**
     * @var Coordinates
     */
    private $coordinates;

    public function __construct(string $type, array $keywords, int $radius, string $location, int $page)
    {
        if (!in_array($type, [self::TYPE_COMMITTEES, self::TYPE_EVENTS])) {
            throw new \InvalidArgumentException('Invalid type '.$type);
        }

        $this->type = $type;
        $this->keywords = $keywords;
        $this->radius = $radius;
        $this->location = $location;
        $this->page = $page;
    }

    public static function getRadii()
    {
        return [ 5, 10, 25, 50, 100, 150 ];
    }

    public function setResolvedCoordinates(Coordinates $coordinates)
    {
        if (!$this->coordinates) {
            $this->coordinates = $coordinates;
        }
    }

    public function getResolvedCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
