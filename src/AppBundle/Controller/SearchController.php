<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Intl\Intl;

class SearchController extends Controller
{
    /**
     * @Route("/evenements", defaults={"type"="events"}, name="app_search_events")
     * @Method("GET")
     */
    public function eventsAction()
    {
        return $this->render('search/events.html.twig', [
            'defaultLocation' => $this->getDefaultLocation(),
        ]);
    }

    /**
     * @Route("/comites", defaults={"type"="committees"}, name="app_search_committees")
     * @Method("GET")
     */
    public function committeesAction()
    {
        return $this->render('search/committees.html.twig', [
            'defaultLocation' => $this->getDefaultLocation(),
        ]);
    }

    private function getDefaultLocation()
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        if ($user instanceof Adherent && $user->getCityName() && $user->getCountry()) {
            return $user->getCityName().', '.Intl::getRegionBundle()->getCountryName($user->getCountry(), 'fr_FR');
        }

        return 'Paris, France';
    }
}
