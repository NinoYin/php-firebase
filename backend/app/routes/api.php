<?php

return function (
    string $method,
    string $path,
    AuthController $authController,
    UserController $userController,
    array $appConfig
) {
    if ($method === 'POST' && $path === '/api/auth/login') {
        $authController->login();
    }

    if ($path === '/api/users') {
        AuthMiddleware::handle($appConfig);

        if ($method === 'GET') {
            $userController->index();
        }

        if ($method === 'POST') {
            $userController->store();
        }
    }

    if (preg_match('#^/api/users/([^/]+)/toggle-active$#', $path, $matches)) {
        AuthMiddleware::handle($appConfig);

        if ($method === 'PATCH') {
            $userController->toggleActive($matches[1]);
        }
    }

    if (preg_match('#^/api/users/([^/]+)$#', $path, $matches)) {
        AuthMiddleware::handle($appConfig);

        if ($method === 'PATCH') {
            $userController->update($matches[1]);
        }

        if ($method === 'DELETE') {
            $userController->destroy($matches[1]);
        }
    }

    Response::error('Ruta no encontrada', 404);
};