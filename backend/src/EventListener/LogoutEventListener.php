<?php

namespace App\EventListener;

use App\ResponseCodes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventListener
{
    public function onLogout(LogoutEvent $event)
    {
        $event->setResponse(new JsonResponse(ResponseCodes::makeResponse(ResponseCodes::$SUCCESS)));
    }
}
