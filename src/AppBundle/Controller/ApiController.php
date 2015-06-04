<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller {

    /**
     * @Route("/api/contacts", name="api.contacts")
     */
    public function contactsAction(Request $request) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['type' => 'failed', 'reason' => 'requested session not authenticated'], 400);
        }
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        // handle create requests
        if($request->isMethod('post')){
            $payload = json_decode($request->getContent(), TRUE);
            if(!($payload['name'] && $payload['email'] && $payload['phone'])) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'required field(s) missing'], 422);
            }
            $contact = new Contact();
            $contact->setName($payload['name'])
                    ->setEmail($payload['email'])
                    ->setPhoneNumber($payload['phone'])
                    ->setUser($user);
            
            $em->persist($contact);
            $em->flush();
            
            return new JsonResponse(['type' => 'success', 'data' => $contact]);
        } else {
            // TODO: handle search
            return new JsonResponse(['type' => 'success', 'data' => $user->getContacts()->toArray()]);
        }
    }

}