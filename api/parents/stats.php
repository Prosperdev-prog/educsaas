<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id']; // Parent's user_id

try {
    // 1. Get linked children
    $stmtChildren = $conn->prepare("
        SELECT e.id, e.nom, e.prenom, c.nom as classe_nom 
        FROM parent_eleve pe
        JOIN eleves e ON pe.eleve_id = e.id
        LEFT JOIN classes c ON e.classe_id = c.id
        WHERE pe.parent_id = ? AND e.school_id = ?
    ");
    $stmtChildren->execute([$user_id, $school_id]);
    $children = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

    // 2. Latest 5 notes for any of those children
    $recent_notes = [];
    if (count($children) > 0) {
        $eleve_ids = array_column($children, 'id');
        $in = str_repeat('?,', count($eleve_ids) - 1) . '?';
        $stmtRecent = $conn->prepare("
            SELECT n.note, e.prenom as eleve_prenom, m.nom as matiere_nom, n.date_evaluation
            FROM notes n
            JOIN eleves e ON n.eleve_id = e.id
            JOIN matieres m ON n.matiere_id = m.id
            WHERE n.eleve_id IN ($in) AND n.school_id = ?
            ORDER BY n.date_evaluation DESC LIMIT 5
        ");
        $stmtRecent->execute([...$eleve_ids, $school_id]);
        $recent_notes = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Count alerts (absences)
    $total_absences = 0;
    if (count($children) > 0) {
        $eleve_ids = array_column($children, 'id');
        $in = str_repeat('?,', count($eleve_ids) - 1) . '?';
        $stmtAbs = $conn->prepare("SELECT COUNT(*) FROM presences WHERE eleve_id IN ($in) AND statut = 'absent'");
        $stmtAbs->execute([...$eleve_ids]);
        $total_absences = $stmtAbs->fetchColumn();
    }

    echo json_encode([
        "status" => "success",
        "data" => [
            "children" => $children,
            "recent_notes" => $recent_notes,
            "total_absences" => $total_absences
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
