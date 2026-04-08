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

if (!$eleve_id) {
    echo json_encode(["status" => "error", "message" => "L'ID de l'élève est requis"]);
    exit;
}

try {
    // Info élève
    $stmt = $conn->prepare("SELECT e.*, c.nom as classe_nom FROM eleves e LEFT JOIN classes c ON e.classe_id = c.id WHERE e.id = ? AND e.school_id = ?");
    $stmt->execute([$eleve_id, $school_id]);
    $eleve = $stmt->fetch();

    if (!$eleve) {
        echo json_encode(["status" => "error", "message" => "Élève introuvable"]);
        exit;
    }

    // Notes groupées par matière
    $stmt = $conn->prepare("
        SELECT n.note, m.nom as matiere_nom, m.coefficient 
        FROM notes n
        JOIN matieres m ON n.matiere_id = m.id
        WHERE n.eleve_id = ? AND n.school_id = ?
    ");
    $stmt->execute([$eleve_id, $school_id]);
    $notes = $stmt->fetchAll();

    $bulletin = [];
    $total_points = 0;
    $total_coefs = 0;

    foreach ($notes as $n) {
        $mat = $n['matiere_nom'];
        if (!isset($bulletin[$mat])) {
            $bulletin[$mat] = [
                "matiere" => $mat,
                "coefficient" => $n['coefficient'],
                "notes" => [],
                "moyenne" => 0
            ];
        }
        $bulletin[$mat]['notes'][] = (float)$n['note'];
    }

    // Calculs finaux par matière
    foreach ($bulletin as &$ligne) {
        $somme = array_sum($ligne['notes']);
        $count = count($ligne['notes']);
        $ligne['moyenne'] = round($somme / $count, 2);
        
        $total_points += ($ligne['moyenne'] * $ligne['coefficient']);
        $total_coefs += $ligne['coefficient'];
    }

    $moyenne_generale = $total_coefs > 0 ? round($total_points / $total_coefs, 2) : 0;

    echo json_encode([
        "status" => "success",
        "data" => [
            "eleve" => $eleve,
            "lignes" => array_values($bulletin),
            "moyenne_generale" => $moyenne_generale
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de base de données : " . $e->getMessage()]);
}
?>
