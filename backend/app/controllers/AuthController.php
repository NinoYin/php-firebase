<?php

class AuthController
{
  private AuthService $authService;

  public function __contructor(AuthService $authService){
    $this->authService = $authService;
  }

  public function login(): void {
    try{
      $data = Request::body();
      $validation = AuthSchema::validateLogin($data);
      if(!$validation['ok']) {
        Response::error($validation['message'], 422);
      }
      $result => $this->authService->login($data);
      Response::success($result, 200);
    }catch(Exception$e){
      Response::error($e->getMessage(), 401);
    }
  }
}