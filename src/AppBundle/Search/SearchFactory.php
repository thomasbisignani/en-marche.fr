<?php

namespace AppBundle\Search;

use Cocur\Slugify\SlugifyInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchFactory
{
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function createFromRequest($type, Request $request)
    {
        return new Search(
            $type,
            explode('-', $this->slugify->slugify(trim((string) $request->query->get('term', '')))),
            $request->query->getInt('radius', 100),
            trim((string) $request->query->get('location', '')),
            $request->query->getInt('page', 1)
        );
    }
}
