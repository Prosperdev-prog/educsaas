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

$classe_id = $data['classe_id'] ?? null;
$matiere_id = $data['matiere_id'] ?? null;
$titre = $data['titre'] ?? '';
$description = $data['description'] ?? '';
$date_limite = $data['date_limite'] ?? null;

if (!$classe_id || !$matiere_id || empty($titre) || !$date_limite) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir les champs obligatoires"]);
    exit;
}

try {
    $sql = "INSERT INTO devoirs (school_id, classe_id, matiere_id, titre, description, date_limite) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$school_id, $classe_id, $matiere_id, $titre, $description, $date_limite]);
    echo json_encode(["status" => "success", "message" => "Devoir ajouté avec succès"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
