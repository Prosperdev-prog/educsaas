<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

if (empty($data['name'])) {
    echo json_encode(["status" => "error", "message" => "Le nom de l'école est requis"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE schools SET name = ?, address = ?, phone = ?, email = ? WHERE id = ?");
    $stmt->execute([
        $data['name'],
        $data['address'] ?? '',
        $data['phone'] ?? '',
        $data['email'] ?? '',
        $school_id
    ]);

    // Mise à jour de la session avec fallback robuste
    $_SESSION['school_name'] = (!empty($data['name'])) ? $data['name'] : 'EcoleSaaS';

    echo json_encode(["status" => "success", "message" => "Informations de l'école mises à jour"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
