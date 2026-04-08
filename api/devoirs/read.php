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
$classe_id = $_GET['classe_id'] ?? null;

try {
    $sql = "
        SELECT d.*, c.nom as classe_nom, m.nom as matiere_nom 
        FROM devoirs d
        JOIN classes c ON d.classe_id = c.id
        JOIN matieres m ON d.matiere_id = m.id
        WHERE d.school_id = ?
    ";
    
    $params = [$school_id];

    if ($classe_id) {
        $sql .= " AND d.classe_id = ?";
        $params[] = $classe_id;
    }

    $sql .= " ORDER BY d.date_limite DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $devoirs = $stmt->fetchAll();

    echo json_encode(["status" => "success", "data" => $devoirs]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
