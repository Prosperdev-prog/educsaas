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
    $eleve_id = null;
    if ($role === 'eleve') {
        $stmt = $conn->prepare("SELECT id FROM eleves WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $res = $stmt->fetch();
        if($res) $eleve_id = $res['id'];
    } elseif ($role === 'parent') {
        $stmt = $conn->prepare("SELECT eleve_id as id FROM parent_eleve WHERE parent_id = ? LIMIT 1"); // Gestion mono-enfant pour simplifier
        $stmt->execute([$user_id]);
        $res = $stmt->fetch();
        if($res) $eleve_id = $res['id'];
    }
    
    if (!$eleve_id) { echo json_encode(["status" => "error", "message" => "Aucun élève lié."]); exit; }

    // On rappelle la logique de bulletin (via include) sans afficher la notice de session active
    $_GET['eleve_id'] = $eleve_id; // Simule l'appel classique
    ob_start();
    @include 'read.php'; // api/bulletins/read.php
    $output = ob_get_clean();
    echo $output;
    
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur bd"]);
}
?>