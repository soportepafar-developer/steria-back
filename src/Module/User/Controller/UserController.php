<?php

namespace App\Module\User\Controller;

use App\Module\User\Repository\RolRepository;
use App\Module\User\Repository\UsuarioRepository;
use App\Module\User\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/users")
 * @OA\Tag(name="Usuarios")
 */
class UserController extends AbstractController
{
    private $userRepository;
    private $rolRepository;
    private $userService;

    public function __construct(
        UsuarioRepository $userRepository,
        RolRepository $rolRepository,
        UserService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->rolRepository = $rolRepository;
        $this->userService = $userService;
    }

    /**
     * @Route("/roles", name="user_roles_list", methods={"GET"})
     */
    public function listRoles(): JsonResponse
    {
        $roles = $this->rolRepository->findAll();
        $data = [];
        foreach ($roles as $rol) {
            $data[] = [
                'id' => $rol->getId(),
                'nombre_rol' => $rol->getNombreRol()
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/listado", name="users_list", methods={"GET"})
     */
    public function listUsers(): JsonResponse
    {
        $usuarios = $this->userRepository->findAll();
        $data = [];
        foreach ($usuarios as $u) {
            $rolesList = [];
            foreach ($u->getUserRoles() as $r) {
                $rolesList[] = $r->getNombreRol();
            }

            $data[] = [
                'id' => $u->getId(),
                'primer_nombre' => $u->getPrimerNombre(),
                'segundo_nombre' => $u->getSegundoNombre(),
                'primer_apellido' => $u->getPrimerApellido(),
                'segundo_apellido' => $u->getSegundoApellido(),
                'email' => $u->getEmail(),
                'documento' => $u->getDocumento(),
                'cargo' => $u->getCargo(),
                'activo' => $u->isActivo(),
                'roles_concatenados' => implode(', ', $rolesList),
                'roles' => $rolesList
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/crear", name="user_create", methods={"POST"})
     * @OA\Post(
     *     summary="Crea un nuevo usuario",
     *     @OA\RequestBody(
     *         description="Datos del usuario a crear",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="nuevo@geoproyect.com"),
     *             @OA\Property(property="primer_nombre", type="string", example="Juan"),
     *             @OA\Property(property="primer_apellido", type="string", example="Pérez"),
     *             @OA\Property(property="segundo_nombre", type="string", example="Carlos"),
     *             @OA\Property(property="segundo_apellido", type="string", example="Gómez"),
     *             @OA\Property(property="tipo_documento", type="string", example="V"),
     *             @OA\Property(property="documento", type="string", example="12345678"),
     *             @OA\Property(property="fecha_nacimiento", type="string", example="1990-01-01"),
     *             @OA\Property(property="pais", type="string", example="Venezuela"),
     *             @OA\Property(property="estado", type="string", example="Distrito Capital"),
     *             @OA\Property(property="ciudad", type="string", example="Caracas"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal"),
     *             @OA\Property(property="sexo", type="string", example="M"),
     *             @OA\Property(property="cargo", type="string", example="Operador"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="administrador"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado de forma exitosa"
     *     ),
     *     @OA\Response(response=400, description="Datos inválidos o faltantes"),
     *     @OA\Response(response=403, description="Acceso denegado (Solo Super Admin o Admin)")
     * )
     */
    public function createUser(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMINISTRADOR') && !$this->isGranted('ROLE_ADMINISTRADOR')) {
            throw $this->createAccessDeniedException('No tienes permisos para realizar esta acción.');
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        try {
            $result = $this->userService->createUser($data);
            $user = $result['user'];
            
            return $this->json([
                'status' => 'success',
                'message' => 'Usuario creado de forma exitosa.',
                'temporary_password' => $result['temporary_password'],
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'primer_nombre' => $user->getPrimerNombre(),
                    'primer_apellido' => $user->getPrimerApellido(),
                    'roles' => $user->getRoles()
                ]
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/desactivar/{id}", name="user_deactivate", methods={"PUT"})
     */
    public function deactivateUser(int $id): JsonResponse
    {
        if (!$this->isGranted('ROLE_SUPER_ADMINISTRADOR') && !$this->isGranted('ROLE_ADMINISTRADOR')) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        try {
            $this->userService->deactivateUser($id);
            return $this->json([
                'status' => 'success',
                'message' => 'Usuario desactivado correctamente (borrado lógico).'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * @Route("/obtener/{id}", name="user_get", methods={"GET"})
     */
    public function getUserDetails(int $id): JsonResponse
    {
        $u = $this->userRepository->find($id);
        if (!$u) {
            return $this->json(['error' => 'Usuario no encontrado.'], 404);
        }

        $rolesList = [];
        foreach ($u->getUserRoles() as $r) {
            $rolesList[] = $r->getNombreRol();
        }

        return $this->json([
            'id' => $u->getId(),
            'primer_nombre' => $u->getPrimerNombre(),
            'segundo_nombre' => $u->getSegundoNombre(),
            'primer_apellido' => $u->getPrimerApellido(),
            'segundo_apellido' => $u->getSegundoApellido(),
            'tipo_documento' => $u->getTipoDocumento(),
            'documento' => $u->getDocumento(),
            'fecha_nacimiento' => $u->getFechaNacimiento() ? $u->getFechaNacimiento()->format('Y-m-d') : null,
            'pais' => $u->getPais(),
            'estado' => $u->getEstado(),
            'ciudad' => $u->getCiudad(),
            'direccion' => $u->getDireccion(),
            'sexo' => $u->getSexo(),
            'email' => $u->getEmail(),
            'cargo' => $u->getCargo(),
            'activo' => $u->isActivo(),
            'roles' => $rolesList
        ]);
    }

    /**
     * @Route("/actualizar/{id}", name="user_update", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualiza un usuario existente",
     *     @OA\RequestBody(
     *         description="Datos del usuario a actualizar",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="primer_nombre", type="string", example="Juan Editado"),
     *             @OA\Property(property="primer_apellido", type="string", example="Pérez Editado"),
     *             @OA\Property(property="segundo_nombre", type="string", example="Carlos"),
     *             @OA\Property(property="segundo_apellido", type="string", example="Gómez"),
     *             @OA\Property(property="tipo_documento", type="string", example="V"),
     *             @OA\Property(property="documento", type="string", example="12345678"),
     *             @OA\Property(property="fecha_nacimiento", type="string", example="1990-01-01"),
     *             @OA\Property(property="pais", type="string", example="Venezuela"),
     *             @OA\Property(property="estado", type="string", example="Distrito Capital"),
     *             @OA\Property(property="ciudad", type="string", example="Caracas"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal"),
     *             @OA\Property(property="sexo", type="string", example="M"),
     *             @OA\Property(property="cargo", type="string", example="Supervisor"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="administrador"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Usuario actualizado correctamente"),
     *     @OA\Response(response=400, description="Datos inválidos o faltantes"),
     *     @OA\Response(response=404, description="Usuario no encontrado")
     * )
     */
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Cuerpo de petición inválido.'], 400);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            if (!$this->isGranted('ROLE_SUPER_ADMINISTRADOR') && !$this->isGranted('ROLE_ADMINISTRADOR')) {
                throw $this->createAccessDeniedException('No tienes permisos para modificar roles.');
            }
        }

        try {
            $this->userService->updateUser($id, $data);
            return $this->json([
                'status' => 'success',
                'message' => 'Usuario actualizado correctamente.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
