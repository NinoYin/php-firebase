<?php

class UserController
{
  private UserService $userService;
  private array $appConfig;

  public function __contructor(UserService $userService, array $appConfig)
  {
    $this->userService = $userService;
    $this->appConfig = $appConfig;
  }

  public function index(): void {
    try{
      $q = Request::query('q');
      $activo = Request::query('activo');
      $page = Request::query('page', 1);
      $limit = Request::query('limit', (int)($this->appConfig['default_pagination_limit'] ?? 10));
      $result = $this->userService->getAll($q, $activo, $page, $limit);
      Response::success($result);
    }catch(Exception $e){
      Response::error($e->getMessage(), 500);
    }
  }

  public function store():: void{
    try{
      $data = Request::body();
      $validation = UserSchema::validateCreate($data);
      if(!validation['ok']) {
        Response::error($validation['message'], 422);
      }
      $item = $this->userService->create($data);
      Response::success([
        'item' => $item,
        'message' => 'Usuario Creado Satisfactoriamente',
        201
      ]);
    }catch(Exception $e){
      Response::error($e->getMessage(), 500);
    }
  }

  public function update(string $id):: void{
    try{
      $data = Request::body();
      $validation = UserSchema::validateUpdate($data);
      if(!validation['ok']) {
        Response::error($validation['message'], 422);
      }
      $item = $this->userService->update($id, $data);
      Response::success([
        'item' => $item,
        'message' => 'Usuario Actualizado Satisfactoriamente',
        201
      ]);
    }catch(Exception $e){
      Response::error($e->getMessage(), 500);
    }
  }

  public function toggleActive(string $id): void {
    try{
      $item = $this->userService->toggleActive($id);
      Response::success([
        'item' => $item,
        'message' => 'Estado Actualizado Satisfactoriamente',
        201
      ]);
    }catch(Exception $e){
      Response::error($e->getMessage(), 500);
    }
  }

  public function destroy(string $id): void {
    try{
      $item = $this->userService->softDelete($id);
      Response::success($item);
    }catch(Exception $e){
      Response::error($e->getMessage(), 500);
    }
  }
}