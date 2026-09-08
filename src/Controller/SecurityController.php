<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class SecurityController extends BaseController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/loginSuccess', name: 'login_success')]
    public function loginSuccess(Security $security)
    {
        $user = $security->getUser();

        //Si el usuario no esta activo no se puede loguear
        /*
        if (!$user->getEstado()) {
            return $this->redirectToRoute("logout");
        }
        */
        $isAdmin = $this->isRolAdmin($user->getRoles());
        if ($isAdmin) {
            return $this->redirectToRoute("admin_home");
        }
        $isOperador = $this->isRolOperador($user->getRoles());
        if ($isOperador) {
            return $this->redirectToRoute("operador_home");
        }
        $isAdministracion = $this->isRolAdministracion($user->getRoles());
        if ($isAdministracion) {
            return $this->redirectToRoute("administracion_home");
        }
    }
}
