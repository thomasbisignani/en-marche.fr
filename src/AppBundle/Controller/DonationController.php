<?php

namespace AppBundle\Controller;

use AppBundle\Donation\DonationView;
use AppBundle\Entity\Donation;
use AppBundle\Form\DonationRequestType;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/don")
 */
class DonationController extends Controller
{
    /**
     * @Route(name="donation_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        return $this->render('donation/index.html.twig', [
            'amount' => (float) $request->query->get('montant', 50),
        ]);
    }

    /**
     * @Route("/coordonnees", name="donation_informations")
     * @Method({"GET", "POST"})
     */
    public function informationsAction(Request $request)
    {
        if (!$amount = (float) $request->query->get('montant')) {
            return $this->redirectToRoute('donation_index');
        }

        $factory = $this->get('app.donation_request.factory');
        $donationRequest = $factory->createFromRequest($request, $amount, $this->getUser());
        $form = $this->createForm(DonationRequestType::class, $donationRequest, ['locale' => $request->getLocale()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.donation_request.handler')->handle($donationRequest, $request->getClientIp());

            return $this->redirectToRoute('donation_pay', [
                'uuid' => $donationRequest->getUuid()->toString(),
            ]);
        }

        return $this->render('donation/informations.html.twig', [
            'form' => $form->createView(),
            'donation' => DonationView::createFromDonationRequest($donationRequest),
        ]);
    }

    /**
     * @Route("/{uuid}/paiement", name="donation_pay", requirements={"uuid"="%pattern_uuid%"})
     * @Method("GET")
     */
    public function payboxAction(Donation $donation)
    {
        if ($donation->isFinished()) {
            $this->get('app.membership_utils')->clearRegisteringDonation();

            return $this->redirectToRoute('donation_index');
        }

        $paybox = $this->get('app.donation.form_factory')->createPayboxFormForDonation($donation);

        return $this->render('donation/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback", name="donation_callback")
     * @Method("GET")
     */
    public function callbackAction(Request $request)
    {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('donation_index');
        }

        return $this->get('app.donation.transaction_callback_handler')->handle($id, $request);
    }

    /**
     * @Route("/{uuid}/{status}", name="donation_result", requirements={"status"="effectue|erreur", "uuid"="%pattern_uuid%"})
     * @Method("GET")
     */
    public function resultAction(Request $request, Donation $donation)
    {
        $parameters = [
            'montant' => $donation->getAmount() / 100,
            'ge' => $donation->getGender(),
            'ln' => $donation->getLastName(),
            'fn' => $donation->getFirstName(),
            'em' => urlencode($donation->getEmailAddress()),
            'co' => $donation->getCountry(),
            'pc' => $donation->getPostalCode(),
            'ci' => $donation->getCityName(),
            'ad' => urlencode($donation->getAddress()),
        ];

        if ($donation->getPhone()) {
            $parameters['phc'] = $donation->getPhone()->getCountryCode();
            $parameters['phn'] = $donation->getPhone()->getNationalNumber();
        }

        return $this->render('donation/result.html.twig', [
            'successful' => $donation->isSuccessful(),
            'error_code' => $request->query->get('code'),
            'donation' => DonationView::createFromDonation($donation),
            'retry_url' => $this->generateUrl('donation_informations', $parameters),
            'is_in_subscription_process' => $this->get('app.membership_utils')->isInSubscriptionProcess(),
        ]);
    }
}
