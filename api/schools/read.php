<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];

try {
    // Info école
    $stmt = $conn->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $school = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$school) {
        echo json_encode(["status" => "error", "message" => "École introuvable"]);
        exit;
    }

    // Info abonnement (le plus récent)
    $stmt = $conn->prepare("
        SELECT s.*, p.name as plan_name, p.has_bulletins, p.has_parents, p.has_stats 
        FROM subscriptions s
        JOIN plans p ON s.plan_id = p.id
        WHERE s.school_id = ? AND s.status = 'active'
        ORDER BY s.end_date DESC LIMIT 1
    ");
    $stmt->execute([$school_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "data" => [
            "school" => $school,
            "subscription" => $subscription
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
