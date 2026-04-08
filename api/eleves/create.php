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

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    echo json_encode(["status" => "error", "message" => "Accès refusé. Seul l'administration peut inscrire des élèves."]);
    exit;
}

$school_id = $_SESSION['school_id'];
$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) $data = $_POST;

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

if (empty($matricule) || empty($nom) || empty($prenom)) {
    echo json_encode(["status" => "error", "message" => "Matricule, nom et prénom sont obligatoires"]);
    exit;
}

// Convert empty strings for optional float/int fields to null to avoid constraint errors
if ($classe_id === '') $classe_id = null;

try {
    // --- LOGIQUE SAAS : VÉRIFIER LE QUOTA DU PLAN ---
    $stmtPlan = $conn->prepare("
        SELECT p.max_students, (SELECT COUNT(*) FROM eleves WHERE school_id = ?) as current_students
        FROM schools s
        LEFT JOIN subscriptions sub ON sub.school_id = s.id AND sub.status = 'active'
        LEFT JOIN plans p ON p.id = sub.plan_id
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmtPlan->execute([$school_id, $school_id]);
    $planInfo = $stmtPlan->fetch();

    if ($planInfo && $planInfo['current_students'] >= $planInfo['max_students']) {
        echo json_encode([
            "status" => "error", 
            "message" => "Quota d'élèves atteint (max: ".$planInfo['max_students']."). Veuillez passer au plan PREMIUM pour ajouter plus d'élèves."
        ]);
        exit;
    }
    // --- FIN LOGIQUE SAAS ---

    // Check for duplicate matricule
    $stmt = $conn->prepare("SELECT id FROM eleves WHERE matricule = ? AND school_id = ?");
    $stmt->execute([$matricule, $school_id]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Ce matricule existe déjà."]);
        exit;
    }

    $sql = "INSERT INTO eleves (school_id, matricule, nom, prenom, classe_id, sexe, date_naissance, adresse, nom_parent, telephone_parent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$school_id, $matricule, $nom, $prenom, $classe_id, $sexe, $date_naissance, $adresse, $nom_parent, $telephone_parent])) {
        $eleve_id = $conn->lastInsertId();
        
        // Insérer dans la table pivot parent_eleve
        if ($parent_id) {
            $stmtPivot = $conn->prepare("INSERT INTO parent_eleve (school_id, parent_id, eleve_id) VALUES (?, ?, ?)");
            $stmtPivot->execute([$school_id, $parent_id, $eleve_id]);
        }
        
        echo json_encode(["status" => "success", "message" => "Élève ajouté avec succès", "data" => []]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur base de données: " . $e->getMessage()]);
}
?>
