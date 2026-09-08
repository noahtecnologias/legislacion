<?php

namespace App\Utils;

class Constants
{

    const ROUTE_DEFAULT_ADMIN = "admin_home";
    const ROUTE_DEFAULT_USER = "user_home";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_OPERADOR = "ROLE_OPERADOR";
    const ROLE_ADMINISTRACION = "ROLE_ADMINISTRACION";
    const ROLE_USER = "ROLE_USER";          
        
    const TABLA_USUARIO = "usuarios";
    const URL_GENERAR_CLAVE = 'user/public/generar_clave/';
    const URL_RECUPERAR_CLAVE = 'user/public/recuperar_clave_update/';
}