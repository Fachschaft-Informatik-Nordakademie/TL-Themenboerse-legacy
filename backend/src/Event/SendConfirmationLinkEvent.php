<?php


namespace App\Event;


use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SendConfirmationLinkEvent extends Event
{

    public const NAME = 'send_confirmation_link';

    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }

}