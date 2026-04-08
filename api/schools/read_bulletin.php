<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];

try {
    $stmt = $conn->prepare("SELECT id, name, logo_url, bulletin_header FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $school = $stmt->fetch();

    echo json_encode(["status" => "success", "data" => $school]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
