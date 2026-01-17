<?php
// Activer le rapport d'erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log des erreurs
error_log("Début du script d'export à la racine - Version complète");

require_once __DIR__ . '/controller/ReservationController.php';

// Vérifier l'authentification (à adapter selon ton système)
session_start();

// Désactiver temporairement la vérification d'authentification pour le débogage
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: view/back office/login.php");
//     exit;
// }

try {
    // Créer une instance du contrôleur de réservation
    $reservationController = new ReservationController();
    
    // Récupérer TOUTES les réservations sans filtrage
    $reservations = $reservationController->getAllReservations();
    error_log("Nombre total de réservations récupérées : " . count($reservations));
    
    // Définir le nom du fichier Excel
    $filename = "Toutes_Reservations_" . date('Y-m-d') . ".xls";
    
    // En-têtes pour forcer le téléchargement
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Début du document Excel
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Toutes les Réservations</title>
        <style>
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .header { background-color: #e70a83; color: white; font-weight: bold; }
        </style>
    </head>
    <body>
        <h1>Toutes les Réservations ClickNGo</h1>
        <table>
            <tr class='header'>
                <th>ID</th>
                <th>Client</th>
                <th>Email</th>
                <th>Activité</th>
                <th>Date</th>
                <th>Horaire</th>
                <th>Personnes</th>
                <th>Total</th>
                <th>Statut</th>
            </tr>";
    
    // Afficher les données des réservations
    if (!empty($reservations)) {
        foreach ($reservations as $reservation) {
            echo "<tr>
                <td>" . htmlspecialchars($reservation['id']) . "</td>
                <td>" . htmlspecialchars($reservation['customer_name']) . "</td>
                <td>" . htmlspecialchars($reservation['customer_email']) . "</td>
                <td>" . htmlspecialchars($reservation['activity_name']) . "</td>
                <td>" . htmlspecialchars($reservation['reservation_date']) . "</td>
                <td>" . htmlspecialchars($reservation['reservation_time']) . "</td>
                <td>" . htmlspecialchars($reservation['people_count']) . "</td>
                <td>" . htmlspecialchars($reservation['total_price']) . " DT</td>
                <td>" . htmlspecialchars(ucfirst($reservation['payment_status'])) . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='9' style='text-align:center;'>Aucune réservation trouvée</td></tr>";
    }
    
    // Fin du document Excel
    echo "</table>
    </body>
    </html>";
    
} catch (Exception $e) {
    error_log("Erreur lors de l'export Excel : " . $e->getMessage());
    echo "Erreur lors de l'export Excel : " . $e->getMessage();
    exit;
} 