<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, phone, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Utilisateur introuvable"]);
        exit;
    }

    // Informations additionnelles selon le rôle
    if ($role === 'eleve') {
        $stmtEleve = $conn->prepare("SELECT matricule, date_naissance, sexe, adresse FROM eleves WHERE user_id = ?");
        $stmtEleve->execute([$user_id]);
        $eleve = $stmtEleve->fetch(PDO::FETCH_ASSOC);
        if ($eleve) {
            $user = array_merge($user, $eleve);
        }
    }

    echo json_encode(["status" => "success", "data" => $user]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
