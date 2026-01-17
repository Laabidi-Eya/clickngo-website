<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Location</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;500&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(145deg, #ffeaf2, #d9e4ff);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .container {
      display: flex;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 30px;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
      max-width: 1100px;
      width: 90%;
      overflow: hidden;
      transform: translateY(0);
      animation: slideIn 0.8s ease-out;
    }

    @keyframes slideIn {
      from {
        transform: translateY(50px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .preview {
      width: 45%;
      background: linear-gradient(135deg, #fff0f5, #f0f7ff);
      color: #333;
      padding: 40px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .preview::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" opacity="0.1"><circle cx="10" cy="10" r="2" fill="%23ff8fa3"/><circle cx="90" cy="90" r="3" fill="%23c084fc"/><circle cx="50" cy="20" r="2" fill="%23ff8fa3"/><circle cx="80" cy="50" r="2" fill="%23c084fc"/></svg>') repeat;
      z-index: 0;
    }

    .preview::after {
      content: '✨';
      position: absolute;
      top: 20px;
      right: 20px;
      font-size: 24px;
      opacity: 0.5;
      z-index: 1;
    }

    .preview img {
      width: 220px;
      height: 220px;
      object-fit: cover;
      border-radius: 20px;
      margin-bottom: 25px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      z-index: 1;
      border: 3px solid #fff;
    }

    .preview img:hover {
      transform: scale(1.08) rotate(1deg);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
    }

    .preview h3 {
      font-family: 'Playfair Display', serif;
      font-size: 30px;
      margin: 10px 0;
      text-align: center;
      color: #2d2d2d;
      z-index: 1;
      position: relative;
    }

    .preview h3::after {
      content: '';
      display: block;
      width: 40px;
      height: 2px;
      background: #ff8fa3;
      margin: 8px auto;
      border-radius: 1px;
    }

    .preview .price {
      font-size: 22px;
      font-weight: 500;
      color: #fff;
      background: linear-gradient(45deg, #ff8fa3, #c084fc);
      padding: 8px 20px;
      border-radius: 20px;
      z-index: 1;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .preview .price:hover {
      transform: translateY(-2px);
    }

    .location-form {
      width: 55%;
      padding: 50px;
      background: #fff;
      transition: transform 0.3s ease;
    }

    .location-form:hover {
      transform: scale(1.02);
    }

    .location-form h2 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      color: #2d2d2d;
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .location-form h2::after {
      content: '';
      width: 60px;
      height: 3px;
      background: #ff8fa3;
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    .location-form label {
      display: block;
      margin: 20px 0 10px;
      font-weight: 500;
      color: #333;
      font-size: 16px;
    }

    .location-form input[type="text"],
    .location-form input[type="date"],
    .location-form input[type="time"],
    .location-form input[type="tel"] {
      width: 100%;
      padding: 14px 18px;
      border-radius: 12px;
      border: 1px solid #e0e0e0;
      font-size: 16px;
      background: #f9f9f9;
      transition: all 0.3s ease;
    }

    .location-form input:focus {
      border-color: #ff8fa3;
      box-shadow: 0 0 0 3px rgba(255, 143, 163, 0.2);
      outline: none;
      background: #fff;
    }

    .location-form .readonly {
      background: #f0f0f0;
      color: #666;
      cursor: not-allowed;
    }

    .location-form button {
      width: 100%;
      padding: 16px;
      margin-top: 20px;
      border: none;
      border-radius: 12px;
      font-size: 18px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .register-btn {
      background: linear-gradient(90deg, #ff8fa3, #c084fc);
      color: #fff;
      position: relative;
      overflow: hidden;
    }

    .register-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: 0.5s;
    }

    .register-btn:hover::before {
      left: 100%;
    }

    .register-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(255, 143, 163, 0.4);
    }

    .register-btn.cancel {
      background: #e0e0e0;
      color: #444;
    }

    .register-btn.cancel:hover {
      background: #d0d0d0;
      transform: translateY(-2px);
      box-shadow: none;
    }

    .error {
      color: #e63946;
      font-size: 13px;
      margin-top: 5px;
      display: none;
    }

    .invalid {
      border-color: #e63946;
      box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.2);
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        width: 95%;
      }

      .preview,
      .location-form {
        width: 100%;
      }

      .preview img {
        width: 180px;
        height: 180px;
      }
    }
  </style>
</head>

<body>

  <div class="container">
    <div class="preview">
      <img src="./images/logo.png" alt="Produit" id="productImage">
      <div class="price" id="previewPrix">-- TND/h</div>
    </div>

    <div class="location-form">
      <h2>Validation de location</h2>
      <form id="locationForm" action="valider_location.php" method="post" novalidate>
        <label>Nom du produit</label>
        <input type="text" name="produit" id="produit" class="readonly" readonly>

        <label>Nom</label>
        <input type="text" name="nom" id="nom" placeholder="Votre nom">
        <span class="error" id="nom_error">Le nom doit contenir uniquement des lettres (min 2 caractères).</span>
        <input type="hidden" name="produit_id" value="<?php echo $_GET['id']; ?>">
        <input type="hidden" name="produit" value="<?php echo $_GET['produit']; ?>">

        <label>Prénom</label>
        <input type="text" name="prenom" id="prenom" placeholder="Votre prénom">
        <span class="error" id="prenom_error">Le prénom doit contenir uniquement des lettres (min 2 caractères).</span>
        <label>Quantité</label>
        <input type="number" name="quantite" id="quantite" placeholder="Quantité à louer" min="1">
        <span class="error" id="quantite_error">Veuillez entrer une quantité valide (min 1).</span>

        <label>Date de location</label>
        <input type="date" name="date_location" id="date_location">
        <span class="error" id="date_error">La date doit être aujourd'hui ou ultérieure.</span>

        <label>Heure de début</label>
        <input type="time" name="heure_debut" id="heure_debut">
        <span class="error" id="heure_debut_error">Veuillez sélectionner une heure de début.</span>

        <label>Heure de fin</label>
        <input type="time" name="heure_fin" id="heure_fin">
        <span class="error" id="heure_fin_error">L'heure de fin doit être postérieure à l'heure de début.</span>

        <label>Numéro de téléphone</label>
        <input type="tel" name="telephone" id="telephone" placeholder="Votre numéro">
        <span class="error" id="telephone_error">Veuillez entrer un numéro de téléphone valide (ex. +21612345678).</span>

        <label>Numéro de carte d’identité</label>
        <input type="text" name="carte_identite" id="carte_identite" placeholder="Votre numéro de CI">
        <span class="error" id="carte_identite_error">Le numéro de CI doit contenir au moins 8 chiffres.</span>

        <button type="submit" class="register-btn">Valider la location</button>
        <button type="button" class="register-btn cancel" onclick="window.location.href='produit.php'">Annuler</button>
      </form>

      <script>
        document.getElementById('locationForm').addEventListener('submit', function(event) {
          let form = event.target;
          let errors = [];

          // Vérification du nom
          let nom = form['nom'].value.trim();
          if (nom.length < 2 || !/^[a-zA-Z]+$/.test(nom)) {
            errors.push("Le nom doit contenir uniquement des lettres (min 2 caractères).");
            document.getElementById('nom_error').style.display = 'inline';
          } else {
            document.getElementById('nom_error').style.display = 'none';
          }

          // Vérification du prénom
          let prenom = form['prenom'].value.trim();
          if (prenom.length < 2 || !/^[a-zA-Z]+$/.test(prenom)) {
            errors.push("Le prénom doit contenir uniquement des lettres (min 2 caractères).");
            document.getElementById('prenom_error').style.display = 'inline';
          } else {
            document.getElementById('prenom_error').style.display = 'none';
          }

          // Vérification de la date de location
          let date_location = new Date(form['date_location'].value);
          let today = new Date();
          today.setHours(0, 0, 0, 0); // Pour comparer sans tenir compte de l'heure
          if (date_location < today) {
            errors.push("La date doit être aujourd'hui ou ultérieure.");
            document.getElementById('date_error').style.display = 'inline';
          } else {
            document.getElementById('date_error').style.display = 'none';
          }

          // Vérification de l'heure de début
          let heure_debut = form['heure_debut'].value;
          if (!heure_debut) {
            errors.push("Veuillez sélectionner une heure de début.");
            document.getElementById('heure_debut_error').style.display = 'inline';
          } else {
            document.getElementById('heure_debut_error').style.display = 'none';
          }

          // Vérification de l'heure de fin
          let heure_fin = form['heure_fin'].value;
          if (heure_fin && heure_debut >= heure_fin) {
            errors.push("L'heure de fin doit être postérieure à l'heure de début.");
            document.getElementById('heure_fin_error').style.display = 'inline';
          } else {
            document.getElementById('heure_fin_error').style.display = 'none';
          }

          // Vérification du numéro de téléphone
          let telephone = form['telephone'].value.trim();
          if (!/^\+216\d{8}$/.test(telephone)) {
            errors.push("Veuillez entrer un numéro de téléphone valide (ex. +21612345678).");
            document.getElementById('telephone_error').style.display = 'inline';
          } else {
            document.getElementById('telephone_error').style.display = 'none';
          }

          // Vérification du numéro de carte d’identité
          let carte_identite = form['carte_identite'].value.trim();
          if (!/^\d{8}$/.test(carte_identite)) {
            errors.push("Le numéro de CI doit contenir au moins 8 chiffres.");
            document.getElementById('carte_identite_error').style.display = 'inline';
          } else {
            document.getElementById('carte_identite_error').style.display = 'none';
          }

          // Si des erreurs sont présentes, empêche l'envoi du formulaire et affiche les erreurs
          if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join("\n"));
          }
        });
      </script>


      <script>
        function getQueryParam(param) {
          const urlParams = new URLSearchParams(window.location.search);
          return urlParams.get(param);
        }

        // Récupérer les paramètres
        const produit = getQueryParam('produit') || 'Produit inconnu';
        const prixHoraire = parseFloat(getQueryParam('prix')) || 0;
        const imageUrl = getQueryParam('image') || './images/logo.png';

        // Sélectionner les éléments
        const nomProduitInput = document.getElementById('produit');
        const productImage = document.getElementById('productImage');
        const previewPrix = document.getElementById('previewPrix');

        // Mettre à jour les champs
        nomProduitInput.value = decodeURIComponent(produit);
        previewPrix.textContent = prixHoraire.toFixed(2) + ' TND/h';

        // Gérer l'image avec une vérification
        try {
          const decodedImageUrl = decodeURIComponent(imageUrl);
          productImage.src = decodedImageUrl;
          productImage.onerror = () => {
            console.error('Erreur de chargement de l\'image:', decodedImageUrl);
            productImage.src = './images/logo.png';
          };
        } catch (e) {
          console.error('Erreur de décodage de l\'URL de l\'image:', e);
          productImage.src = './images/logo.png';
        }

        const form = document.getElementById('locationForm');
        form.addEventListener('submit', function(event) {
          event.preventDefault();
          let isValid = true;

          const nom = document.getElementById('nom');
          const nomRegex = /^[A-Za-zÀ-ÿ\s-]{2,}$/;
          if (!nom.value.trim() || !nomRegex.test(nom.value.trim())) {
            nom.classList.add('invalid');
            document.getElementById('nom_error').style.display = 'block';
            isValid = false;
          } else {
            nom.classList.remove('invalid');
            document.getElementById('nom_error').style.display = 'none';
          }



          let quantite = form['quantite'].value.trim();
          if (!quantite || quantite < 1) {
            errors.push("Veuillez entrer une quantité valide (min 1).");
            document.getElementById('quantite_error').style.display = 'inline';
          } else {
            document.getElementById('quantite_error').style.display = 'none';
          }


          const prenom = document.getElementById('prenom');
          if (!prenom.value.trim() || !nomRegex.test(prenom.value.trim())) {
            prenom.classList.add('invalid');
            document.getElementById('prenom_error').style.display = 'block';
            isValid = false;
          } else {
            prenom.classList.remove('invalid');
            document.getElementById('prenom_error').style.display = 'none';
          }

          const dateLocation = document.getElementById('date_location');
          const today = new Date().toISOString().split('T')[0];
          if (!dateLocation.value || dateLocation.value < today) {
            dateLocation.classList.add('invalid');
            document.getElementById('date_error').style.display = 'block';
            isValid = false;
          } else {
            dateLocation.classList.remove('invalid');
            document.getElementById('date_error').style.display = 'none';
          }

          const heureDebut = document.getElementById('heure_debut');
          if (!heureDebut.value) {
            heureDebut.classList.add('invalid');
            document.getElementById('heure_debut_error').style.display = 'block';
            isValid = false;
          } else {
            heureDebut.classList.remove('invalid');
            document.getElementById('heure_debut_error').style.display = 'none';
          }

          const heureFin = document.getElementById('heure_fin');
          if (!heureFin.value || (heureDebut.value && heureFin.value <= heureDebut.value)) {
            heureFin.classList.add('invalid');
            document.getElementById('heure_fin_error').style.display = 'block';
            isValid = false;
          } else {
            heureFin.classList.remove('invalid');
            document.getElementById('heure_fin_error').style.display = 'none';
          }

          const telephone = document.getElementById('telephone');
          const telRegex = /^\+216\d{8}$/; // Format: +216 suivi de 8 chiffres

          if (!telephone.value.trim() || !telRegex.test(telephone.value.trim())) {
            telephone.classList.add('invalid');
            document.getElementById('telephone_error').style.display = 'block';
            isValid = false;
          } else {
            telephone.classList.remove('invalid');
            document.getElementById('telephone_error').style.display = 'none';
          }

          const carteIdentite = document.getElementById('carte_identite');
          const ciRegex = /^[0-9]{8,}$/;
          if (!carteIdentite.value.trim() || !ciRegex.test(carteIdentite.value.trim())) {
            carteIdentite.classList.add('invalid');
            document.getElementById('carte_identite_error').style.display = 'block';
            isValid = false;
          } else {
            carteIdentite.classList.remove('invalid');
            document.getElementById('carte_identite_error').style.display = 'none';
          }

          if (isValid) {
            form.submit();
          }
        });
      </script>

      <script>
  const sessionUser = <?php echo json_encode($_SESSION['user'] ?? []); ?>;

  document.addEventListener('DOMContentLoaded', function () {
  if (sessionUser && sessionUser.full_name) {
    const fullName = sessionUser.full_name.trim();

    if (fullName.includes(' ')) {
      const nameParts = fullName.split(/\s+/);
      document.getElementById('nom').value = nameParts[0];
      document.getElementById('prenom').value = nameParts.slice(1).join(' ');
    } else {
      document.getElementById('nom').value = fullName;
      document.getElementById('prenom').value = '';
    }
  }

  if (sessionUser.num_user) {
    document.getElementById('telephone').value = sessionUser.num_user;
    document.getElementById('telephone').readOnly = true;
    document.getElementById('telephone').classList.add('readonly');
  }
});

</script>

</body>

</html>