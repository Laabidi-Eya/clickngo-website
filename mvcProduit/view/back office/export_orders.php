<?php
session_start();
require_once '../../config.php';

// Vérifier si l'utilisateur est connecté (à adapter selon votre système d'authentification)
// if (!isset($_SESSION['id_user'])) {
//     header('Location: login.php');
//     exit;
// }

// Connexion à la base de données
$pdo = Config::getConnexion();

// Récupérer les commandes d'achat
$achats = $pdo->query("
    SELECT c.id_commande, c.id_user, c.id_produit, p.name as produit_nom, c.quantite, 
           c.date_commande, c.statut_commande, (p.price * c.quantite) as prix_total
    FROM commandes c
    LEFT JOIN products p ON c.id_produit = p.id
    ORDER BY c.date_commande DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les locations
$locations = $pdo->query("
    SELECT l.id, l.id_user, l.produit, l.nom, l.prenom, l.date_location, 
           l.heure_debut, l.heure_fin, l.telephone, l.statut_location, 
           l.created_at
    FROM louer l
    ORDER BY l.date_location DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Définir les en-têtes pour forcer le téléchargement
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="commandes_export_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

// Créer le contenu du fichier Excel au format HTML (compatible avec Excel)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export des Commandes - Click'N'Go</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 20px;
            background-color: #FFFFFF;
            color: #333333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            border: none;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border: none;
            border-bottom: 1px solid #E1E1E1;
        }
        th {
            background: linear-gradient(135deg, #FF69B4, #9370DB);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11pt;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background-color: #F8F9FC;
        }
        tr:hover {
            background-color: #FCE4EC;
            transition: background-color 0.3s ease;
        }
        .sheet-title {
            font-size: 20pt;
            font-weight: bold;
            margin: 25px 0 15px 0;
            color: #9370DB;
            border-bottom: 3px solid #FF69B4;
            padding-bottom: 8px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        .statut-en_attente, .statut-en-attente {
            color: #FF9800;
            font-weight: 600;
            background-color: rgba(255, 152, 0, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        .statut-confirmee {
            color: #4CAF50;
            font-weight: 600;
            background-color: rgba(76, 175, 80, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        .statut-livree {
            color: #2E7D32;
            font-weight: 600;
            background-color: rgba(46, 125, 50, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        .statut-annulee {
            color: #F44336;
            font-weight: 600;
            background-color: rgba(244, 67, 54, 0.1);
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
        }
        .export-info {
            margin-bottom: 25px;
            font-size: 11pt;
            color: #666;
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 6px;
            display: inline-block;
            border-left: 4px solid #9370DB;
        }
        .header-logo {
            position: relative;
            font-size: 32pt;
            font-weight: 800;
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: linear-gradient(90deg, #FF69B4, #9370DB, #4169E1, #9370DB, #FF69B4);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient 5s ease infinite;
            display: inline-block;
            width: 100%;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .header-logo::before {
            content: "";
            position: absolute;
            top: 0;
            left: 25%;
            right: 25%;
            height: 5px;
            background: linear-gradient(90deg, transparent, #FF69B4, #9370DB, #FF69B4, transparent);
            border-radius: 5px;
        }
        
        .header-logo::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 25%;
            right: 25%;
            height: 5px;
            background: linear-gradient(90deg, transparent, #9370DB, #FF69B4, #9370DB, transparent);
            border-radius: 5px;
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 15px;
            color: #9370DB;
            font-size: 11pt;
            border-top: 1px solid #E1E1E1;
        }
    </style>
</head>
<body>
    <div class="header-logo">✦ Click'N'Go ✦</div>
    <div class="export-info">Rapport généré le <?= date('d/m/Y à H:i') ?></div>
    
    <!-- Feuille des commandes d'achat -->
    <div class="sheet-title">Commandes d'achat</div>
    <table>
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>ID Utilisateur</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Total</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achats as $achat): 
                $statutClass = 'statut-' . $achat['statut_commande'];
            ?>
            <tr>
                <td><?= $achat['id_commande'] ?></td>
                <td><?= $achat['id_user'] ?></td>
                <td><?= htmlspecialchars($achat['produit_nom']) ?></td>
                <td><?= $achat['quantite'] ?></td>
                <td><?= number_format($achat['prix_total'], 2) ?> TND</td>
                <td><?= $achat['date_commande'] ?></td>
                <td class="<?= $statutClass ?>"><?= $achat['statut_commande'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Feuille des locations -->
    <div class="sheet-title">Locations</div>
    <table>
        <thead>
            <tr>
                <th>ID Location</th>
                <th>ID Utilisateur</th>
                <th>Produit</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date</th>
                <th>Heure Début</th>
                <th>Heure Fin</th>
                <th>Téléphone</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $location): 
                $statutClass = 'statut-' . $location['statut_location'];
            ?>
            <tr>
                <td><?= $location['id'] ?></td>
                <td><?= $location['id_user'] ?></td>
                <td><?= htmlspecialchars($location['produit']) ?></td>
                <td><?= htmlspecialchars($location['nom']) ?></td>
                <td><?= htmlspecialchars($location['prenom']) ?></td>
                <td><?= $location['date_location'] ?></td>
                <td><?= $location['heure_debut'] ?></td>
                <td><?= $location['heure_fin'] ?></td>
                <td><?= $location['telephone'] ?></td>
                <td class="<?= $statutClass ?>"><?= $location['statut_location'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        © <?= date('Y') ?> Click'N'Go - Tous droits réservés
    </div>
</body>
</html> 