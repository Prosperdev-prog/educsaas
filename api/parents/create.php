<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Oups! Droits d'administration requis pour créer des comptes parents."]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$nom = $data['nom'] ?? '';
$prenom = $data['prenom'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Tous les champs sont requis"]);
    exit;
}

try {
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (school_id, nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?, 'parent')");
    $stmt->execute([$school_id, $nom, $prenom, $email, $hashed_password]);

    echo json_encode(["status" => "success", "message" => "Compte Parent créé avec succès"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
