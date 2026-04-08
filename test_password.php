<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Mot de passe à hasher
$password = 'admin123';

// Génération du hash
$hash = password_hash($password, PASSWORD_DEFAULT);

// Affichage
echo "<h3>Mot de passe : $password</h3>";
echo "<h3>Hash généré :</h3>";
echo "<textarea rows='4' cols='80'>$hash</textarea>";