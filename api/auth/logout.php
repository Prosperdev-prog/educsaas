<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
session_unset();
session_destroy();

header("Access-Control-Allow-Origin: *");

echo json_encode([
    "status" => "success",
    "message" => "Déconnexion réussie"
]);
?>
