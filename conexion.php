<?php
$host = 'localhost';
$db   = 'formulario_contacto';
$user = 'root'; // Cambia por tu usuario de MySQL
$pass = '';     // Cambia por tu contraseña de MySQL

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>