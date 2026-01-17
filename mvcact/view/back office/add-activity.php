<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter une Activité - ClickNGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="dash.css">
</head>
<body>
  <div class="sidebar">
    <div>
      <img src="logo.png" alt="Logo" class="logo">
      <h1>ClickNGo</h1>
      <div class="menu-item"><a href="dashboard.php?action=notifications">🔔 Notifications</a></div>
      <div class="menu-item"><a href="dashboard.php">📋 Contrôle de Données</a></div>
      <div class="menu-item"><a href="dashboard.php?action=calendar">📅 Calendrier</a></div>
      <div class="menu-item"><a href="dashboard.php?action=statistics">📊 Statistiques Générales</a></div>
      <div class="menu-item"><a href="dashboard.php?action=daily_activity">🌟 Activité du Jour</a></div>
      <div class="menu-item"><a href="dashboard.php?action=history">📜 Historique</a></div>
    </div>
    <div class="menu-item"><a href="dashboard.php?action=settings">⚙️ Paramètres</a></div>
    <div class="menu-item"><a href="dashboard.php?action=logout">🚪 Déconnexion</a></div>
  
</div>

  <div class="dashboard">
    <div class="header">
      <h2>Ajouter une Nouvelle Activité</h2>
      <div class="profile-container">
        <input class="search" type="text" placeholder="Rechercher une activite...">
        <div class="profile">
          <img src="laetitia.webp" alt="Profile Picture">
        </div>
      </div>
    </div>

    <div class="add-activity-form">
      <h3>📋 Formulaire d'Ajout d'Activité</h3>
      <?php
      session_start();
      if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
      }
      if (isset($_SESSION['success'])) {
        echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
      }
      // Fonction utilitaire pour l'affichage d'image (Cloudinary ou locale avec fallback)
      function getImagePath($image) {
          if (preg_match('/^https?:\/\//', $image)) {
              return $image; // URL Cloudinary
          } elseif (file_exists(__DIR__ . '/../front office/' . $image)) {
              return '../front office/' . $image; // Chemin local existant
          } else {
              return '../front office/images/default.jpg'; // Image par défaut si rien n'existe
          }
      }
      ?>
      <form method="POST" action="process_activity.php" enctype="multipart/form-data">
        <input type="hidden" name="operation" value="add">
        <div class="form-group">
          <label for="activityName">Nom de l'activité</label>
          <input type="text" id="activityName" name="name" placeholder="Ex: Yoga du matin" required>
        </div>

        <div class="form-group">
          <label for="activityDescription">Description</label>
          <textarea id="activityDescription" name="description" placeholder="Décrivez l'activité..." rows="5" required></textarea>
        </div>

        <div class="form-group">
          <label for="activityPrice">Prix (en TND)</label>
          <input type="number" id="activityPrice" name="price" placeholder="Ex: 20" step="0.01" min="0" required>
        </div>

        <div class="form-group">
          <label for="activityLocation">Lieu</label>
          <select id="activityLocation" name="location" required>
            <option value="" disabled selected>Choisir une région</option>
            <option value="Tunis">Tunis</option>
            <option value="Ariana">Ariana</option>
            <option value="Ben Arous">Ben Arous</option>
            <option value="Manouba">Manouba</option>
            <option value="Nabeul">Nabeul</option>
            <option value="Zaghouan">Zaghouan</option>
            <option value="Bizerte">Bizerte</option>
            <option value="Béja">Béja</option>
            <option value="Jendouba">Jendouba</option>
            <option value="Kef">Kef</option>
            <option value="Siliana">Siliana</option>
            <option value="Sousse">Sousse</option>
            <option value="Monastir">Monastir</option>
            <option value="Mahdia">Mahdia</option>
            <option value="Sfax">Sfax</option>
            <option value="Kairouan">Kairouan</option>
            <option value="Kasserine">Kasserine</option>
            <option value="Sidi Bouzid">Sidi Bouzid</option>
            <option value="Gabès">Gabès</option>
            <option value="Medenine">Medenine</option>
            <option value="Tataouine">Tataouine</option>
            <option value="Gafsa">Gafsa</option>
            <option value="Tozeur">Tozeur</option>
            <option value="Kebili">Kebili</option>
          </select>
        </div>

        <div class="form-group">
          <label for="activityDate">Date et Heure</label>
          <input type="datetime-local" id="activityDate" name="date" required>
        </div>

        <div class="form-group">
          <label for="activityCategory">Catégorie</label>
          <select id="activityCategory" name="category" required>
            <option value="" disabled>Choisir une catégorie</option>
            <option value="Ateliers">Ateliers</option>
            <option value="bien-etre">Bien-être</option>
            <option value="Aérien">Aérien</option>
            <option value="Aquatique">Aquatique</option>
            <option value="Terestre">Terrestre</option>
            <option value="Insolite">Insolite</option>
            <option value="culture">Culture</option>
            <option value="Détente">Détente</option>
            <option value="sport">Sport</option>
            <option value="nature">Nature</option>
            <option value="aventure">Aventure</option>
            <option value="Famille">Famille</option>
            <option value="Extreme">Extrême</option>
          </select>
        </div>

        <div class="form-group">
          <label for="activityCapacity">Capacité maximale</label>
          <input type="number" id="activityCapacity" name="capacity" placeholder="Ex: 50" min="1" required>
        </div>
        <!-- Champ pour uploader une image -->
        <div class="form-group">
          <label for="imageFile">Image de l'activité *</label>
          <div class="image-input-container" style="background:#f8f8ff;padding:10px;border-radius:8px;">
            <input type="file" id="imageFile" name="image" accept="image/*" required style="display:block;margin-bottom:8px;">
          </div>
          <div id="imagePreview" style="margin-top: 10px; display: none;">
            <img id="previewImg" src="" alt="Aperçu de l'image" style="max-width: 100%; max-height: 200px; border:1px solid #ccc;">
          </div>
        </div>
        <!-- JavaScript pour gérer l'aperçu de l'image -->
  <script>
    const imageFileInput = document.getElementById('imageFile');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    imageFileInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImg.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.style.display = 'none';
        previewImg.src = '';
      }
    });
  </script>

        <div class="form-buttons" style="display:flex;gap:16px;margin-top:24px;">
          <button type="submit" class="submit-button" style="background:#6941FF;color:#fff;padding:10px 24px;border-radius:8px;font-weight:600;">Ajouter l'activité</button>
          <a href="dashboard.php" class="cancel-button" style="background:#eee;color:#6941FF;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600;">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>