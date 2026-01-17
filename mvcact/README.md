# 🎉 ClickNGooo - Module Activité

✨ *Description* :  
Ce module fait partie de la plateforme **ClickNGooo**, qui permet aux utilisateurs de réserver des **activités de loisirs**, participer à des **événements**, acheter des **produits**, et organiser du **covoiturage**.  
Le module **Activité** se concentre sur la gestion et la réservation d'activités disponibles en Tunisie.

---

## ⚙️ Fonctionnalités

- 🔍 Recherche d'activités par catégorie (sport, culture, nature…)
- 📆 Réservation en ligne avec confirmation par email
- 📍 Affichage des activités selon localisation
- 💬 Ajout d'avis/commentaires sur les activités
- 🖼️ Affichage dynamique (toggle) des activités sous chaque catégorie
- 🔒 Accès admin pour gérer les activités (CRUD complet)
- 📱 Interface responsive

---

## 💻 Comment utiliser ?

1. Télécharger ou cloner le projet :
```bash
git clone https://github.com/ton-nom-utilisateur/clickngooo.git

```

# Cloudinary Uploader

Cette application PHP vous permet de télécharger facilement des images vers Cloudinary et de gérer votre galerie d'images cloud.

## Fonctionnalités

- **Téléchargement direct** : Uploadez des images individuelles directement depuis votre navigateur
- **Scanner de dossier** : Uploadez automatiquement toutes les images d'un dossier local
- **Galerie d'images** : Visualisez vos images téléchargées avec leurs URLs Cloudinary
- **Transformations d'images** : Testez différentes transformations d'images Cloudinary
- **Interface responsive** : Application utilisable sur desktop et mobile

## Prérequis

- PHP 7.4 ou supérieur
- Extension cURL pour PHP
- Un compte Cloudinary (gratuit pour commencer)

## Installation

1. Clonez ou téléchargez ce dépôt dans votre environnement web (XAMPP, WAMP, serveur en ligne, etc.)
2. Modifiez le fichier `cloudinary_config.php` avec vos identifiants Cloudinary :
   ```php
   return [
       'cloud_name' => 'VOTRE_CLOUD_NAME',
       'api_key' => 'VOTRE_API_KEY',
       'api_secret' => 'VOTRE_API_SECRET',
       'secure' => true
   ];
   ```
3. Assurez-vous que le dossier `images` existe à la racine du projet et possède les droits d'écriture
4. Accédez à l'application via votre navigateur : `http://localhost/clickngo/`

## Structure des fichiers

- `index.php` - Page d'accueil et tableau de bord
- `cloudinary_config.php` - Configuration de l'API Cloudinary
- `cloudinary_upload.php` - Script pour scanner et uploader des dossiers d'images
- `cloudinary_form.php` - Formulaire pour télécharger des images individuelles
- `cloudinary_gallery.php` - Galerie d'images téléchargées
- `cloudinary_images.json` - Stockage des métadonnées des images téléchargées
- `images/` - Dossier pour stocker les images avant téléchargement

## Utilisation

### Téléchargement d'une image individuelle

1. Accédez à la page "Télécharger une image"
2. Glissez-déposez une image ou cliquez sur "Parcourir"
3. Cliquez sur "Télécharger vers Cloudinary"
4. L'image sera téléchargée et vous obtiendrez son URL Cloudinary

### Scanner un dossier

1. Placez vos images dans le dossier `images/` à la racine du projet
2. Accédez à la page "Scanner un dossier"
3. Le script détectera automatiquement les nouvelles images et les téléchargera
4. Les URLs des images seront enregistrées dans le fichier JSON

### Visualiser la galerie

1. Accédez à la page "Galerie d'images"
2. Vous verrez toutes vos images téléchargées avec leurs URLs
3. Utilisez les boutons de transformation pour tester différents effets
4. Copiez les URLs pour les utiliser dans vos projets

## Comment ça marche

L'application utilise l'API REST de Cloudinary pour télécharger les images. Elle ne nécessite pas le SDK officiel, ce qui la rend légère et facile à déployer. Les informations sur les images téléchargées sont stockées localement dans un fichier JSON pour permettre le suivi et la gestion.

## Sécurité

- Les identifiants Cloudinary sont stockés dans un fichier PHP, ce qui évite leur exposition dans le code client
- Les types de fichiers sont vérifiés pour éviter le téléchargement de fichiers malveillants
- Les données sont validées avant d'être utilisées dans l'application

## Personnalisation

Vous pouvez facilement personnaliser cette application :

- Modifiez les styles CSS dans chaque fichier
- Ajoutez de nouvelles fonctionnalités comme la suppression d'images
- Intégrez l'application à une base de données plutôt qu'à un fichier JSON

## Licence

Ce projet est libre d'utilisation pour vos projets personnels ou commerciaux.