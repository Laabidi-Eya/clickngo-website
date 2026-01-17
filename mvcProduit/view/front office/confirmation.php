<?php
// confirmation.php
session_start();

// Vérifiez si l'utilisateur est redirigé après l'insertion
if (!isset($_SESSION['location_success'])) {
    // Si l'enregistrement échoue ou si la session n'est pas configurée, redirigez vers la page d'accueil.
    header("Location: produit.php");
    exit;
}

// Assurez-vous que les données de location sont disponibles pour le reçu
if (!isset($_SESSION['location_data']) && isset($_SESSION['temp_location_data'])) {
    $_SESSION['location_data'] = $_SESSION['temp_location_data'];
    unset($_SESSION['temp_location_data']);
}

// Définir la session de succès pour éviter que la page ne s'affiche à nouveau après actualisation
unset($_SESSION['location_success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 50px;
            text-align: center;
            background-color: #f2f2f2;
        }
        .message {
            padding: 30px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a {
            display: block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .receipt-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 25px;
            background: linear-gradient(90deg, #ff8fa3, #c084fc);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .receipt-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="message">
        <h2>✅ Location enregistrée avec succès !</h2>
        <p>Merci pour votre confiance.</p>
        <a href="produit.php" class="return-link">Retour à l'accueil</a>
        <a href="recu_location.php" class="receipt-btn">Reçu location</a>
    </div>
</body>
</html>
