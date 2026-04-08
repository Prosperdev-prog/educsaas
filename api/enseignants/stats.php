<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db.php';

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    echo json_encode(["status" => "error", "message" => "Non autorisé"]);
    exit;
}

$school_id = $_SESSION['school_id'];
$user_id = $_SESSION['user_id'];

try {
    // 1. Get assigned classes count
    $stmtClasses = $conn->prepare("SELECT COUNT(*) FROM enseignant_classes WHERE enseignant_id = ?");
    $stmtClasses->execute([$user_id]);
    $total_classes = $stmtClasses->fetchColumn();

    // 2. Get list of assigned classes for display
    $stmtClassesList = $conn->prepare("
        SELECT c.id, c.nom, c.niveau 
        FROM enseignant_classes ec 
        JOIN classes c ON ec.classe_id = c.id 
        WHERE ec.enseignant_id = ? AND c.school_id = ?
    ");
    $stmtClassesList->execute([$user_id, $school_id]);
    $classes_list = $stmtClassesList->fetchAll(PDO::FETCH_ASSOC);

    // 3. Count total students in those classes
    $total_students = 0;
    if (count($classes_list) > 0) {
        $classe_ids = array_column($classes_list, 'id');
        $in = str_repeat('?,', count($classe_ids) - 1) . '?';
        $stmtStudents = $conn->prepare("SELECT COUNT(*) FROM eleves WHERE classe_id IN ($in) AND school_id = ?");
        $stmtStudents->execute([...$classe_ids, $school_id]);
        $total_students = $stmtStudents->fetchColumn();
    }

    // 4. Recently added notes by this teacher (using created_at as we don't have teacher_id in notes table yet, but let's assume notes added in school)
    // To be more precise, let's assume teachers can see notes for their classes.
    $recent_activity = [];
    if (count($classes_list) > 0) {
        $classe_ids = array_column($classes_list, 'id');
        $in = str_repeat('?,', count($classe_ids) - 1) . '?';
        $stmtRecent = $conn->prepare("
            SELECT n.note, e.nom, e.prenom, m.nom as matiere_nom, n.created_at
            FROM notes n
            JOIN eleves e ON n.eleve_id = e.id
            JOIN matieres m ON n.matiere_id = m.id
            WHERE e.classe_id IN ($in) AND n.school_id = ?
            ORDER BY n.created_at DESC LIMIT 5
        ");
        $stmtRecent->execute([...$classe_ids, $school_id]);
        $recent_activity = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "status" => "success",
        "data" => [
            "total_classes" => $total_classes,
            "total_students" => $total_students,
            "classes" => $classes_list,
            "recent_activity" => $recent_activity
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
