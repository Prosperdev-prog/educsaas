<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

try {
    // Jointure pour récupérer les infos de l'école et de son administrateur
    $sql = "
    SELECT 
        s.*, 
        u.nom as admin_nom, 
        u.prenom as admin_prenom, 
        u.email as admin_email,
        (SELECT COUNT(*) FROM eleves e WHERE e.school_id = s.id) as total_eleves
    FROM schools s
    LEFT JOIN users u ON u.school_id = s.id AND u.role = 'admin'
    WHERE s.id > 1
    GROUP BY s.id
    ORDER BY s.created_at DESC
    ";
    $stmt = $conn->query($sql);
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $schools]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
}
?>
