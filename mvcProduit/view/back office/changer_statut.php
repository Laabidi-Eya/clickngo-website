<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("mysql:host=localhost;dbname=click'n'go", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $idCommande = $_POST['id_commande'];
    $nouveauStatut = $_POST['nouveau_statut'];

    $stmt = $pdo->prepare("UPDATE commandes SET statut_commande = :statut WHERE id_commande = :id");
    $stmt->bindParam(':statut', $nouveauStatut);
    $stmt->bindParam(':id', $idCommande);
    $stmt->execute();

    header("Location: indeex.php");
    exit;
}
?>
