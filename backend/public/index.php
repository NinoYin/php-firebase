<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$appConfig = require __DIR__ . '/../app/config/app.php';
$firebaseConfig = require __DIR__ . '/../app/config/firebase.php';

date_default_timezone_set($appConfig['timezone'] ?? 'UTC');

require_once __DIR__ . '/../app/helpers/Response.php';
require_once __DIR__ . '/../app/helpers/Request.php';
require_once __DIR__ . '/../app/helpers/JwtHelper.php';
require_once __DIR__ . '/../app/helpers/FirebaseAuth.php';
require_once __DIR__ . '/../app/helpers/FirestoreClient.php';

require_once __DIR__ . '/../app/schemas/AuthSchema.php';
require_once __DIR__ . '/../app/schemas/UserSchema.php';

require_once __DIR__ . '/../app/repositories/UserRepository.php';

require_once __DIR__ . '/../app/services/AuthService.php';
require_once __DIR__ . '/../app/services/UserService.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

require_once __DIR__ . '/../app/middlewares/AuthMiddleware.php';

$firestoreClient = new FirestoreClient($firebaseConfig);
$userRepository = new UserRepository($firestoreClient, $firebaseConfig);
$authService = new AuthService($userRepository, $appConfig);
$userService = new UserService($userRepository, $appConfig);
$authController = new AuthController($authService);
$userController = new UserController($userService, $appConfig);

$router = require __DIR__ . '/../app/routes/api.php';
$method = Request::method();
$path = Request::path();

$router($method, $path, $authController, $userController, $appConfig);