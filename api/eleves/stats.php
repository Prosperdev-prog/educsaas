<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];

try {
    // 1. Get Eleve info
    $stmt = $conn->prepare("SELECT id, classe_id FROM eleves WHERE user_id = ? AND school_id = ?");
    $stmt->execute([$user_id, $school_id]);
    $eleve = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$eleve) {
        echo json_encode(["status" => "error", "message" => "Élève introuvable"]);
        exit;
    }

    $eleve_id = $eleve['id'];
    $classe_id = $eleve['classe_id'];

    // 2. Last 3 notes
    $stmtNotes = $conn->prepare("
        SELECT n.note, m.nom as matiere_nom, n.date_evaluation 
        FROM notes n
        JOIN matieres m ON n.matiere_id = m.id
        WHERE n.eleve_id = ? 
        ORDER BY n.date_evaluation DESC LIMIT 3
    ");
    $stmtNotes->execute([$eleve_id]);
    $notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

    // 3. Upcoming devoirs (next 3)
    $upcoming_devoirs = [];
    if($classe_id) {
        $stmtDevoirs = $conn->prepare("
            SELECT d.titre, m.nom as matiere_nom, d.date_limite 
            FROM devoirs d
            JOIN matieres m ON d.matiere_id = m.id
            WHERE d.classe_id = ? AND d.date_limite >= CURDATE()
            ORDER BY d.date_limite ASC LIMIT 3
        ");
        $stmtDevoirs->execute([$classe_id]);
        $upcoming_devoirs = $stmtDevoirs->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Summaries
    $stmtCountNotes = $conn->prepare("SELECT COUNT(*) FROM notes WHERE eleve_id = ?");
    $stmtCountNotes->execute([$eleve_id]);
    $total_notes = $stmtCountNotes->fetchColumn();

    $stmtCountDevoirs = $conn->prepare("SELECT COUNT(*) FROM devoirs WHERE classe_id = ? AND date_limite >= CURDATE()");
    $stmtCountDevoirs->execute([$classe_id]);
    $total_devoirs = $stmtCountDevoirs->fetchColumn();

    echo json_encode([
        "status" => "success", 
        "data" => [
            "total_notes" => $total_notes,
            "total_devoirs" => $total_devoirs,
            "recent_notes" => $notes,
            "upcoming_devoirs" => $upcoming_devoirs
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
