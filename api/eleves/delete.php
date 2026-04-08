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

$id = $data['id'] ?? null;

if (empty($id)) {
    echo json_encode(["status" => "error", "message" => "L'ID de l'élève est obligatoire"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM eleves WHERE id=? AND school_id=?");
    
    if ($stmt->execute([$id, $school_id])) {
        echo json_encode(["status" => "success", "message" => "Élève supprimé avec succès", "data" => []]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la suppression"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données: " . $e->getMessage()]);
}
?>
