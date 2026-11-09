<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - PLAGUE</title>
    <style>
        /* Estilos generales y centrado de la tarjeta */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Contenedor principal estilo tarjeta */
        .login-card {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        /* Logo */
        .logo {
            display: block;
            margin: 0 auto 15px auto;
            max-width: 120px;
            height: auto;
        }

        /* Títulos */
        h1 {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 5px;
            font-weight: 600;
        }

        h2 {
            font-size: 1.4rem;
            color: #2c3e50;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Formularios y Campos */
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #444;
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
            background-color: #fff;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }

        /* Botón de ingreso principal */
        .btn-submit {
            width: 100%;
            padding: 11px;
            background-color: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #1a252f;
        }

        /* Enlace de recuperación */
        .forgot-link {
            display: inline-block;
            margin-top: 10px;
            font-size: 0.85rem;
            color: #3498db;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        /* Estilo para mensajes de error enviados desde el controlador */
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Logo con ruta basada en BASE_URL -->
        <img src="<?= BASE_URL ?>resources/img/R.png" alt="Logo" class="logo">
        
        <h1>Autenticación de Usuario</h1>
        <h2>Iniciar Sesión</h2>
        
        <!-- Mostrar errores provenientes del controlador si existen -->
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert-error" role="alert">
                <?= $mensaje_error ?>
            </div>
        <?php endif; ?>

        <!-- Formulario que apunta a index.php con campo oculto -->
        <form action="<?= BASE_URL ?>index.php" method="POST">
            
            <!-- Controlador al que debe ir la petición -->
            <input type="hidden" name="modulo" value="login">

            <!-- Campo de usuario (correo en tu bd actual) -->
            <div class="form-group">
                <label for="correo">Correo electrónico:</label>
                <input
                    type="text"
                    id="coreo"
                    name="correo"
                    placeholder="Ingresa tu correo"
                    maxlength="50"
                    autocomplete="email"
                    required
                >
            </div>

            <!-- Campo de contraseña -->
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >
            </div>

            <!-- Botón de ingreso -->
            <div class="form-group" style="margin-top: 20px;">
                <button type="submit" class="btn-submit">
                    Ingresar
                </button>
            </div>

            <!-- Recuperación de contraseña -->
            <div>
                <!-- Si implementas la recuperación luego, el enlace sería algo como index.php?modulo=recuperar -->
                <a href="#" class="forgot-link">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

        </form>
    </div>

</body>
</html>