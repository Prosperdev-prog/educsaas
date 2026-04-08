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
$eleve_id = $_GET['eleve_id'] ?? null;

try {
    $sql = "
        SELECT n.*, m.nom as matiere_nom, m.coefficient, e.nom as eleve_nom, e.prenom as eleve_prenom 
        FROM notes n
        JOIN matieres m ON n.matiere_id = m.id
        JOIN eleves e ON n.eleve_id = e.id
        WHERE n.school_id = ?
    ";
    
    $params = [$school_id];

    if ($eleve_id) {
        $sql .= " AND n.eleve_id = ?";
        $params[] = $eleve_id;
    }

    $sql .= " ORDER BY n.date_evaluation DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notes = $stmt->fetchAll();

    // Calcul de la moyenne pour un élève spécifique
    $moyenne_generale = null;
    if ($eleve_id && count($notes) > 0) {
        $total_points = 0;
        $total_coef = 0;
        foreach ($notes as $n) {
            $coef = $n['coefficient'] ?: 1;
            $total_points += ($n['note'] * $coef);
            $total_coef += $coef;
        }
        if ($total_coef > 0) {
            $moyenne_generale = round($total_points / $total_coef, 2);
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $notes,
        "moyenne_generale" => $moyenne_generale
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de base de données"]);
}
?>
