<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventListener
{
    public function onLogout(LogoutEvent $event)
    {
        $event->setResponse(new JsonResponse(['message' => 'Logout successful']));
    }
}
