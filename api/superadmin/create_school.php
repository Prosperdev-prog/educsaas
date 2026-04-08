<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$school_name = $data['school_name'] ?? '';
$admin_nom = $data['admin_nom'] ?? '';
$admin_prenom = $data['admin_prenom'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($school_name) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs"]);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Check email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => "Email admin déjà utilisé"]);
        exit;
    }

    // 2. Create the school
    $stmt = $conn->prepare("INSERT INTO schools (name, email, license_status) VALUES (?, ?, 'trial')");
    $stmt->execute([$school_name, $email]);
    $new_school_id = $conn->lastInsertId();

    // 3. Create the admin user for this school
    $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (school_id, nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
    $stmt->execute([$new_school_id, $admin_nom, $admin_prenom, $email, $hashed_pwd]);

    // 4. Initialiser un abonnement FREE (SaaS Logic)
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+30 days'));
    $stmtSub = $conn->prepare("INSERT INTO subscriptions (school_id, plan_id, start_date, end_date, status) VALUES (?, 1, ?, ?, 'active')");
    $stmtSub->execute([$new_school_id, $startDate, $endDate]);

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "École '$school_name' enregistrée avec succès!"]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(["status" => "error", "message" => "Erreur backend : " . $e->getMessage()]);
}
?>
