<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error", "message" => "Non autorisé"]); exit; }

$school_id = $_SESSION['school_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM matieres WHERE school_id = ? ORDER BY nom ASC");
    $stmt->execute([$school_id]);
    echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB"]);
}
?>
