<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use Exception;
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
            return new JsonResponse(['type' => 'failed', 'reason' => 'requested session not authenticated'], 401);
        }
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        // handle create requests
        if($request->isMethod('post')){
            $payload = json_decode($request->getContent(), TRUE);
            if($payload && !($payload['name'] && $payload['email'] && $payload['phone'])) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'required field(s) missing'], 422);
            }
            $contact = new Contact();
            $contact->setName($payload['name'])
                    ->setEmail($payload['email'])
                    ->setPhoneNumber($payload['phone'])
                    ->setUser($user);
            
            $em->persist($contact);
            
            try {
                $em->flush();
                return new JsonResponse(['type' => 'success', 'data' => $contact]);
            } catch (Exception $exc) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'Error while processing request'], 500);
            }
        } else {
            // TODO: handle search
            return new JsonResponse(['type' => 'success', 'data' => $user->getContacts()->toArray()]);
        }
    }
    
    /**
     * @Route("/api/contact/{id}", name="api.contact")
     */
    public function contactAction(Request $request, $id) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['type' => 'failed', 'reason' => 'requested session not authenticated'], 401);
        }
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        // request comes with empty id
        if (!$id) {
            return new JsonResponse(['type' => 'failed', 'reason' => 'contact id is required'], 400);
        }

        $contact = $this->getDoctrine()
                ->getRepository('AppBundle:Contact')
                ->find($id);
        
        // contact doesn't exist for the requested id
        if (!$contact) {
            return new JsonResponse(['type' => 'failed', 'reason' => 'Invalid contact requested'], 400);
        }
        
        // contact's user isn't the currently logged in user
        $contacUser = $contact->getUser();        
        if($contacUser->getId() != $user->getId()){
            return new JsonResponse(['type' => 'failed', 'reason' => 'Invalid contact requested'], 400);
        }

        // handle update requests
        if($request->isMethod('put')){
            // process request body as it comes as json string
            $payload = json_decode($request->getContent(), TRUE);
            if($payload && !($payload['name'] && $payload['email'] && $payload['phone'])) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'required field(s) missing'], 422);
            }
            
            $contact->setName($payload['name'])
                    ->setEmail($payload['email'])
                    ->setPhoneNumber($payload['phone']);
            
            $em->persist($contact);
            
            try {
                $em->flush();
                return new JsonResponse(['type' => 'success', 'data' => $contact]);
            } catch (Exception $exc) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'Error while processing request'], 500);
            }
        } else if ($request->isMethod('get')) { // handle get request
            return new JsonResponse(['type' => 'success', 'data' => $contact]);
        } else if ($request->isMethod('delete')) {  // handle get request
            $em->remove($contact);
            try {
                $em->flush();
                return new JsonResponse(['type' => 'success', 'data' => []]);
            } catch (Exception $exc) {
                return new JsonResponse(['type' => 'failed', 'reason' => 'Error while processing request'], 500);
            }
        }
    }

}