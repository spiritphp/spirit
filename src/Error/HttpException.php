<?php

namespace Spirit\Error;

class HttpException extends \Exception {

    private $headers;

    public function __construct($statusCode, $message = null, array $headers = [])
    {
        $this->headers = $headers;

        parent::__construct($message, $statusCode);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

}