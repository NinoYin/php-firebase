<?php

class AuthService
{
  private UseRepository $userRepository;
  private array $appConfig;

  public function __construct(UserRepository $userRepository, array $appConfig){
    $this->userRepository = $userRepository;
    $this->appConfig = $appConfig;
  }

  public function login(array $data): array
  {
    $user = $this->userRepository->findByUsuario($data['usuario']);

    if(!$user || ($user['deleted'] ?? false) === true) {
      throw new Exception('Credenciales Invalidas')
    }

    if(($user['activo'] ?? false) !== true) {
      throw new Exception('Credenciales Inactivo')
    }

    $passwordHash = $user['password'] ?? '';
    if(!password_verify($data['password'], $passwordHash)) {
      throw new Exception('Credenciales Invalidas')
    }

    $token = JwtHelper::create([
      'sub' => $user['id'],
      'usuario' => $user['usuario'],
      'nombre' = $user['nombre'],
    ], $this->appConfig['jwt_secret'], $this->appConfig['jwt_exp_minutes']);

    return [
      'token' => $token,
      'user' => [
        'id' =>$user['id'],
        'nombre' => $user['nombre'],
        'apaterno' => $user['apaterno'] ?? '',
        'amaterno' => $user['amaterno'] ?? '',
        'usuario' => $user['usuario'],
        'activo' => $user['activo'],
      ]
    ];
  }
}