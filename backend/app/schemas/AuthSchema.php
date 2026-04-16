<?php
class AuthSchema {
  public static function validateLogin(array $data): void {
    $usuario = trim($data['usuario'] ?? '');
    $password = trim($data['password'] ?? '');

    if($usuario === '' ) {
      return [
        'ok' => false,
        'message' => 'El usuario es obligatorioS'
      ];
    }

    if($password === '' ) {
      return [
        'ok' => false,
        'message' => 'El password es obligatorioS'
      ];
    }

    return [
      'ok' => true
    ];
  }
}