<?php
session_start();
/*
if(empty($_SESSION['token'])) {
  header('Location: index.php');
  exit;
}
*/

$user = $_SESSION['user'] ?? [];
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - PHP - (⌐■_■)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body class="bg-light">
    <nav class="navbar nabvar-expand-lg bg-dark navbar-dark shadow-sm">
      <div class="container">
        <a href="dashboard.php" class="navbar-brand fw-bold">Sistema PHP</a>
        <div class="d-flex gap-2">
          <a href="user.php" class="btn btn-outline-light btn-sm">Usuario</a>
          <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
        </div>
      </div>
    </nav>

    <div class="container py-4">
      <div class="card border-0 shadow-sm">
        <h1 class="h3 mb-2">Dashboard</h1>
        <p class="text-muted mb-0">
          Bienvenidos <?= htmlspecialchars(trim($user['nombre']) ?? '') ?>
        </p>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h2 class="h5">Proximamente...</h2>
              <p class="mb-0 text-muted">
                Paginacion, Filtros, Busqueda y  mas
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <h2 class="h5">Modulo Usuario</h2>
              <p class="mb-0 text-muted">
                Gestion de Usuarios del sistema
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>