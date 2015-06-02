<?php

namespace AppBundle\security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {

    private $router;
    private $session;

    /**
     * Constructor
     *
     * @param 	RouterInterface $router
     * @param 	Session $session
     */
    public function __construct(RouterInterface $router, Session $session) {
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * onAuthenticationSuccess
     *
     * @param 	Request $request
     * @param 	TokenInterface $token
     * @return 	Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        // if AJAX login
        if ($request->isXmlHttpRequest()) {
            $array = array('type' => 'success', 'data' => [ 'username' => $token->getUsername()]); // data to return via JSON
            return new JsonResponse($array);            
        } else {// if form login 
            if ($this->session->get('_security.main.target_path')) {
                $url = $this->session->get('_security.main.target_path');
            } else {
                $url = $this->router->generate('index');
            } // end if
            return new RedirectResponse($url);
        }
    }

    /**
     * onAuthenticationFailure
     *
     * @param 	Request $request
     * @param 	AuthenticationException $exception
     * @return 	Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        // if AJAX login
        if ($request->isXmlHttpRequest()) {
            $array = array('type' => 'failed', 'reason' => $exception->getMessage()); // data to return via JSON
            return new JsonResponse($array, 401);            
        } else { // if form login 
            // set authentication exception to session
            $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);
            return new RedirectResponse($this->router->generate('login_route'));
        }
    }

    /**
     * function to call on logout success
     * 
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function onLogoutSuccess(Request $request) {
        if ($request->isXmlHttpRequest()) {
            $array = array('type' => 'success', 'data' => ''); // data to return via JSON
            return new JsonResponse($array);           
        } else { // if form login 
            return new RedirectResponse($this->router->generate('login_route')); 
        }
    }

}
