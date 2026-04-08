<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

// Seul le superadmin ou l'admin de l'école peut désactiver un utilisateur
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['superadmin', 'admin'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$user_id = $data['user_id'] ?? null;
$is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
$comment = $data['comment'] ?? '';

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "ID Utilisateur manquant"]);
    exit;
}

try {
    // Si c'est un admin, il ne peut désactiver que les users de son école
    if ($_SESSION['role'] === 'admin') {
        $stmt = $conn->prepare("UPDATE users SET is_active = ?, status_comment = ? WHERE id = ? AND school_id = ?");
        $stmt->execute([$is_active, $comment, $user_id, $_SESSION['school_id']]);
    } else {
        // Superadmin peut tout faire
        $stmt = $conn->prepare("UPDATE users SET is_active = ?, status_comment = ? WHERE id = ?");
        $stmt->execute([$is_active, $comment, $user_id]);
    }

    $msg = $is_active ? "Utilisateur activé" : "Utilisateur désactivé";
    echo json_encode(["status" => "success", "message" => $msg]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
