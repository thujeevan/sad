<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityController extends Controller {

    /**
     * @Route("/session/login", name="login_route")
     */
    public function loginAction(Request $request) {
        // as this will be handled by client side routes
        return $this->redirect($this->generateUrl('index'));
    }
    
    /**
     * @Route("/session/isauthenticated", name="isauthenticated")
     */
    public function isauthenticatedAction(Request $request) {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(['type' => 'failed', 'reason' => 'requested session not authenticated'], 400);
        }
        $email = $this->getUser()->getEmail();
        return new JsonResponse(['type' => 'success', 'data' => ['username' => $email ]]);
    }

    /**
     * @Route("/session/signup", name="signup")
     */
    public function signupAction(Request $request) {
        $user = new User();
        $payload = json_decode($request->getContent(), TRUE);
        $plainPassword = isset($payload['password']) ? $payload['password'] : '';
        $email = isset($payload['email']) ? $payload['email'] : '';
        
        if (!($email && $plainPassword)) {
            return new JsonResponse([
                'type' => 'failed',
                'reason' => 'Invalid email or password, please double check and try again'
            ], 400);
        }

        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);

        $user->setPassword($encoded)->setUsername($email)->setEmail($email);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        
        // update security context with new token
        $token = new UsernamePasswordToken($user, null, 'default', $user->getRoles());
        $this->get('security.context')->setToken($token);

        return new JsonResponse([ 'type' => 'success' , 'data' => [ 'username' => $email ]]);
    }

    /**
     * @Route("/session/login_check", name="login_check")
     */
    public function loginCheckAction() {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

}
