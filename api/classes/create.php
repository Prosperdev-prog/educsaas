<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$nom = $data['nom'] ?? '';
$niveau = $data['niveau'] ?? '';

if (empty($nom)) {
    echo json_encode(["status" => "error", "message" => "Le nom de la classe est obligatoire"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO classes (school_id, nom, niveau) VALUES (?, ?, ?)");
    if ($stmt->execute([$school_id, $nom, $niveau])) {
        echo json_encode(["status" => "success", "message" => "Classe ajoutée avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
