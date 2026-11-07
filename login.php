<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso Administrativo | Consientt Spa</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <form id="loginForm" class="login-form">
      <div class="form-group">
        <label for="email">Usuario o Correo</label>
        <div class="input-wrapper">
          <i class="fas fa-user input-icon"></i>
          <input type="email" id="email" name="email" required autocomplete="username">
        </div>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <div class="input-wrapper">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
      </div>

      <button type="submit" class="btn-submit">Ingresar al Panel</button>
    </form>
    
    <div class="login-footer">
      <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver al sitio público</a>
    </div>
  </div>

  <!-- Script para procesar el JSON y hacer la redirección automática -->
  <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Evita que la página se recargue de golpe

        let formData = new FormData(this);

        fetch('procesar_login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Si entra bien, nos manda a la página que indique el PHP (index.php o admin.php)
                window.location.href = data.redirect;
            } else {
                // Si hay error (contraseña mal), muestra una alerta bonita con el mensaje
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
        });
    });
  </script>

</body>
</html>
