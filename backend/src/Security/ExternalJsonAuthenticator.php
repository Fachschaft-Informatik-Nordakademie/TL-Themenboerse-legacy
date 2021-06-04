<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserType;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class ExternalJsonAuthenticator extends AbstractJsonAuthenticator
{

    public function supports(Request $request): ?bool
    {
        return $this->supportsJsonLoginType($request, 'external');
    }

    public function supportsRegistration(Request $request): ?bool // TODO refactor
    {
        if (!$this->httpUtils->checkRequestPath($request, '/registration')) {
            return false;
        }
        if (false === strpos($request->getRequestFormat(), 'json') && false === strpos($request->getContentType(), 'json')) {
            return false;
        }
        $data = json_decode($request->getContent());
        if (!$data instanceof \stdClass) {
            throw new BadRequestHttpException('Invalid JSON.');
        }
        $email = $this->propertyAccessor->getValue($data, 'email');
        if (!\is_string($email)) {
            throw new BadRequestException('The key "email" must be provided.');
        }
        $password = $this->propertyAccessor->getValue($data, 'password');
        if (!\is_string($password)) {
            throw new BadRequestException('The key "password" must be provided.');
        }
        return true;
    }

    public function getCredentials(Request $request)
    {
        $data = $this->decodeRequestBody($request);

        $credentials = [
            'email' =>  $this->getStringFromRequestData($data, 'email'),
            'password' => $this->getStringFromRequestData($data, 'password'),
        ];

        return $credentials;
    }

    public function authenticate(Request $request): PassportInterface
    {
        try {
            $credentials = $this->getCredentials($request);
        } catch (BadRequestHttpException $e) {
            $request->setRequestFormat('json');

            throw $e;
        }

        $passport = new Passport(new UserBadge($credentials['email'], function ($email) {
            $user = $this->userRepository->loadUserByUsername($email);

            if (!$user instanceof User) {
                throw new AuthenticationServiceException('The user provider must return a User object.');
            }

            // Only allow external (non-ldap) users
            if ($user->getType() !== UserType::EXTERNAL) {
                return null;
            }

            return $user;
        }), new PasswordCredentials($credentials['password']));

        return $passport;
    }
}
