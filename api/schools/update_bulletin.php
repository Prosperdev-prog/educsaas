<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Accès refusé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$logo_url = $data['logo_url'] ?? null;
$bulletin_header = $data['bulletin_header'] ?? null;

try {
    $stmt = $conn->prepare("UPDATE schools SET logo_url = ?, bulletin_header = ? WHERE id = ?");
    $stmt->execute([$logo_url, $bulletin_header, $school_id]);

    echo json_encode(["status" => "success", "message" => "Modèle de bulletin mis à jour."]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données: " . $e->getMessage()]);
}
?>
