<?php

namespace App\Security;

final class ModuloPermission
{
    public const USUARIOS = 'MODULO_USUARIOS';
    public const POTENCIALES = 'MODULO_POTENCIALES';
    public const POZO = 'MODULO_POZO';
    public const CONFIGURACION = 'MODULO_CONFIGURACION';
    public const CERTIFICADO = 'MODULO_CERTIFICADO';
    public const POTENCIALES_DIARIOS = 'MODULO_POTENCIALES_DIARIOS';
    public const PRODUCCION_DIARIA = 'MODULO_PRODUCCION_DIARIA';
    public const SAHARA = 'MODULO_SAHARA';

    private function __construct()
    {
    }
}