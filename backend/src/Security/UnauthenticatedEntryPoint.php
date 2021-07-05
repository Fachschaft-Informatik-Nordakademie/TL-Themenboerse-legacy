<?php

namespace App\Security;

use App\ResponseCodes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class UnauthenticatedEntryPoint implements AuthenticationEntryPointInterface
{

    public function start(Request $request, ?AuthenticationException $authException = null): ?Response
    {
        return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$UNAUTHENTICATED), Response::HTTP_UNAUTHORIZED);
    }
}
