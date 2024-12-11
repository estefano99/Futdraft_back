<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contraseña reseteada</title>
</head>
<body>
    <h1>¡Hola {{ $user->nombre }}!</h1>
    <p>Se ha reseteado tu contraseña.</p>
    <p>Tu nueva contraseña es: <strong>{{ $password }}</strong></p>
    <p>Por favor, cámbiala después de iniciar sesión por seguridad.</p>
    <p>Gracias,</p>
    <p>El equipo de la plataforma</p>
</body>
</html>
