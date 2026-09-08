<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response;


class Codes {
    const OK = Response::HTTP_OK;   //200
    const ERROR = Response::HTTP_INTERNAL_SERVER_ERROR; //400
    const CREATED = Response::HTTP_CREATED;  //201
    const UNAUTHORIZED = Response::HTTP_UNAUTHORIZED;
    const NOT_FOUND = Response::HTTP_NOT_FOUND;
    const CONFLICT = Response::HTTP_CONFLICT;
}