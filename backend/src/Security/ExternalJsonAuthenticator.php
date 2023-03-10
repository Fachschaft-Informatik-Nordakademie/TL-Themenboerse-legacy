<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserType;
use App\ResponseCodes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
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
                throw AppAuthenticationException::withMessageCode(ResponseCodes::$INVALID_CREDENTIALS, 401);
            }

            // Only allow external (non-ldap) users
            if ($user->getType() !== UserType::EXTERNAL) {
                return null;
            }

            if(!$user->isEmailVerified()) {
                throw AppAuthenticationException::withMessageCode(ResponseCodes::$EMAIL_NOT_VERIFIED, 401);
            }

            return $user;
        }), new PasswordCredentials($credentials['password']));

        return $passport;
    }
}
