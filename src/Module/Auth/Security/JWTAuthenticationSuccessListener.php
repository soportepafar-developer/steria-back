<?php

namespace App\Module\Auth\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use App\Module\User\Entity\Usuario;

class JWTAuthenticationSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();
        if (!$user instanceof Usuario) {
            return;
        }

        $data = $event->getData();
        
        // Mapear los roles al formato de booleanos que el frontend espera
        $rolesObj = [
            'rol_super_administrador' => false,
            'rol_administrador' => false,
            'rol_analista' => false,
            'rol_regular' => false,
        ];
        
        foreach ($user->getUserRoles() as $rol) {
            $nombreRol = strtolower($rol->getNombreRol());
            if ($nombreRol === 'super_administrador' || $nombreRol === 'rol_super_administrador') {
                $rolesObj['rol_super_administrador'] = true;
            } elseif ($nombreRol === 'administrador' || $nombreRol === 'rol_administrador') {
                $rolesObj['rol_administrador'] = true;
            } elseif ($nombreRol === 'analista' || $nombreRol === 'rol_analista') {
                $rolesObj['rol_analista'] = true;
            } elseif ($nombreRol === 'regular' || $nombreRol === 'rol_regular') {
                $rolesObj['rol_regular'] = true;
            }
        }
        
        // Si no tiene ningún rol asignado en la base de datos, por defecto es regular
        if (!array_filter($rolesObj)) {
            $rolesObj['rol_regular'] = true;
        }

        $data['user'] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'primer_nombre' => $user->getPrimerNombre(),
            'primer_apellido' => $user->getPrimerApellido(),
            'roles' => $rolesObj,
            'cambiar_password' => $user->getCambiarPassword(),
        ];

        $event->setData($data);
    }
}
