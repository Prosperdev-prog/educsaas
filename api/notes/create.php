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
$matiere_id = $data['matiere_id'] ?? null;
$note = $data['note'] ?? null;
$type = $data['type'] ?? 'examen';
$date_evaluation = $data['date_evaluation'] ?? date('Y-m-d');
$commentaire = $data['commentaire'] ?? '';

if (!$eleve_id || !$matiere_id || $note === null) {
    echo json_encode(["status" => "error", "message" => "Élève, matière et note sont obligatoires"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO notes (school_id, eleve_id, matiere_id, note, type, date_evaluation, commentaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$school_id, $eleve_id, $matiere_id, $note, $type, $date_evaluation, $commentaire])) {
        echo json_encode(["status" => "success", "message" => "Note ajoutée avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout de la note"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données"]);
}
?>
