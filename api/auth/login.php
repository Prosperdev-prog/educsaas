<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Méthode non autorisée"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT u.id, u.school_id, u.nom, u.prenom, u.role, u.password, u.is_active, u.status_comment, u.photo,
               s.name as school_name, s.logo as school_logo, s.status_comment as school_comment, s.license_status,
               p.has_bulletins, p.has_parents, p.has_stats, p.name as plan_name
        FROM users u 
        LEFT JOIN schools s ON u.school_id = s.id 
        LEFT JOIN subscriptions sub ON s.id = sub.school_id AND sub.status = 'active'
        LEFT JOIN plans p ON sub.plan_id = p.id
        WHERE u.email = ? LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        
        // 1. Vérifier si l'utilisateur est actif
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            $reason = !empty($user['status_comment']) ? " Raison : " . $user['status_comment'] : "";
            echo json_encode(["status" => "error", "message" => "Votre compte a été désactivé." . $reason]);
            exit;
        }

        // 2. Vérifier si l'école est suspendue (sauf pour superadmin)
        if ($user['role'] !== 'superadmin' && ($user['license_status'] === 'suspended')) {
            $reason = !empty($user['school_comment']) ? " Message : " . $user['school_comment'] : " Veuillez contacter l'administration.";
            echo json_encode(["status" => "error", "message" => "L'accès de votre établissement est suspendu." . $reason]);
            exit;
        }

        // Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['school_id'] = $user['school_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['user_photo'] = (!empty($user['photo'])) ? $user['photo'] : '/saas/assets/images/user/1.png';
        
        // Fallback robuste pour l'école
        $_SESSION['school_name'] = (!empty($user['school_name'])) ? $user['school_name'] : 'EcoleSaaS';
        $_SESSION['school_logo'] = (!empty($user['school_logo'])) ? $user['school_logo'] : '/saas/assets/images/logo.png';

        // Permissions SaaS
        $_SESSION['plan_name'] = $user['plan_name'] ?? 'FREE';
        $_SESSION['has_bulletins'] = (int)($user['has_bulletins'] ?? 0);
        $_SESSION['has_parents'] = (int)($user['has_parents'] ?? 0);
        $_SESSION['has_stats'] = (int)($user['has_stats'] ?? 0);

        echo json_encode([
            "status" => "success",
            "message" => "Connexion réussie",
            "data" => [
                "id" => $user['id'],
                "role" => $user['role'],
                "plan" => $_SESSION['plan_name']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Identifiants incorrects"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur de base de données"]);
}
?>
