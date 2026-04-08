<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'enseignant'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID manquant"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND school_id = ?");
    if ($stmt->execute([$id, $school_id])) {
        echo json_encode(["status" => "success", "message" => "Note supprimée avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la suppression"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
