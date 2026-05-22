<?php

namespace App\Module\User\Service;

use App\Module\User\Entity\Usuario;
use App\Module\User\Entity\Rol;
use App\Module\User\Repository\UsuarioRepository;
use App\Module\User\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private $em;
    private $userRepository;
    private $rolRepository;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        UsuarioRepository $userRepository,
        RolRepository $rolRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->rolRepository = $rolRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function createUser(array $data): array
    {
        $email = $data['email'] ?? null;
        $primerNombre = $data['primer_nombre'] ?? null;
        $primerApellido = $data['primer_apellido'] ?? null;
        $rolesInput = $data['roles'] ?? [];

        if (!$email || !$primerNombre || !$primerApellido) {
            throw new \InvalidArgumentException('Faltan campos obligatorios: email, primer_nombre, primer_apellido.');
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            throw new \InvalidArgumentException('Ya existe un usuario con este correo electrónico.');
        }

        $tempPassword = $data['password'] ?? bin2hex(random_bytes(4));

        $user = new Usuario();
        $user->setEmail($email);
        $user->setPrimerNombre($primerNombre);
        $user->setPrimerApellido($primerApellido);
        $user->setSegundoNombre($data['segundo_nombre'] ?? null);
        $user->setSegundoApellido($data['segundo_apellido'] ?? null);
        $user->setTipoDocumento($data['tipo_documento'] ?? null);
        $user->setDocumento($data['documento'] ?? null);
        $user->setPais($data['pais'] ?? null);
        $user->setEstado($data['estado'] ?? null);
        $user->setCiudad($data['ciudad'] ?? null);
        $user->setDireccion($data['direccion'] ?? null);
        $user->setSexo($data['sexo'] ?? null);
        $user->setCargo($data['cargo'] ?? null);
        $user->setActivo(true);

        if (!empty($data['fecha_nacimiento'])) {
            try {
                $user->setFechaNacimiento(new \DateTime($data['fecha_nacimiento']));
            } catch (\Exception $e) {}
        }

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $tempPassword);
        $user->setPassword($encodedPassword);

        if (is_array($rolesInput)) {
            foreach ($rolesInput as $roleName) {
                $role = $this->rolRepository->findOneBy(['nombreRol' => $roleName]);
                if ($role) {
                    $user->addUserRole($role);
                }
            }
        }

        if ($user->getUserRoles()->isEmpty()) {
            $defaultRole = $this->rolRepository->findOneBy(['nombreRol' => 'regular']);
            if (!$defaultRole) {
                $defaultRole = new Rol();
                $defaultRole->setNombreRol('regular');
                $this->em->persist($defaultRole);
            }
            $user->addUserRole($defaultRole);
        }

        $this->em->persist($user);
        $this->em->flush();

        return [
            'user' => $user,
            'temporary_password' => $tempPassword
        ];
    }

    public function updateUser(int $id, array $data): Usuario
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        if (isset($data['primer_nombre'])) $user->setPrimerNombre($data['primer_nombre']);
        if (isset($data['segundo_nombre'])) $user->setSegundoNombre($data['segundo_nombre']);
        if (isset($data['primer_apellido'])) $user->setPrimerApellido($data['primer_apellido']);
        if (isset($data['segundo_apellido'])) $user->setSegundoApellido($data['segundo_apellido']);
        if (isset($data['tipo_documento'])) $user->setTipoDocumento($data['tipo_documento']);
        if (isset($data['documento'])) $user->setDocumento($data['documento']);
        if (isset($data['pais'])) $user->setPais($data['pais']);
        if (isset($data['estado'])) $user->setEstado($data['estado']);
        if (isset($data['ciudad'])) $user->setCiudad($data['ciudad']);
        if (isset($data['direccion'])) $user->setDireccion($data['direccion']);
        if (isset($data['sexo'])) $user->setSexo($data['sexo']);
        if (isset($data['cargo'])) $user->setCargo($data['cargo']);
        if (isset($data['email'])) $user->setEmail($data['email']);
        if (isset($data['activo'])) $user->setActivo((bool) $data['activo']);

        if (!empty($data['fecha_nacimiento'])) {
            try {
                $user->setFechaNacimiento(new \DateTime($data['fecha_nacimiento']));
            } catch (\Exception $e) {}
        }

        if (!empty($data['password'])) {
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $data['password']);
            $user->setPassword($encodedPassword);
        }

        // Roles update must be verified in Controller (Admin check)
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($user->getUserRoles() as $role) {
                $user->removeUserRole($role);
            }
            foreach ($data['roles'] as $roleName) {
                $role = $this->rolRepository->findOneBy(['nombreRol' => $roleName]);
                if ($role) {
                    $user->addUserRole($role);
                }
            }
        }

        $this->em->flush();

        return $user;
    }

    public function deactivateUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado.');
        }

        $user->setActivo(false);
        $this->em->flush();
    }
}
