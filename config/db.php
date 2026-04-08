<?php
// Permettre l'accès CORS si nécessaire
header("Access-Control-Allow-Origin: *");

$host = 'localhost';
$db_name = 'saas_ecole_db';
$username = 'root'; // Modifier selon l'environnement
$password = ''; // Modifier selon l'environnement

try {
    $conn = new PDO("mysql:host={$host};dbname={$db_name};charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $exception) {
    echo json_encode([
        "status" => "error",
        "message" => "Erreur de connexion à la base de données: " . $exception->getMessage()
    ]);
    exit;
}
?>
