<?php


require_once(__DIR__ . "/../model/CommandModel.php");
require_once __DIR__ . '/../config.php';

class CommandController {
    private $db;
    private $modelClass = 'CommandModel';

    public function __construct(PDO $db = null) {
        if ($db === null) {
            $this->db = Config::getConnexion();
        } else {
            $this->db = $db;
        }
    }

public function ajouterCommandeDepuisStripe($orderDetails) {
    try {
        $db = Config::getConnexion();

        // Récupérer l'ID de l'utilisateur depuis la session
        $id_user = $_SESSION['user']['id_user'] ?? null;
        
        if (!$id_user) {
            throw new Exception("Utilisateur non connecté");
        }

        // Pour chaque produit dans le panier, créer une entrée dans la table commandes
        foreach ($orderDetails['panier'] as $produit) {
            $stmt = $db->prepare("INSERT INTO commandes (
                id_user,
                id_produit, 
                quantite,
                date_commande,
                statut_commande
            ) VALUES (
                :id_user,
                :id_produit,
                :quantite,
                NOW(),
                'en_attente'
            )");
            
            $stmt->execute([
                'id_user' => $id_user,
                'id_produit' => $produit['id'],
                'quantite' => $produit['quantite']
            ]);
        }

        return true;

    } catch (PDOException $e) {
        error_log("Erreur lors de l'insertion de la commande Stripe : " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Erreur : " . $e->getMessage());
        return false;
    }
}
    public function create() {
        include 'views/command/create.php';
    }

    public function show($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }
        $commande = CommandModel::getById($id, $this->db);
        if ($commande) {
            include 'views/command/show.php';
        } else {
            echo "Commande non trouvée.";
        }
    }

    public function edit($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }
        $commande = CommandModel::getById($id, $this->db);
        if ($commande) {
            include 'views/command/edit.php';
        } else {
            echo "Commande non trouvée.";
        }
    }

    public function update($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo "Requête invalide.";
            return;
        }

        try {
            $id_user = filter_input(INPUT_POST, 'id_user', FILTER_SANITIZE_NUMBER_INT);
            $id_produit = filter_input(INPUT_POST, 'id_produit', FILTER_SANITIZE_NUMBER_INT);
            $quantite = filter_input(INPUT_POST, 'quantite', FILTER_SANITIZE_NUMBER_INT);
            $statut_commande = filter_input(INPUT_POST, 'statut_commande', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$id_user || !$id_produit || !$quantite) {
                throw new InvalidArgumentException("Tous les champs sont requis et doivent être valides.");
            }

            $commande = new CommandModel($id_user, $id_produit, $quantite, null, $id);

            if (CommandModel::mettreAJour($commande, $this->db)) {
                header('Location: index.php?action=show&id=' . $id . '&success=commande_mise_a_jour');
                exit();
            } else {
                echo "Erreur lors de la mise à jour de la commande.";
            }
        } catch (InvalidArgumentException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    public function delete($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            echo "ID de commande invalide.";
            return;
        }

        if (CommandModel::supprimer($id, $this->db)) {
            header('Location: index.php?action=list&success=commande_supprimee');
            exit();
        } else {
            echo "Erreur lors de la suppression de la commande.";
        }
    }

public function ajouterCommande() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifie que le formulaire est bien soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajouter_commande') {
        // Nettoyage des entrées utilisateur
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_SPECIAL_CHARS);
        $quantite = filter_input(INPUT_POST, 'quantite', FILTER_SANITIZE_NUMBER_INT);
        $prix_total = filter_input(INPUT_POST, 'prix_total_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $paiement = filter_input(INPUT_POST, 'paiement', FILTER_SANITIZE_SPECIAL_CHARS);
        $panier_data = json_decode($_POST['panier_data'] ?? '[]', true);

        // Validation des données obligatoires
        if (!$nom || !$prenom || !$email || !$telephone || !$quantite || !$prix_total || !$paiement || empty($panier_data)) {
            header('Location: /projet Web/mvcProduit/view/front office/panier.php?error=donnees_invalides');
            exit;
        }

        // Vérifie que l'utilisateur est connecté
        $idUtilisateur = $_SESSION['user']['id_user'] ?? null;
        if (!$idUtilisateur) {
            error_log("❌ Utilisateur non connecté ou session invalide.");
            header('Location: /projet Web/mvcProduit/view/front office/panier.php?error=utilisateur_non_connecte');
            exit;
        }

        // Sauvegarde temporaire pour commande_success.php
        $_SESSION['order_details'] = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone,
            'quantite' => (int) $quantite,
            'prix_total' => (float) $prix_total,
            'paiement' => $paiement,
            'panier' => $panier_data
        ];

        // Traitement de la commande
        try {
            if (CommandModel::creerCommandeDepuisTableau($idUtilisateur, $panier_data, $this->db)) {
                $_SESSION['derniere_commande'] = [
                    'client' => compact('nom', 'prenom', 'email', 'telephone'),
                    'produits' => $panier_data,
                    'total' => $prix_total,
                    'paiement' => $paiement
                ];

                // Envoi d'email de confirmation
                try {
                    $mail = Config::getMailer();
                    $mail->addAddress($email, "$nom $prenom");
                    $mail->isHTML(true);
                    $mail->Subject = 'Confirmation de votre commande';

                    $itemsList = '';
                    foreach ($panier_data as $item) {
                        $lineTotal = $item['prix'] * $item['quantite'];
                        $itemsList .= "<li>{$item['nom']} x{$item['quantite']} - {$lineTotal} TND</li>";
                    }

                    $mail->Body = "
                        <h2>Merci pour votre commande !</h2>
                        <p>Voici les détails de votre commande :</p>
                        <ul>
                            <li><strong>Nom :</strong> $nom</li>
                            <li><strong>Prénom :</strong> $prenom</li>
                            <li><strong>Email :</strong> $email</li>
                            <li><strong>Téléphone :</strong> $telephone</li>
                            <li><strong>Quantité :</strong> $quantite</li>
                            <li><strong>Total :</strong> $prix_total TND</li>
                            <li><strong>Paiement :</strong> $paiement</li>
                        </ul>
                        <h3>Produits :</h3>
                        <ul>$itemsList</ul>
                        <p>Nous vous contacterons prochainement.</p>
                    ";
                    $mail->AltBody = strip_tags($mail->Body);
                    $mail->send();

                } catch (Exception $e) {
                    error_log("❌ Erreur email : " . $e->getMessage());
                }

                $_SESSION['commande_success'] = true;
                header('Location: /projet Web/mvcProduit/view/front office/commande_success.php');
                exit;
            } else {
                error_log("❌ Échec insertion base.");
                header('Location: /projet Web/mvcProduit/view/front office/panier.php?error=erreur_commande');
                exit;
            }
        } catch (Exception $e) {
            error_log("❌ Exception : " . $e->getMessage());
            header('Location: /projet Web/mvcProduit/view/front office/panier.php?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    // Si ce n'est pas un POST avec action valide
    header('Location: /projet Web/mvcProduit/view/front office/panier.php');
    exit;
}

}

// Routeur
if (isset($_GET['action']) || isset($_POST['action'])) {
    require_once '../config.php';
    $db = Config::getConnexion();
    $controller = new CommandController($db);
    $action = $_GET['action'] ?? $_POST['action'];
    $id = $_GET['id'] ?? $_POST['id'] ?? null;

    switch ($action) {
        case 'create':
            $controller->create();
            break;
        case 'show':
            $controller->show($id);
            break;
        case 'edit':
            $controller->edit($id);
            break;
        case 'update':
            $controller->update($id);
            break;
        case 'delete':
            $controller->delete($id);
            break;
        case 'ajouter_commande':
            $controller->ajouterCommande();
            break;
        default:
            echo "Action non reconnue.";
            break;
    }
} else {
    $db = Config::getConnexion();
    $controller = new CommandController($db);
}
?>