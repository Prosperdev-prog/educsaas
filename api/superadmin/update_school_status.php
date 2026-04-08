<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Accès réservé au Superadmin"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$school_id = $data['school_id'] ?? null;
$action = $data['action'] ?? ''; // 'prolonger', 'desactiver', 'activer'
$comment = $data['comment'] ?? '';
$days = (int)($data['days'] ?? 30);

if (!$school_id) {
    echo json_encode(["status" => "error", "message" => "ID École manquant"]);
    exit;
}

try {
    if ($action === 'prolonger') {
        // Calculer la nouvelle date
        $stmt = $conn->prepare("SELECT license_expiry FROM schools WHERE id = ?");
        $stmt->execute([$school_id]);
        $current = $stmt->fetchColumn();
        
        // Si la licence actuelle est encore valide, on part de sa fin, sinon on part d'aujourd'hui
        $now = time();
        $baseTimestamp = ($current && strtotime($current) > $now) ? strtotime($current) : $now;
        
        $newExpiry = date('Y-m-d', strtotime("+$days days", $baseTimestamp));
        
        $stmt = $conn->prepare("UPDATE schools SET license_expiry = ?, license_status = 'active', status_comment = ? WHERE id = ?");
        $stmt->execute([$newExpiry, "Licence prolongée de $days jours. " . $comment, $school_id]);
        
        echo json_encode(["status" => "success", "message" => "Licence prolongée jusqu'au " . date('d/m/Y', strtotime($newExpiry))]);
        
    } elseif ($action === 'desactiver') {
        $stmt = $conn->prepare("UPDATE schools SET license_status = 'suspended', status_comment = ? WHERE id = ?");
        $stmt->execute([$comment, $school_id]);
        echo json_encode(["status" => "success", "message" => "École désactivée"]);
        
    } elseif ($action === 'activer') {
        $stmt = $conn->prepare("UPDATE schools SET license_status = 'active', status_comment = ? WHERE id = ?");
        $stmt->execute([$comment, $school_id]);
        echo json_encode(["status" => "success", "message" => "École réactivée"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
