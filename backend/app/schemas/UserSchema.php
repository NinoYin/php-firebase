<?php
class UserSchema {
  public static function validateCreate(array $data): array {
    $required = [
      'nombre', 'apaterno', 'direccion', 'telefono', 'ciudad', 'estado', 'usuario', 'password'
    ];

    foreach($required as $field) {
      if(trim((string)($data[$field] ?? '')) === '') {
        return [
          'ok' => false,
          'message' = > "El campo {$field} es obligatorio"
        ];
      }

      return [ 
        'ok' = true
      ];
    }
  } 

  public static function validateUpdate(array $data): array {
    if(empty($data)) {
      return [
        'ok' => false,
        'message' => 'No se recibieron datos'
      ];
    }

    return [
      'ok'=> true
    ];
  }
}