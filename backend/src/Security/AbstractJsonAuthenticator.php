<?php

namespace App\Security;

use App\Repository\UserRepository;
use App\ResponseCodes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\HttpUtils;

abstract class AbstractJsonAuthenticator extends AbstractAuthenticator
{
    protected HttpUtils $httpUtils;
    protected PropertyAccessorInterface $propertyAccessor;
    protected EntityManagerInterface $em;
    protected UserRepository $userRepository;
    protected Ldap $ldap;
    protected ContainerBagInterface $params;

    public function __construct(
        HttpUtils $httpUtils,
        PropertyAccessorInterface $propertyAccessor,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        Ldap $ldap,
        ContainerBagInterface $params
    ) {
        $this->httpUtils = $httpUtils;
        $this->propertyAccessor = $propertyAccessor;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->ldap = $ldap;
        $this->params = $params;
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = $exception->getMessage();
        if ($exception instanceof BadCredentialsException) {
            return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$INVALID_CREDENTIALS), Response::HTTP_UNAUTHORIZED);
        }

        if($exception instanceof AppAuthenticationException) {
            return new JsonResponse(ResponseCodes::makeResponse($exception->getMessageCode()), $exception->getStatusCode());
        }

        return new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$UNKNOWN_ERROR, ['message' => $message]), Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    protected function supportsJsonLoginType(Request $request, string $authenticatorType): bool
    {
        if (!$this->httpUtils->checkRequestPath($request, '/login')) {
            return false;
        }

        if (false === strpos($request->getRequestFormat(), 'json') && false === strpos($request->getContentType(), 'json')) {
            return false;
        }

        $data = json_decode($request->getContent());

        if (!$data instanceof \stdClass) {
            throw AppAuthenticationException::withMessageCode(ResponseCodes::$INVALID_JSON, 400);
        }

        $type = $this->propertyAccessor->getValue($data, 'type');
        if (!\is_string($type)) {
            throw AppAuthenticationException::withMessageCode(ResponseCodes::$VALIDATION_FAILED, 400);
        }

        return $authenticatorType === $type;
    }

    protected function decodeRequestBody(Request $request)
    {
        $data = json_decode($request->getContent());
        if (!$data instanceof \stdClass) {
            throw AppAuthenticationException::withMessageCode(ResponseCodes::$INVALID_JSON, 400);
        }

        return $data;
    }

    protected function getStringFromRequestData(\stdClass $data, string $propertyName): string
    {
        try {
            $value = $this->propertyAccessor->getValue($data, $propertyName);

            if (!\is_string($value)) {
                throw AppAuthenticationException::withMessageCode(ResponseCodes::$VALIDATION_FAILED, 400);
            }

            if (strlen(trim($value)) === 0) {
                throw AppAuthenticationException::withMessageCode(ResponseCodes::$VALIDATION_FAILED, 400);
            }
        } catch (AccessException $e) {
            throw AppAuthenticationException::withMessageCode(ResponseCodes::$VALIDATION_FAILED, 400);
        }

        return $value;
    }
}
