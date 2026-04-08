<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Vérification authentification
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$id = $_GET['id'] ?? null;

try {
    if ($id) {
        $stmt = $conn->prepare("
            SELECT e.*, c.nom as classe_nom, pe.parent_id
            FROM eleves e
            LEFT JOIN classes c ON e.classe_id = c.id
            LEFT JOIN parent_eleve pe ON e.id = pe.eleve_id
            WHERE e.school_id = ? AND e.id = ?
        ");
        $stmt->execute([$school_id, $id]);
        $eleve = $stmt->fetch();
        
        if ($eleve) {
            echo json_encode(["status" => "success", "data" => $eleve]);
        } else {
            echo json_encode(["status" => "error", "message" => "Élève introuvable"]);
        }
    } else {
        $stmt = $conn->prepare("
            SELECT e.*, c.nom as classe_nom, pe.parent_id
            FROM eleves e
            LEFT JOIN classes c ON e.classe_id = c.id
            LEFT JOIN parent_eleve pe ON e.id = pe.eleve_id
            WHERE e.school_id = ?
            ORDER BY e.nom ASC
        ");
        $stmt->execute([$school_id]);
        $eleves = $stmt->fetchAll();
        
        echo json_encode(["status" => "success", "data" => $eleves]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de base de données"]);
}
?>
