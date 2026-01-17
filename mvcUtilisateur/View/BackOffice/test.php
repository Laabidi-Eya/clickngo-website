<?php
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../Controller/UserController.php");

$userController = new UserController();

// Remplace cet ID par un ID valide dans ta base de données
$idTest = 105;

$result = $userController->supprimerUser($idTest);

if ($result) {
    echo "✅ Utilisateur ID $idTest supprimé avec succès.";
} else {
    echo "❌ Échec de la suppression de l'utilisateur ID $idTest.";
}
