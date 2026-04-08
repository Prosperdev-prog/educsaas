<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];

try {
    // 1. Get Teacher's classes
    $stmtClasses = $conn->prepare("SELECT classe_id FROM enseignant_classes WHERE enseignant_id = ?");
    $stmtClasses->execute([$user_id]);
    $classe_ids = $stmtClasses->fetchAll(PDO::FETCH_COLUMN);

    if (empty($classe_ids)) {
        echo json_encode(["status" => "success", "data" => []]);
        exit;
    }

    // 2. Get students in those classes
    $in = str_repeat('?,', count($classe_ids) - 1) . '?';
    $stmtStudents = $conn->prepare("
        SELECT e.id, e.nom, e.prenom, c.nom as classe_nom 
        FROM eleves e 
        JOIN classes c ON e.classe_id = c.id
        WHERE e.classe_id IN ($in) AND e.school_id = ?
        ORDER BY c.nom ASC, e.nom ASC
    ");
    $stmtStudents->execute([...$classe_ids, $school_id]);
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $students]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
