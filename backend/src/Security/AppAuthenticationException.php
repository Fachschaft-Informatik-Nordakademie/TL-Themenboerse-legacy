<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AppAuthenticationException extends AuthenticationException
{
    private string $message_code;
    private int $statusCode;

    public static function withMessageCode(string $responseCode, int $status): AppAuthenticationException
    {
        $ex = new AppAuthenticationException();
        $ex->message_code = $responseCode;
        $ex->statusCode = $status;
        return $ex;
    }

    public function getMessageCode(): string
    {
        return $this->message_code;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }


}
