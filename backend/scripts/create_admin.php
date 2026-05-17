<?php

$appConfig = require __DIR__ . '/../app/config/app.php';
$firebaseConfig = require __DIR__ . '/../app/config/firebase.php';

date_default_timezone_set($appConfig['timezone'] ?? 'UTC');

require_once __DIR__ . '/../app/helpers/FirebaseAuth.php';
require_once __DIR__ . '/../app/helpers/FirestoreClient.php';

require_once __DIR__ . '/../app/repositories/UserRepository.php';
require_once __DIR__ . '/../app/services/UserService.php';

try {
    $firestoreClient = new FirestoreClient($firebaseConfig);
    $userRepository = new UserRepository($firestoreClient, $firebaseConfig);
    $userService = new UserService($userRepository, $appConfig);

    $admin = $userService->createBootstrapAdmin([
        'nombre' => 'Proyecto',
        'apaterno' => 'Administrador',
        'amaterno' => '',
        'direccion' => 'Sin dirección',
        'telefono' => '0000000000',
        'ciudad' => 'Irapuato',
        'estado' => 'Guanajuato',
        'usuario' => 'proyecto',
        'password' => 'Hello2U"',
    ]);

    echo "Admin creado correctamente\n";
    echo "Usuario: proyecto\n";
    echo "Password: Hello2U\"\n";
    echo "ID: " . ($admin['id'] ?? '') . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}