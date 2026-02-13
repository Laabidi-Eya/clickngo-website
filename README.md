# ClickNGo – Site web

## Description

ClickNGo est une plateforme web tunisienne de loisirs qui liste des activités par catégorie. Les utilisateurs peuvent réserver en ligne, explorer les événements, acheter des produits associés, utiliser le covoiturage et découvrir les sponsors. Des modules alimentés par l’IA personnalisent les recommandations.

---

## Fonctionnalités

- Réservation en ligne d’activités
- Découverte d’événements
- E-commerce de produits associés
- Intégration covoiturage
- Recommandations avec IA
- Liste des sponsors et partenaires

---

## Stack technique

- **Frontend** : HTML, CSS, JavaScript
- **Backend** : PHP, MySQL
- **Modules IA** : scripts Python / APIs
- **Serveur** : XAMPP (Apache + MySQL)

---

## Lancer le projet depuis le terminal (recommandé)

1. **MySQL doit tourner** (XAMPP : démarrer MySQL dans le panneau de contrôle, ou MySQL installé en service).
2. **Base de données** : créez la base `clickngo_db` dans phpMyAdmin et importez les fichiers dans `mvcact/sql/` (au moins `clickngo_db.sql`).
3. **À la racine du projet**, dans le terminal :
   ```bash
   composer install
   run.bat
   ```
   Ou sans script : `php -S localhost:8000` puis ouvrez dans le navigateur :  
   **http://localhost:8000/mvcUtilisateur/View/FrontOffice/index.php**
4. Le script **run.bat** ouvre le navigateur automatiquement. Pour arrêter le serveur : **Ctrl+C** dans le terminal.

**URLs utiles après démarrage :**
- Front utilisateur : http://localhost:8000/mvcUtilisateur/View/FrontOffice/index.php
- Module Activités : http://localhost:8000/mvcact/
- Activités (pages front) : http://localhost:8000/mvcact/view/front%20office/

---

## Lancer avec XAMPP (Apache)

1. **Installer XAMPP** depuis [https://www.apachefriends.org](https://www.apachefriends.org).
2. Copier le dossier du projet dans `C:\xampp\htdocs\clickngo-website` (ou équivalent).
3. Ouvrir le **XAMPP Control Panel** et démarrer **Apache** et **MySQL**.
4. Créer la base `clickngo_db` dans **phpMyAdmin** et importer les SQL du dossier `mvcact/sql/`.
5. À la racine : `composer install`.
6. Dans le navigateur : `http://localhost/clickngo-website/mvcUtilisateur/View/FrontOffice/index.php` (adapter le nom du dossier si besoin).

---

## Prérequis et configuration

- Voir **REQUIREMENTS.md** pour les versions PHP, extensions, MySQL et dépendances.
- Copier **.env.example** en **.env** et renseigner les variables (base de données, Cloudinary, Stripe, etc.) lorsque le projet lira la config depuis `.env`.

---

## Notes

- Vérifier que les versions de PHP et MySQL sont compatibles avec le projet.
- Les modules IA en Python doivent être lancés séparément si nécessaire.
