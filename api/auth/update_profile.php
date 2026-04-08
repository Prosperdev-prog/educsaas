<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

$nom = $data['nom'] ?? '';
$prenom = $data['prenom'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (empty($nom) || empty($prenom) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Nom, prénom et email sont obligatoires"]);
    exit;
}

try {
    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Cet email est déjà utilisé"]);
        exit;
    }

    $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, phone = ? WHERE id = ?";
    $params = [$nom, $prenom, $email, $phone, $user_id];
    
    if (!empty($password)) {
        $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, phone = ?, password = ? WHERE id = ?";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $params = [$nom, $prenom, $email, $phone, $hashed_password, $user_id];
    }

    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        // Mettre à jour la session
        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;
        
        echo json_encode(["status" => "success", "message" => "Profil mis à jour avec succès"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour"]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
