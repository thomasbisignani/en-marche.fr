<?php

namespace AppBundle\Controller\Api;

use AppBundle\Geocoder\Exception\GeocodingException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class SearchController extends Controller
{
    /**
     * @Route(
     *     "/search/{type}",
     *     requirements={"type"="committees|events"},
     *     name="app_api_search"
     * )
     * @Method("GET")
     */
    public function resultsAction(Request $request, $type)
    {
        $search = $this->get('app.search.factory')->createFromRequest($type, $request);
        $violations = $this->get('app.search.validator')->validate($search);

        if (!empty($violations)) {
            return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($this->get('app.search.engine')->runSearch($search));
    }
}
