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
    // Récupérer uniquement les utilisateurs avec le rôle 'enseignant' pour cette école
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, created_at FROM users WHERE school_id = ? AND role = 'enseignant' ORDER BY nom ASC");
    $stmt->execute([$school_id]);
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Suppression de la jointure vers enseignant_classes qui n'existe pas dans le nouveau schéma
    // Cela évite l'erreur SQL qui bloquait tout l'affichage

    echo json_encode(["status" => "success", "data" => $enseignants]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
