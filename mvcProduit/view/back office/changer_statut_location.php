<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("mysql:host=localhost;dbname=clickngo_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_POST['id_location'];
    $nouveauStatut = $_POST['nouveau_statut'];

    $stmt = $pdo->prepare("UPDATE louer SET statut_location = :statut WHERE id = :id");
    $stmt->bindParam(':statut', $nouveauStatut);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: indeex.php");
    exit;
}
?>
