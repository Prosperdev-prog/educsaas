<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$id = $data['id'] ?? null;
$nom = $data['nom'] ?? '';
$coefficient = $data['coefficient'] ?? 1;

if (!$id || empty($nom)) {
    echo json_encode(["status" => "error", "message" => "ID et Nom sont obligatoires"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE matieres SET nom = ?, coefficient = ? WHERE id = ? AND school_id = ?");
    if ($stmt->execute([$nom, $coefficient, $id, $school_id])) {
        echo json_encode(["status" => "success", "message" => "Matière mise à jour avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
