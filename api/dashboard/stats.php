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

try {
    // Total élèves
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM eleves WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $total_eleves = $stmt->fetchColumn();

    // Total classes
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM classes WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $total_classes = $stmt->fetchColumn();

    // Total matières
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM matieres WHERE school_id = ?");
    $stmt->execute([$school_id]);
    $total_matieres = $stmt->fetchColumn();

    // Total enseignants (Personnel)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE school_id = ? AND role = 'enseignant'");
    $stmt->execute([$school_id]);
    $total_enseignants = $stmt->fetchColumn();

    // Répartition garçons / filles
    $stmt = $conn->prepare("SELECT sexe, COUNT(*) as count FROM eleves WHERE school_id = ? GROUP BY sexe");
    $stmt->execute([$school_id]);
    $repartition_sexe = $stmt->fetchAll();
    
    $garcons = 0;
    $filles = 0;
    foreach ($repartition_sexe as $row) {
        if ($row['sexe'] === 'M') $garcons = $row['count'];
        if ($row['sexe'] === 'F') $filles = $row['count'];
    }

    // 5 derniers inscrits
    $stmt = $conn->prepare("SELECT nom, prenom, created_at FROM eleves WHERE school_id = ? ORDER BY id DESC LIMIT 5");
    $stmt->execute([$school_id]);
    $derniers_inscrits = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => [
            "total_eleves" => $total_eleves,
            "total_classes" => $total_classes,
            "total_matieres" => $total_matieres,
            "total_enseignants" => $total_enseignants,
            "stats_sexe" => [
                "garcons" => $garcons,
                "filles" => $filles
            ],
            "derniers_inscrits" => $derniers_inscrits
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
