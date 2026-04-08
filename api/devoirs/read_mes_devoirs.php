<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error", "message" => "Non autorisé"]); exit; }

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    $classes_ids = [];
    if ($role === 'eleve') {
        $stmt = $conn->prepare("SELECT classe_id FROM eleves WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $res = $stmt->fetch();
        if($res && $res['classe_id']) $classes_ids[] = $res['classe_id'];
    } elseif ($role === 'parent') {
        $stmt = $conn->prepare("SELECT e.classe_id FROM parent_eleve p JOIN eleves e ON p.eleve_id = e.id WHERE p.parent_id = ?");
        $stmt->execute([$user_id]);
        while($row = $stmt->fetch()) {
            if($row['classe_id']) $classes_ids[] = $row['classe_id'];
        }
    }
    
    $classes_ids = array_unique($classes_ids); // Éviter les doublons si enfants dans la même classe
    if (empty($classes_ids)) { echo json_encode(["status" => "success", "data" => []]); exit; }
    
    $in = str_repeat('?,', count($classes_ids) - 1) . '?';
    $params = array_merge([$school_id], $classes_ids);
    
    $stmt = $conn->prepare("
        SELECT d.*, m.nom as matiere_nom 
        FROM devoirs d
        LEFT JOIN matieres m ON d.matiere_id = m.id
        WHERE d.school_id = ? AND d.classe_id IN ($in)
        ORDER BY d.date_limite ASC
    ");
    $stmt->execute($params);
    $devoirs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $devoirs]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de bd"]);
}
?>