<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$school_id = $data['school_id'] ?? null;

if (!$school_id) {
    echo json_encode(["status" => "error", "message" => "ID école manquant"]);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Mettre à jour le statut de l'école
    $stmt = $conn->prepare("UPDATE schools SET license_status = 'active' WHERE id = ?");
    $stmt->execute([$school_id]);

    // 2. Créer ou mettre à jour l'abonnement (Plan PREMIUM = id 2)
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+1 year'));
    
    // On désactive les anciens abonnements
    $stmtCancel = $conn->prepare("UPDATE subscriptions SET status = 'expired' WHERE school_id = ?");
    $stmtCancel->execute([$school_id]);

    $stmtSub = $conn->prepare("INSERT INTO subscriptions (school_id, plan_id, start_date, end_date, status) VALUES (?, 2, ?, ?, 'active')");
    $stmtSub->execute([$school_id, $startDate, $endDate]);

    // 3. Enregistrer le paiement (50 000 FCFA par défaut pour 1 an)
    $stmtPay = $conn->prepare("INSERT INTO payments (school_id, amount, currency, payment_method, status) VALUES (?, 50000, 'XOF', 'Admin Force', 'completed')");
    $stmtPay->execute([$school_id]);

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Licence PREMIUM activée avec succès pour 1 an."]);

} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => "Erreur: " . $e->getMessage()]);
}
?>
