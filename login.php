<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Administrativo | Consientt Spa</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://googleapis.com">
  <link rel="preconnect" href="https://gstatic.com" crossorigin>
  <link href="https://googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cloudflare.com">
  <!-- CSS -->
  <link rel="stylesheet" href="login_style.css">
</head>
<body>

  <!-- Flor transparente decorativa en el fondo general -->
  <div class="bg-flower-overlay"></div>

  <div class="login-card">
    <div class="login-header">
      <div class="logo">
        <i class="fas fa-spa"></i> Consientt Spa
      </div>
      <h1>Panel de Administración</h1>
      <p>Introduce tus credenciales para acceder al sistema</p>
    </div>

    <form class="login-form" action="procesar_login.php" method="POST">
      <div class="form-group">
        <label for="email">Usuario o Correo</label>
        <div class="input-wrapper">
          <i class="fas fa-user input-icon"></i>
          <input type="email" id="email" required autocomplete="username">
        </div>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <div class="input-wrapper">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password" required autocomplete="current-password">
        </div>
      </div>

      <button type="submit" class="btn-submit">Ingresar al Panel</button>
    </form>
    
    <div class="login-footer">
      <a href="index.html" class="back-link"><i class="fas fa-arrow-left"></i> Volver al sitio público</a>
    </div>
  </div>

</body>
</html>
