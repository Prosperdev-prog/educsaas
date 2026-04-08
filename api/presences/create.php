<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$eleve_id = $data['eleve_id'] ?? null;
$date_presence = $data['date_presence'] ?? date('Y-m-d');
$statut = $data['statut'] ?? 'présent';
$motif = $data['motif'] ?? '';

if (!$eleve_id || !$date_presence) {
    echo json_encode(["status" => "error", "message" => "Élève et date sont obligatoires"]);
    exit;
}

try {
    // Vérifier si une présence existe déjà pour cet élève à cette date
    $stmt = $conn->prepare("SELECT id FROM presences WHERE eleve_id = ? AND date_presence = ? AND school_id = ?");
    $stmt->execute([$eleve_id, $date_presence, $school_id]);
    
    if ($stmt->fetch()) {
        // Mise à jour
        $sql = "UPDATE presences SET statut=?, motif=? WHERE eleve_id=? AND date_presence=? AND school_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$statut, $motif, $eleve_id, $date_presence, $school_id]);
        echo json_encode(["status" => "success", "message" => "Présence mise à jour"]);
    } else {
        // Insertion
        $sql = "INSERT INTO presences (school_id, eleve_id, date_presence, statut, motif) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$school_id, $eleve_id, $date_presence, $statut, $motif]);
        echo json_encode(["status" => "success", "message" => "Présence enregistrée"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
