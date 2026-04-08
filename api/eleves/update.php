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

$id = $data['id'] ?? null;
$matricule = $data['matricule'] ?? '';
$nom = $data['nom'] ?? '';
$prenom = $data['prenom'] ?? '';
$classe_id = $data['classe_id'] ?? null;
$sexe = $data['sexe'] ?? 'M';
$date_naissance = $data['date_naissance'] ?? null;
$adresse = $data['adresse'] ?? '';
$nom_parent = $data['nom_parent'] ?? '';
$telephone_parent = $data['telephone_parent'] ?? '';
$parent_id = !empty($data['parent_id']) ? $data['parent_id'] : null;

if (empty($id) || empty($matricule) || empty($nom) || empty($prenom)) {
    echo json_encode(["status" => "error", "message" => "ID, Matricule, nom et prénom sont obligatoires"]);
    exit;
}

if ($classe_id === '') $classe_id = null;

try {
    // Check for duplicate matricule for other students
    $stmt = $conn->prepare("SELECT id FROM eleves WHERE matricule = ? AND school_id = ? AND id != ?");
    $stmt->execute([$matricule, $school_id, $id]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Ce matricule est déjà utilisé par un autre élève."]);
        exit;
    }

    $sql = "UPDATE eleves 
            SET matricule=?, nom=?, prenom=?, classe_id=?, sexe=?, date_naissance=?, adresse=?, nom_parent=?, telephone_parent=?
            WHERE id=? AND school_id=?";
            
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$matricule, $nom, $prenom, $classe_id, $sexe, $date_naissance, $adresse, $nom_parent, $telephone_parent, $id, $school_id])) {
        // Mettre à jour la table pivot (suppression puis insertion)
        $conn->prepare("DELETE FROM parent_eleve WHERE eleve_id = ? AND school_id = ?")->execute([$id, $school_id]);
        if ($parent_id) {
            $conn->prepare("INSERT INTO parent_eleve (school_id, parent_id, eleve_id) VALUES (?, ?, ?)")->execute([$school_id, $parent_id, $id]);
        }
        
        echo json_encode(["status" => "success", "message" => "Élève mis à jour avec succès", "data" => []]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données: " . $e->getMessage()]);
}
?>
