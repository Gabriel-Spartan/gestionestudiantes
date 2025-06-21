<?php
// generate_password.php - Script para generar contraseñas hasheadas

// Configuración
$plain_password = 'Admin12345'; // Cambia esto por la contraseña deseada
$users_to_update = ['admin@gestion.com', 'secretaria@gestion.com'];

// Generar hash seguro
$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT, [
    'cost' => 12 // Coste de procesamiento (mayor = más seguro pero más lento)
]);

echo "Contraseña original: " . $plain_password . "\n";
echo "Contraseña hasheada: " . $hashed_password . "\n\n";

// Conexión a MySQL (ajusta estos valores)
$db_host = 'localhost';
$db_name = 'gestion_estudiantes';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar consulta de actualización
    $stmt = $pdo->prepare("UPDATE usuarios SET contrasenia = :password WHERE correo = :email");
    
    foreach ($users_to_update as $email) {
        $stmt->execute([
            ':password' => $hashed_password,
            ':email' => $email
        ]);
        
        $affected = $stmt->rowCount();
        echo "Usuario $email actualizado. Filas afectadas: $affected\n";
    }

    echo "\n¡Actualización completada!\n";
    echo "Ahora puedes usar la contraseña '$plain_password' para iniciar sesión.\n";

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}