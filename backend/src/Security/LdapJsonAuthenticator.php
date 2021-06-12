<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserType;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class LdapJsonAuthenticator extends AbstractJsonAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return $this->supportsJsonLoginType($request, 'ldap');
    }

    public function getCredentials(Request $request)
    {
        $data = $this->decodeRequestBody($request);

        $credentials = [
            'username' =>  $this->getStringFromRequestData($data, 'username'),
            'password' => $this->getStringFromRequestData($data, 'password'),
        ];

        return $credentials;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $bindDn = $this->params->get('app.ldap_bind_dn');
        $bindPassword = $this->params->get('app.ldap_bind_password');
        $userQuery = $this->params->get('app.ldap_user_query');
        $userBaseDn = $this->params->get('app.ldap_user_base_dn');

        try {
            $credentials = $this->getCredentials($request);
        } catch (BadRequestHttpException $e) {
            $request->setRequestFormat('json');

            throw $e;
        }

        // Connect with readonly account to lookup provided username 
        $this->ldap->bind($bindDn, $bindPassword);
        $username = $this->ldap->escape($credentials['username'], '', LdapInterface::ESCAPE_FILTER);
        $query = str_replace('{username}', $username, $userQuery);
        $result = $this->ldap->query($userBaseDn, $query)->execute()->toArray();

        // Only continue when exactly 1 user is found
        if (count($result) !== 1) {
            throw new BadCredentialsException();
        }

        // Import the user into db
        $user = $this->importUserIntoDB($result[0]);

        $passport = new Passport(new UserBadge($user->getLdapUsername(), function ($username) {
            $user = $this->userRepository->loadUserByLdapUsername($username);

            if (!$user instanceof User) {
                throw new AuthenticationServiceException('The user provider must return a User object.');
            }

            // Only allow LDAP users
            if ($user->getType() !== UserType::LDAP) {
                return null;
            }

            return $user;
        }), new CustomCredentials(
            function ($credentials, User $user) {
                try {
                    $this->ldap->bind($user->getLdapDn(), $credentials);
                } catch (ConnectionException $e) {
                    return false;
                }
                return true;
            },
            $credentials['password']
        ));

        return $passport;
    }

    private function importUserIntoDB(Entry $entry): User
    {
        $mailAttribute = $this->params->get('app.ldap_attribute_mail');
        $usernameAttribute = $this->params->get('app.ldap_attribute_username');
        $firstNameAttribute = $this->params->get('app.ldap_attribute_first_name');
        $lastNameAttribute = $this->params->get('app.ldap_attribute_last_name');

        $emailValue = $entry->getAttribute($mailAttribute);
        if ($emailValue === null || count($emailValue) !== 1) {
            throw new LogicException("User has multiple stored email addresses");
        }

        $usernameValue = $entry->getAttribute($usernameAttribute);
        if ($usernameValue === null || count($usernameValue) !== 1) {
            throw new LogicException("User has multiple stored usernames");
        }

        $firstNameValue = $entry->getAttribute($firstNameAttribute);

        $lastNameValue = $entry->getAttribute($lastNameAttribute);

        $user = $this->userRepository->loadUserByLdapUsername($usernameValue[0]);

        if ($user === null) {
            $user = new User();
        } else if ($user->getType() !== UserType::LDAP) {
            throw new LogicException("User found in DB but it is external");
        }

        $user->setLdapUsername($usernameValue[0]);
        $user->setLdapDn($entry->getDn());
        $user->setEmail($emailValue[0]);
        $user->setType(UserType::LDAP);
        $user->setFirstName($firstNameValue[0]);
        $user->setLastName($lastNameValue[0]);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
