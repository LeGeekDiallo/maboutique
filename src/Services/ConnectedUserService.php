<?php


namespace App\Services;


use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ConnectedUserService
{
    private Security $security;

    /**
     * ConnectedUserService constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function connectedUser():User|null|UserInterface{
        return $this->security->getUser();
    }

}