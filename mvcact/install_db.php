<?php
/**
 * Script d'installation de la base clickngo_db.
 * À ouvrir une fois dans le navigateur : http://localhost:8000/mvcact/install_db.php
 * Puis supprimer ce fichier ou ne plus y accéder.
 */
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'clickngo_db';
$sqlFile = __DIR__ . '/sql/clickngo_db.sql';

if (!file_exists($sqlFile)) {
    die('Fichier SQL introuvable : ' . $sqlFile);
}

$mysqli = @new mysqli($host, $user, $pass);
if ($mysqli->connect_error) {
    die('<p style="color:red;">Connexion MySQL impossible. Vérifiez que MySQL (XAMPP) est démarré.<br>Erreur : ' . $mysqli->connect_error . '</p>');
}

$mysqli->set_charset('utf8');

// Créer la base
if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname`")) {
    die('<p style="color:red;">Impossible de créer la base : ' . $mysqli->error . '</p>');
}
$mysqli->select_db($dbname);

// Exécuter le fichier SQL (requêtes multiples)
$sql = file_get_contents($sqlFile);
// Enlever les commentaires en début de ligne qui gênent parfois
$sql = preg_replace('/^--.*$/m', '', $sql);

if (!$mysqli->multi_query($sql)) {
    die('<p style="color:red;">Erreur lors de l\'import SQL : ' . $mysqli->error . '</p>');
}

// Vider les résultats multiples
do {
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

$mysqli->close();

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Installation OK</title></head><body>';
echo '<h1 style="color:green;">Base de données installée avec succès</h1>';
echo '<p>La base <strong>' . htmlspecialchars($dbname) . '</strong> a été créée et les tables ont été importées.</p>';
echo '<p><a href="/mvcact/view/front%20office/activite.php">Aller à la page Activités</a></p>';
echo '</body></html>';
