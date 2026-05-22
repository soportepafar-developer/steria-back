<?php

namespace App\Module\Auth\Controller;

use App\Module\User\Entity\Usuario;
use App\Module\User\Entity\Rol;
use App\Module\User\Repository\UsuarioRepository;
use App\Module\User\Repository\RolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @OA\Tag(name="Autenticación")
 */

class AuthController extends AbstractController
{
    /**
     * @Route("/api/auth/login", name="api_login", methods={"POST"})
     * @OA\Post(
     *     summary="Inicia sesión para obtener un token JWT",
     *     @OA\RequestBody(
     *         description="Credenciales del usuario",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="admin@geoproyect.com"),
     *             @OA\Property(property="password", type="string", example="admin123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token JWT generado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciales inválidas")
     * )
     */
    public function login(): JsonResponse
    {
        // El firewall interceptará esta ruta (json_login)
        return new JsonResponse(['message' => 'This should not be reached']);
    }

    /**
     * Endpoint temporal para crear un usuario administrador por defecto
     * y poder probar el JWT.
     * 
     * @Route("/api/auth/setup-admin", name="api_setup_admin", methods={"GET"})
     */
    public function setupAdmin(
        UsuarioRepository $userRepo,
        RolRepository $rolRepo,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $email = 'admin@geoproyect.com';

        if ($userRepo->findOneBy(['email' => $email])) {
            return $this->json(['message' => 'Admin user already exists.']);
        }

        $rol = $rolRepo->findOneBy(['nombreRol' => 'super_administrador']);
        if (!$rol) {
            $rol = new Rol();
            $rol->setNombreRol('super_administrador');
            $em->persist($rol);
        }

        $user = new Usuario();
        $user->setEmail($email);
        $user->setPrimerNombre('Admin');
        $user->setPrimerApellido('Sistema');
        $user->setActivo(true);
        $user->addUserRole($rol);

        $encodedPassword = $passwordEncoder->encodePassword($user, 'admin123');
        $user->setPassword($encodedPassword);

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Admin user created successfully.',
            'email' => $email,
            'password' => 'admin123'
        ]);
    }
}
