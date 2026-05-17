<?php

class AuthMiddleware {
  public static function handle(array $appConfig): array {
    $token = Request::bearerToken();
    if(!$token) {
      Response::error('No Autorizado', 401);
    }
    $payload = JwtHelper::verify($token, $appConfig['jwt_secret']);
    if(!$payload) {
      Response::error('Token invalido o expirado', 401);
    }
    return $payload;
  }
}