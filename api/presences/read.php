<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$date_filter = $_GET['date'] ?? null;
$eleve_id = $_GET['eleve_id'] ?? null;

try {
    $sql = "
        SELECT p.*, e.nom as eleve_nom, e.prenom as eleve_prenom, e.matricule 
        FROM presences p
        JOIN eleves e ON p.eleve_id = e.id
        WHERE p.school_id = ?
    ";
    
    $params = [$school_id];

    if ($date_filter) {
        $sql .= " AND p.date_presence = ?";
        $params[] = $date_filter;
    }
    
    if ($eleve_id) {
        $sql .= " AND p.eleve_id = ?";
        $params[] = $eleve_id;
    }

    $sql .= " ORDER BY p.date_presence DESC, e.nom ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $presences = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $presences]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
