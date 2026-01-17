<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clickngo_db";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Récupérer l'ID de l'activité à supprimer
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

  // Supprimer l'activité
  $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
  $stmt->execute([$id]);

  // Rediriger vers le dashboard
  header("Location: dashboard.php");
} catch (PDOException $e) {
  echo "Erreur : " . $e->getMessage();
}
$conn = null;
?>