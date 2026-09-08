<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\UsuarioService;
use App\Utils\Codes;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class PublicController extends BaseController
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    #[Route('/public/restablecer/password', name: 'public_restablecer_password')]
    public function restablecerPassword()
    {
        $this->data['data'] = null;
        return $this->render(
            'security/reset_password.html.twig', $this->data
        );
    }

    #[Route('/public/ajax/restablecer/password', name: 'public_restablecer_password_email', methods: ['POST'])]
    public function restablecerPasswordEmail(
            Request $request, 
            \JMS\Serializer\SerializerInterface $serializer, 
            MailerInterface $mailer, 
            Environment $twig
            )
        {
        $email = $request->request->get('email');
        try {
            $usuario = $this->usuarioService->recuperarClaveEmail($mailer, $twig, $request, $email);
            return new JsonResponse(
                [
                    'message' => 'Correo electrónico enviado éxitosamente.',
                    'status' => 'success',
                    'code' => Codes::OK
                ], 
                JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'message' => 'Ocurrió un error inesperado',
                'error' => $e->getMessage(),
                'code' => Codes::ERROR
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }  
    }
}
