<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = "user_" . $user_id . "_" . time() . "." . $ext;
    $targetDir = "../../assets/images/profile/";
    
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    $targetPath = $targetDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $photoUrl = "/saas/assets/images/profile/" . $newName;
        
        try {
            $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute([$photoUrl, $user_id]);
            
            $_SESSION['user_photo'] = $photoUrl;
            echo json_encode(["status" => "success", "photo_url" => $photoUrl]);
        } catch (PDOException $e) {
             echo json_encode(["status" => "error", "message" => "Erreur DB"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur transfert"]);
    }
}
?>
