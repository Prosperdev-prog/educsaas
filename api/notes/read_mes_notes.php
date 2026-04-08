<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(["status" => "error", "message" => "Non autorisé"]); 
    exit; 
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

try {
    $eleves_ids = [];
    
    // Déterminer quels élèves appartiennent à cet utilisateur
    if ($role === 'eleve') {
        $stmt = $conn->prepare("SELECT id FROM eleves WHERE user_id = ? AND school_id = ?");
        $stmt->execute([$user_id, $school_id]);
        $res = $stmt->fetch();
        if ($res) $eleves_ids[] = $res['id'];
    } elseif ($role === 'parent') {
        $stmt = $conn->prepare("SELECT eleve_id FROM parent_eleve WHERE parent_id = ? AND school_id = ?");
        $stmt->execute([$user_id, $school_id]);
        $eleves_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        echo json_encode(["status" => "error", "message" => "Rôle non autorisé pour cette API"]);
        exit;
    }
    
    if (empty($eleves_ids)) {
        echo json_encode(["status" => "success", "data" => []]);
        exit;
    }

    // Préparer la clause IN (?, ?, ...)
    $placeholders = implode(',', array_fill(0, count($eleves_ids), '?'));
    $params = array_merge([$school_id], $eleves_ids);
    
    $sql = "
        SELECT 
            n.id, 
            n.note, 
            n.type, 
            n.date_evaluation, 
            n.commentaire,
            m.nom as matiere_nom,
            e.nom as eleve_nom,
            e.prenom as eleve_prenom
        FROM notes n
        JOIN matieres m ON n.matiere_id = m.id
        JOIN eleves e ON n.eleve_id = e.id
        WHERE n.school_id = ? AND n.eleve_id IN ($placeholders)
        ORDER BY n.date_evaluation DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $notes]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Erreur DB: " . $e->getMessage()]);
}
?>
