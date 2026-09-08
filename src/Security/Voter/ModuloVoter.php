<?php

namespace App\Security\Voter;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ModuloVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with($attribute, 'MODULO_')
            && $subject === null;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $usuario = $token->getUser();

        // No hay usuario autenticado
        if (!$usuario instanceof Usuario) {
            return false;
        }

        // El administrador tiene acceso a todos los módulos
        if (in_array('ROLE_ADMIN', $usuario->getRoles(), true)) {
            return true;
        }

        // Obtener el código del módulo
        $codigoModulo = substr($attribute, strlen('MODULO_'));

        // Buscar si el usuario tiene asignado ese módulo
        foreach ($usuario->getModulos() as $modulo) {
            if (
                $modulo->isActivo() &&
                $modulo->getCodigo() === $codigoModulo
            ) {
                return true;
            }
        }

        return false;
    }
}