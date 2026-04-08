<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$id = $data['id'] ?? null;
$nom = $data['nom'] ?? '';
$prenom = $data['prenom'] ?? '';
$email = $data['email'] ?? '';

if (!$id || empty($nom) || empty($prenom) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Tous les champs sont obligatoires"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ?, email = ? WHERE id = ? AND school_id = ? AND role = 'parent'");
    if ($stmt->execute([$nom, $prenom, $email, $id, $school_id])) {
        echo json_encode(["status" => "success", "message" => "Parent mis à jour avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
