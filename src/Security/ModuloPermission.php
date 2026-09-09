<?php

namespace App\Security;

final class ModuloPermission
{
    public const USUARIOS = 'MODULO_USUARIOS';
    public const VOTOS = 'MODULO_VOTOS';
    public const COMPRAS = 'MODULO_COMPRAS';

    private function __construct()
    {
    }
}