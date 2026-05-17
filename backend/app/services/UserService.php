<?php

class UserService
{
    private UserRepository $userRepository;
    private array $appConfig;

    public function __construct(UserRepository $userRepository, array $appConfig)
    {
        $this->userRepository = $userRepository;
        $this->appConfig = $appConfig;
    }

    public function getAll(?string $q = null, ?string $activo = null, int $page = 1, int $limit = 10): array
    {
        $all = $this->userRepository->findAllNotDeleted();

        if ($q !== null && trim($q) !== '') {
            $term = mb_strtolower(trim($q));
            $all = array_values(array_filter($all, function ($item) use ($term) {
                $text = mb_strtolower(
                    ($item['nombre'] ?? '') . ' ' .
                    ($item['apaterno'] ?? '') . ' ' .
                    ($item['amaterno'] ?? '') . ' ' .
                    ($item['usuario'] ?? '') . ' ' .
                    ($item['ciudad'] ?? '') . ' ' .
                    ($item['estado'] ?? '') . ' ' .
                    ($item['telefono'] ?? '')
                );
                return str_contains($text, $term);
            }));
        }

        if ($activo !== null && $activo !== '') {
            if ($activo === 'true' || $activo === '1') {
                $all = array_values(array_filter($all, fn($item) => (bool)($item['activo'] ?? false) === true));
            } elseif ($activo === 'false' || $activo === '0') {
                $all = array_values(array_filter($all, fn($item) => (bool)($item['activo'] ?? false) === false));
            }
        }

        usort($all, function ($a, $b) {
            return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
        });

        $page = max(1, $page);
        $defaultLimit = (int)($this->appConfig['default_pagination_limit'] ?? 10);
        $maxLimit = (int)($this->appConfig['max_pagination_limit'] ?? 100);
        $limit = max(1, min($limit > 0 ? $limit : $defaultLimit, $maxLimit));

        $total = count($all);
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $items = array_slice($all, $offset, $limit);

        foreach ($items as &$item) {
            unset($item['password']);
        }

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
                'hasPrev' => $page > 1,
                'hasNext' => $page < $totalPages,
            ],
            'filters' => [
                'q' => $q,
                'activo' => $activo,
            ]
        ];
    }

    public function create(array $data): array
    {
        $existing = $this->userRepository->findByUsuario(trim($data['usuario']));
        if ($existing && ($existing['deleted'] ?? false) === false) {
            throw new Exception('Ya existe un usuario con ese nombre de usuario');
        }

        $payload = [
            'nombre' => trim($data['nombre']),
            'apaterno' => trim($data['apaterno']),
            'amaterno' => trim($data['amaterno'] ?? ''),
            'direccion' => trim($data['direccion']),
            'telefono' => trim($data['telefono']),
            'ciudad' => trim($data['ciudad']),
            'estado' => trim($data['estado']),
            'usuario' => trim($data['usuario']),
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'activo' => isset($data['activo']) ? (bool)$data['activo'] : true,
            'deleted' => false,
            'role' => trim($data['role'] ?? 'user'),
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ];

        $created = $this->userRepository->create($payload);
        unset($created['password']);

        return $created;
    }

    public function update(string $id, array $data): array
    {
        $existing = $this->userRepository->findById($id);
        if (!$existing || ($existing['deleted'] ?? false) === true) {
            throw new Exception('Usuario no encontrado');
        }

        if (!empty($data['usuario']) && trim($data['usuario']) !== ($existing['usuario'] ?? '')) {
            $userByUsuario = $this->userRepository->findByUsuario(trim($data['usuario']));
            if ($userByUsuario && $userByUsuario['id'] !== $id && ($userByUsuario['deleted'] ?? false) === false) {
                throw new Exception('Ya existe un usuario con ese nombre de usuario');
            }
        }

        $payload = [
            'nombre' => trim($data['nombre'] ?? $existing['nombre']),
            'apaterno' => trim($data['apaterno'] ?? $existing['apaterno']),
            'amaterno' => trim($data['amaterno'] ?? ($existing['amaterno'] ?? '')),
            'direccion' => trim($data['direccion'] ?? $existing['direccion']),
            'telefono' => trim($data['telefono'] ?? $existing['telefono']),
            'ciudad' => trim($data['ciudad'] ?? $existing['ciudad']),
            'estado' => trim($data['estado'] ?? $existing['estado']),
            'usuario' => trim($data['usuario'] ?? $existing['usuario']),
            'activo' => array_key_exists('activo', $data) ? (bool)$data['activo'] : (bool)$existing['activo'],
            'deleted' => (bool)($existing['deleted'] ?? false),
            'role' => trim($data['role'] ?? ($existing['role'] ?? 'user')),
            'createdAt' => $existing['createdAt'] ?? date('c'),
            'updatedAt' => date('c')
        ];

        if (!empty($data['password'])) {
            $payload['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            $payload['password'] = $existing['password'];
        }

        $updated = $this->userRepository->update($id, $payload);
        unset($updated['password']);

        return $updated;
    }

    public function toggleActive(string $id): array
    {
        $existing = $this->userRepository->findById($id);
        if (!$existing || ($existing['deleted'] ?? false) === true) {
            throw new Exception('Usuario no encontrado');
        }

        $existing['activo'] = !((bool)($existing['activo'] ?? false));
        $existing['updatedAt'] = date('c');

        $updated = $this->userRepository->update($id, $existing);
        unset($updated['password']);

        return $updated;
    }

    public function softDelete(string $id): array
    {
        $existing = $this->userRepository->findById($id);
        if (!$existing || ($existing['deleted'] ?? false) === true) {
            throw new Exception('Usuario no encontrado');
        }

        $existing['deleted'] = true;
        $existing['updatedAt'] = date('c');

        $updated = $this->userRepository->update($id, $existing);
        unset($updated['password']);

        return [
            'message' => 'Usuario eliminado correctamente',
            'item' => $updated
        ];
    }

    public function createBootstrapAdmin(array $data): array
    {
        $existing = $this->userRepository->findByUsuario(trim($data['usuario']));
        if ($existing && ($existing['deleted'] ?? false) === false) {
            throw new Exception('Ya existe un usuario con ese nombre de usuario');
        }

        return $this->create([
            'nombre' => $data['nombre'],
            'apaterno' => $data['apaterno'],
            'amaterno' => $data['amaterno'] ?? '',
            'direccion' => $data['direccion'],
            'telefono' => $data['telefono'],
            'ciudad' => $data['ciudad'],
            'estado' => $data['estado'],
            'usuario' => $data['usuario'],
            'password' => $data['password'],
            'activo' => true,
            'role' => 'admin',
        ]);
    }
}