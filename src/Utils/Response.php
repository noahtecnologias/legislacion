<?php

namespace App\Utils;


class Response
{
    private $data;
    private $code;
    private $message;
    private $status;

    function __construct() {

    }

    function getData() {
        return $this->data;
    }

    function getCode() {
        return $this->code;
    }

    function getMessage() {
        return $this->message;
    }

    function getStatus() {
        return $this->status;
    }

    function setData($data) {
        $this->data = $data;
    }

    function setCode($code) {
        $this->code = $code;
    }

    function setMessage($message) {
        $this->message = $message;
    }

    function setStatus($statu) {
        $this->status = $statu;
    }

}