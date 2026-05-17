<?php

//session_start();
require_once __DIR__ . '/api.php';

if(!empty($_SESSION['token'])) {
  header('Location: dashboard.php');
  exit;
}

$error = '';

if($_Server['REQUEST_METHOD'] ==='POST') {
  $usuario = trim($_POST['usuario'] ?? '');
  $password = trim($_POST['password'] ?? '');

  $result = apiRequest('POST', '/auth/login', [
    'usuario' => $usuario,
    'password' => $password
  ], false);
  
  if($result 'status' >= 200 && $result['status'] < 300) {
    $_SESSION['token'] = $result['data']['token'];
    $_SESSION['user'] = $result['data']['user'];
    header('Location: dashboard.php');
    exit;
  }

  $error = $result['data']['message'] ?? 'No fue posible iniciar sesion';
}

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - PHP - Firebase - ᓚᘏᗢ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
      <div class="card shadow-lg border-0" style="max-width: 420px; width: 100%">
        <div class="card-body p-4">
          <h2 class="fw-bold mb-1">Iniciar Sesion</h2>
          <p class="text-muted mb-4">PHP + Firebase + Jwt</p>

          <?php if($error): ?>
            <div class="alert alert-danger">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form method="post">
            <div class="mb-3">
              <lable class="form-label">Usuario</lable>
              <input type="text" class="form-control" name="usuario" required>
            </div>

            <div class="mb-3">
              <lable class="form-label">Password</lable>
              <input type="password" class="form-control" name="password" required>
            </div>

            <button class="btn btn-primary w-100">Entrar</button>
          </form>

          <div class="mt-4 small text-muted">
            Usuario inicial sugerido: <strong>proyecto</strong>
          </div>
          
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>