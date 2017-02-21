<?php

namespace AppBundle\Search;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Geocoder\Exception\GeocodingException;
use AppBundle\Geocoder\GeocoderInterface;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchEngine
{
    const CACHE_PREFIX = 'search_';
    const MAX_RESULTS = 30;

    private $cache;
    private $geocoder;
    private $urlGenerator;
    private $committeeRepository;
    private $eventRepository;

    public function __construct(
        CacheItemPoolInterface $cache,
        GeocoderInterface $geocoder,
        UrlGeneratorInterface $urlGenerator,
        CommitteeRepository $committeeRepository,
        EventRepository $eventRepository
    ) {
        $this->cache = $cache;
        $this->geocoder = $geocoder;
        $this->urlGenerator = $urlGenerator;
        $this->committeeRepository = $committeeRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Search $search
     * @return array
     */
    public function runSearch(Search $search): array
    {
        if (!$this->resolveLocationCoordinates($search)) {
            return $this->formatResponse($search, [], 0);
        }

        if ($search->getType() === Search::TYPE_COMMITTEES) {
            return $this->searchCommittees($search);
        }

        return $this->searchEvents($search);
    }

    /**
     * @param Search $search
     * @return bool
     */
    private function resolveLocationCoordinates(Search $search): bool
    {
        $cacheItem = $this->cache->getItem(self::CACHE_PREFIX.md5(mb_strtolower($search->getLocation())));

        if (!$cacheItem->isHit()) {
            try {
                $resolved = $this->geocoder->geocode($search->getLocation());
            } catch (GeocodingException $e) {
                return false;
            }

            $cacheItem->set($resolved);
            $this->cache->save($cacheItem);
        }

        $search->setResolvedCoordinates($cacheItem->get());

        return true;
    }

    /**
     * @param Search $search
     * @return array
     */
    private function searchCommittees(Search $search): array
    {
        return $this->formatResponse(
            $search,
            array_map([$this, 'formatCommittee'], $this->committeeRepository->findSearchResults($search)),
            $this->committeeRepository->countSearchResults($search)
        );
    }

    /**
     * @param Search $search
     * @return array
     */
    private function searchEvents(Search $search): array
    {
        return $this->formatResponse(
            $search,
            array_map([$this, 'formatEvent'], $this->eventRepository->findSearchResults($search)),
            $this->eventRepository->countSearchResults($search)
        );
    }

    private function formatEvent(Event $event)
    {
        return [
            'uuid' => $event->getUuid()->toString(),
            'slug' => $event->getSlug(),
            'name' => $event->getName(),
            'committee' => $event->getCommittee() ? $event->getCommittee()->getName() : null,
            'city' => $event->getCityName(),
            'participantsCount' => $event->getParticipantsCount(),
            'beginAt' => $event->getBeginAt() ? $event->getBeginAt()->format(\DateTime::ATOM) : null,
            'location' => [
                'address' => $event->getInlineFormattedAddress(),
                'latitude' => $event->getLatitude(),
                'longitude' => $event->getLongitude(),
            ],
        ];
    }

    private function formatCommittee(Committee $committee)
    {
        return [
            'uuid' => $committee->getUuid()->toString(),
            'slug' => $committee->getSlug(),
            'name' => $committee->getName(),
            'city' => $committee->getCityName(),
            'membersCount' => $committee->getMembersCount(),
        ];
    }

    private function formatResponse(Search $search, array $results, int $totalCount)
    {
        $next = null;

        if ($search->getPage() * self::MAX_RESULTS < $totalCount) {
            $next = $this->urlGenerator->generate('app_api_search', [
                'type' => $search->getType(),
                'term' => implode('-', $search->getKeywords()),
                'radius' => $search->getRadius(),
                'location' => $search->getLocation(),
                'page' => $search->getPage() + 1,
            ]);
        }

        return [
            'results' => $results,
            'pager' => [
                'count' => $totalCount,
                'next' => $next,
            ],
        ];
    }
}
