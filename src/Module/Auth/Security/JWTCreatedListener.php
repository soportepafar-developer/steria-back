<?php

namespace App\Module\Auth\Security;

use App\Module\User\Entity\Usuario;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        if (!$user instanceof Usuario) {
            return;
        }

        $payload = $event->getData();
        
        $payload['user_id'] = $user->getId();
        $payload['primer_nombre'] = $user->getPrimerNombre();
        $payload['primer_apellido'] = $user->getPrimerApellido();

        $event->setData($payload);
    }
}
