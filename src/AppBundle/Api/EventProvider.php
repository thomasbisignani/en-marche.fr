<?php

namespace AppBundle\Api;

use AppBundle\Event\EventCategories;
use AppBundle\Exception\EventException;
use AppBundle\Repository\EventRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventProvider
{
    private $repository;
    private $urlGenerator;

    public function __construct(EventRepository $repository, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @throws EventException
     */
    public function getUpcomingEvents(string $type = null): array
    {
        if ($type && !EventCategories::getCategoryName($type)) {
            throw new EventException(sprintf('Given event category "%s" is invalid.', $type));
        }

        foreach ($this->repository->findUpcomingEvents($type) as $event) {
            if (!$event->isGeocoded()) {
                continue;
            }

            $data[] = [
                'uuid' => $event->getUuid()->toString(),
                'slug' => $event->getSlug(),
                'name' => $event->getName(),
                'url' => $this->urlGenerator->generate('app_committee_show_event', [
                    'uuid' => $event->getUuid()->toString(),
                    'slug' => $event->getSlug(),
                ]),
                'position' => [
                    'lat' => (float) $event->getLatitude(),
                    'lng' => (float) $event->getLongitude(),
                ],
            ];
        }

        return $data ?? [];
    }
}
