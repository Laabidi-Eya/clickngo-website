# Prérequis – ClickNGo

Ce document liste les exigences pour faire tourner le projet **ClickNGo** en local ou en déploiement.

---

## Environnement

- **PHP** : 7.4 ou supérieur (recommandé : 8.0+)
- **MySQL** : 5.7 ou supérieur (ou MariaDB 10.2+)
- **Serveur web** : Apache (XAMPP, WAMP, LAMP) avec `mod_rewrite` activé si besoin
- **Composer** : [getcomposer.org](https://getcomposer.org) (pour les dépendances PHP)

---

## Extensions PHP requises

À activer dans `php.ini` (décommenter la ligne correspondante) :

| Extension   | Usage principal                    |
|------------|------------------------------------|
| `pdo_mysql`| Connexion base de données          |
| `mysqli`   | Connexion MySQL (certains scripts) |
| `curl`     | Cloudinary, APIs externes          |
| `json`     | Données JSON                       |
| `mbstring` | Chaînes UTF-8                      |
| `openssl`  | Connexions HTTPS, Stripe           |

Sous XAMPP, ces extensions sont souvent déjà activées.

---

## Dépendances PHP (Composer)

À la racine du projet :

```bash
composer install
```

Le `composer.json` racine inclut notamment :

- `stripe/stripe-php` (paiements)

D’autres parties du projet (ex. **mvcact**) utilisent :

- **PHPMailer** (inclus manuellement dans `mvcUtilisateur` / vues)
- **Cloudinary** : pas de SDK Composer, utilisation de l’API REST (PHP + cURL)

---

## Base de données

1. Créer une base MySQL (ex. `clickngo_db` ou `click'n'go` selon la config utilisée).
2. Importer le schéma et les données :
   - `mvcact/sql/clickngo_db.sql`
   - `mvcact/sql/enterprise_activities_table.sql` (si utilisé)

**Note :** Le nom de la base n’est pas partout le même dans le projet (`clickngo_db` vs `click'n'go`). Pour éviter les erreurs, utiliser le même nom partout et le renseigner dans votre configuration (fichier de config ou `.env`).

---

## Services optionnels / tiers

- **Cloudinary** : stockage d’images (compte gratuit possible). Config dans `mvcact/cloudinary_config.php` (à terme, préférer des variables d’environnement).
- **Stripe** : clés API pour les paiements (mode test pour le dev).
- **SMTP** : pour l’envoi d’emails (réservations, contact). Config PHPMailer dans les vues concernées.

---

## Résumé rapide (XAMPP)

1. Installer XAMPP, démarrer Apache et MySQL.
2. Copier le projet dans `htdocs` (ex. `C:\xampp\htdocs\clickngo-website`).
3. Créer la base et importer les SQL du dossier `mvcact/sql/`.
4. Exécuter `composer install` à la racine.
5. Configurer la base et, si besoin, Cloudinary (voir `.env.example`).
6. Accéder à l’app via l’URL indiquée dans le README (ex. module Activité / front office).

---

Pour les étapes détaillées d’installation et d’exécution, voir le **README.md** à la racine du projet.
