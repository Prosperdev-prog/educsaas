<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Accès refusé"]);
    exit;
}

$school_id = $_SESSION['school_id'];

if (isset($_FILES['logo'])) {
    $file = $_FILES['logo'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = "logo_" . $school_id . "_" . time() . "." . $ext;
    $targetDir = "../../assets/images/schools/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $targetPath = $targetDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $logoUrl = "/saas/assets/images/schools/" . $newName;
        
        try {
            // Mise à jour de la colonne 'logo' (selon le schéma SQL)
            $stmt = $conn->prepare("UPDATE schools SET logo = ? WHERE id = ?");
            $stmt->execute([$logoUrl, $school_id]);
            
            // Mise à jour de la session pour un affichage immédiat
            $_SESSION['school_logo'] = $logoUrl;
        } catch (PDOException $e) {
             echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
             exit;
        }

        echo json_encode(["status" => "success", "logo_url" => $logoUrl]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors du transfert du fichier."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Aucun fichier reçu."]);
}
?>
