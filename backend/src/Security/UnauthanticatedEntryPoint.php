<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class UnauthanticatedEntryPoint implements AuthenticationEntryPointInterface
{

    public function start(Request $request, ?AuthenticationException $authException = null): ?Response
    {
        return new JsonResponse(['message' => 'You need to be authenticated to view this resource'], Response::HTTP_UNAUTHORIZED);
    }
}
