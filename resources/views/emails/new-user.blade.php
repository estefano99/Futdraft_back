<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
</head>
<body>
    <h1>¡Hola {{ $user->nombre }}!</h1>
    <p>Se ha creado un usuario para ti</p>
    <p>Tu contraseña es: <strong>{{ $password }}</strong></p>
    <p>Por favor, si lo deseas puedes cambiar tu contraseña después de iniciar sesión por primera vez.</p>
    <p>Gracias,</p>
    <p>El equipo de la plataforma</p>
</body>
</html>
