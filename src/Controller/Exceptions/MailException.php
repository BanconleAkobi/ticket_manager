<?php

namespace App\Controller\Exceptions;

class MailException extends \Exception
{
    public function __construct($message = "Erreur lors de l'envoi du mail", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
