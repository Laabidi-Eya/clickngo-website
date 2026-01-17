<?php
session_start();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Achat</title>
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
      from { transform: translateY(50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
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

    .achat-form {
      width: 55%;
      padding: 50px;
      background: #fff;
      transition: transform 0.5s ease-in-out;
      position: relative;
      overflow: visible;
    }

    .achat-form h2 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      color: #2d2d2d;
      text-align: center;
      margin-bottom: 30px;
      position: relative;
    }

    .achat-form h2::after {
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

    .achat-form label {
      display: block;
      margin: 20px 0 10px;
      font-weight: 500;
      color: #333;
      font-size: 16px;
    }

    .achat-form input[type="text"],
    .achat-form input[type="email"],
    .achat-form input[type="number"] {
      width: 100%;
      padding: 14px 18px;
      border-radius: 12px;
      border: 1px solid #e0e0e0;
      font-size: 16px;
      background: #f9f9f9;
      transition: all 0.3s ease;
    }

    .achat-form input:focus {
      border-color: #ff8fa3;
      box-shadow: 0 0 0 3px rgba(255, 143, 163, 0.2);
      outline: none;
      background: #fff;
    }

    .achat-form .readonly {
      background: #f0f0f0;
      color: #666;
      cursor: not-allowed;
    }

    .radio-group {
      margin: 20px 0;
      display: flex;
      gap: 25px;
      flex-wrap: wrap;
    }

    .radio-group label {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 15px;
      color: #444;
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .radio-group input[type="radio"] {
      accent-color: #ff8fa3;
    }

    .radio-group label:hover {
      color: #ff8fa3;
    }

    .achat-form button {
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

    #stripe-section {
      margin-top: 20px;
      padding: 20px;
      background: #f9f9f9;
      border-radius: 12px;
      display: none;
    }

    #stripe-section.active {
      display: block;
    }

    #card-errors {
      color: #e63946;
      font-size: 13px;
      display: none;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        width: 95%;
      }

      .preview,
      .achat-form {
        width: 100%;
      }

      .preview img {
        width: 180px;
        height: 180px;
      }
    }

    .result-section {
      display: none;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #fff;
      padding: 40px;
      transform: translateX(100%);
      transition: transform 0.5s ease-in-out;
      z-index: 2;
    }

    .result-section.active {
      transform: translateX(0);
    }

    .result-section h3 {
      font-size: 28px;
      color: #333;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 2px solid #ff8fa3;
      margin-left: 20px;
    }

    .result-info {
      display: grid;
      grid-template-columns: 200px 1fr;
      gap: 15px;
      margin: 20px;
      font-size: 18px;
    }

    .result-label {
      color: #ff8fa3;
      font-weight: 500;
      padding: 10px;
      background: #fff5f7;
      border-radius: 8px;
      text-align: right;
      margin-right: 10px;
    }

    .result-value {
      color: #333;
      padding: 10px;
      background: #f8f9fa;
      border-radius: 8px;
      font-weight: 400;
    }

    .back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      background: #ff8fa3;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .back-button:hover {
      background: #ff7b93;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .result-section {
        padding: 20px;
      }

      .result-info {
        grid-template-columns: 1fr;
        gap: 10px;
        margin: 15px;
      }

      .result-label {
        text-align: left;
        margin-right: 0;
        background: none;
        padding: 5px 0;
        color: #666;
      }

      .result-value {
        padding: 8px;
        font-size: 16px;
      }

      .back-button {
        padding: 8px 15px;
        font-size: 14px;
      }
    }

    input.readonly {
  background-color: #f0f0f0;
  cursor: not-allowed;
}

  </style>
</head>
<body>
  <div class="container">
    <div class="preview">
      <img src="images/panier.jpg" alt="Panier" id="productImage">
      <h3 id="previewNom">Votre Panier</h3>
      <div class="price" id="previewPrix">-- TND</div>
    </div>

    <div class="achat-form">
      <h2>Validation d'achat</h2>
      <form id="achatForm" method="post" novalidate>
        <input type="hidden" name="action" value="ajouter_commande">
        <input type="hidden" id="panier_data" name="panier_data" value="">
        <div class="form-group">
          <label>Nom</label>
          <input type="text" name="nom" id="nom" placeholder="Votre nom">
          <span class="error" id="nom_error">Le nom doit contenir uniquement des lettres (min 2 caractères).</span>
        </div>

        <div class="form-group">
          <label>Prénom</label>
          <input type="text" name="prenom" id="prenom" placeholder="Votre prénom">
          <span class="error" id="prenom_error">Le prénom doit contenir uniquement des lettres (min 2 caractères).</span>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" id="email" placeholder="Votre email">
          <span class="error" id="email_error">Veuillez entrer un email valide.</span>
        </div>

        <div class="form-group">
          <label>Numéro de téléphone</label>
          <input type="text" name="telephone" id="telephone" placeholder="Votre numéro de téléphone">
          <span class="error" id="telephone_error">Veuillez entrer un numéro de téléphone valide.</span>
        </div>

        <div class="form-group">
          <label>Quantité</label>
          <input type="number" name="quantite" id="quantite" min="1" value="1" class="readonly" readonly>
          <span class="error" id="quantite_error">La quantité doit être un nombre positif (minimum 1).</span>
        </div>

        <div class="form-group">
          <label>Prix total (TND)</label>
          <input type="text" name="prix_total" id="prix_total" class="readonly" readonly>
          <input type="hidden" name="prix_total_value" id="prix_total_value">
        </div>

        <div class="form-group">
          <label>Mode de paiement</label>
          <div class="radio-group">
            <label><input type="radio" name="paiement" value="Carte"> Carte</label>
            <label><input type="radio" name="paiement" value="Especes"> Espèces</label>
            <label><input type="radio" name="paiement" value="PayPal"> PayPal</label>
          </div>
          <span class="error" id="paiement_error">Veuillez sélectionner un mode de paiement.</span>
        </div>

        <div id="stripe-section">
          <span class="error" id="card-errors"></span>
        </div>

        <div class="form-buttons">
          <button type="submit" class="register-btn">Valider l'achat</button>
          <button type="button" class="register-btn cancel" onclick="window.location.href='produit.php'">Annuler</button>
        </div>
      </form>

      <div class="result-section" id="resultSection">
        <button class="back-button" onclick="goBack()">← Retour</button>
        <h3>Récapitulatif de votre commande</h3>
        <div class="result-info">
          <div class="result-label">Nom :</div>
          <div class="result-value" id="resultNom"></div>
          <div class="result-label">Prénom :</div>
          <div class="result-value" id="resultPrenom"></div>
          <div class="result-label">Email :</div>
          <div class="result-value" id="resultEmail"></div>
          <div class="result-label">Téléphone :</div>
          <div class="result-value" id="resultTelephone"></div>
          <div class="result-label">Quantité :</div>
          <div class="result-value" id="resultQuantite"></div>
          <div class="result-label">Prix total :</div>
          <div class="result-value" id="resultPrixTotal"></div>
          <div class="result-label">Mode de paiement :</div>
          <div class="result-value" id="resultPaiement"></div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://js.stripe.com/v3/"></script>
  <script>
    const stripe = Stripe('pk_test_51RJL0sQabomDz0Baxnej9s98KKxxRyqRzsJUCLkHaveIB3FGVPF2rhdi8jLPCydMO4lF95QJotwMvL0QjOXjAud200GjafxVKG');

    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('achatForm');
      const nomInput = document.getElementById('nom');
      const prenomInput = document.getElementById('prenom');
      const emailInput = document.getElementById('email');
      const telephoneInput = document.getElementById('telephone');
      const quantiteInput = document.getElementById('quantite');
      const paiementRadios = document.querySelectorAll('input[name="paiement"]');
      const panierDataInput = document.getElementById('panier_data');
      const cardErrors = document.getElementById('card-errors');

      paiementRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          cardErrors.style.display = 'none';
          validatePaiement();
        });
      });

      nomInput.addEventListener('input', validateNom);
      prenomInput.addEventListener('input', validatePrenom);
      emailInput.addEventListener('input', validateEmail);
      telephoneInput.addEventListener('input', validateTelephone);

      nomInput.addEventListener('blur', validateNom);
      prenomInput.addEventListener('blur', validatePrenom);
      emailInput.addEventListener('blur', validateEmail);
      telephoneInput.addEventListener('blur', validateTelephone);

      function validateNom() {
        const value = nomInput.value.trim();
        const errorElement = document.getElementById('nom_error');
        if (value.length < 2 || !/^[a-zA-ZÀ-ÖØ-öø-ÿ\s'-]+$/.test(value)) {
          errorElement.style.display = 'inline';
          nomInput.classList.add('invalid');
          return false;
        } else {
          errorElement.style.display = 'none';
          nomInput.classList.remove('invalid');
          return true;
        }
      }

      function validatePrenom() {
        const value = prenomInput.value.trim();
        const errorElement = document.getElementById('prenom_error');
        if (value.length < 2 || !/^[a-zA-ZÀ-ÖØ-öø-ÿ\s'-]+$/.test(value)) {
          errorElement.style.display = 'inline';
          prenomInput.classList.add('invalid');
          return false;
        } else {
          errorElement.style.display = 'none';
          prenomInput.classList.remove('invalid');
          return true;
        }
      }

      function validateEmail() {
        const value = emailInput.value.trim();
        const errorElement = document.getElementById('email_error');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
          errorElement.style.display = 'inline';
          emailInput.classList.add('invalid');
          return false;
        } else {
          errorElement.style.display = 'none';
          emailInput.classList.remove('invalid');
          return true;
        }
      }

      function validateTelephone() {
        const value = telephoneInput.value.trim();
        const errorElement = document.getElementById('telephone_error');
        if (!/^\d{8,15}$/.test(value)) {
          errorElement.style.display = 'inline';
          telephoneInput.classList.add('invalid');
          return false;
        } else {
          errorElement.style.display = 'none';
          telephoneInput.classList.remove('invalid');
          return true;
        }
      }

      function validatePaiement() {
        const checked = document.querySelector('input[name="paiement"]:checked');
        const errorElement = document.getElementById('paiement_error');
        if (!checked) {
          errorElement.style.display = 'inline';
          return false;
        } else {
          errorElement.style.display = 'none';
          return true;
        }
      }

      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const isNomValid = validateNom();
        const isPrenomValid = validatePrenom();
        const isEmailValid = validateEmail();
        const isTelephoneValid = validateTelephone();
        const isPaiementValid = validatePaiement();

        if (!isNomValid || !isPrenomValid || !isEmailValid || !isTelephoneValid || !isPaiementValid) {
          if (!isNomValid) nomInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          else if (!isPrenomValid) prenomInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          else if (!isEmailValid) emailInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          else if (!isTelephoneValid) telephoneInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          else if (!isPaiementValid) paiementRadios[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }

        const paiement = document.querySelector('input[name="paiement"]:checked').value;

        // Store order details for receipt (for all payment methods)
        const panier = JSON.parse(panierDataInput.value || '[]');
        const orderDetails = {
          nom: nomInput.value.trim(),
          prenom: prenomInput.value.trim(),
          email: emailInput.value.trim(),
          telephone: telephoneInput.value.trim(),
          quantite: parseInt(quantiteInput.value),
          prix_total: parseFloat(document.getElementById('prix_total_value').value),
          panier: panier,
          paiement: paiement
        };

        // Send order details to store in session
        try {
          await fetch('../../Controller/store_order_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderDetails),
          });
        } catch (err) {
          console.error('Error storing order details:', err);
        }

        if (paiement === 'Carte') {
          const submitBtn = form.querySelector('.register-btn');
          submitBtn.disabled = true;
          submitBtn.textContent = 'Processing...';

          // Send panier_data and prix_total to Stripe
          const formData = new FormData();
          formData.append('panier_data', panierDataInput.value);
          formData.append('prix_total', document.getElementById('prix_total_value').value);

          try {
            const response = await fetch('../../Controller/create-checkout-session.php', {
              method: 'POST',
              body: formData,
            });

            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();

            if (result.error) {
              cardErrors.textContent = result.error;
              cardErrors.style.display = 'block';
              submitBtn.disabled = false;
              submitBtn.textContent = 'Valider l\'achat';
              return;
            }

            console.log('Redirecting to Checkout with session ID:', result.id);

            const { error } = await stripe.redirectToCheckout({ sessionId: result.id });
            if (error) {
              console.error('Stripe redirect error:', error);
              cardErrors.textContent = error.message || 'Failed to redirect to payment page.';
              cardErrors.style.display = 'block';
              submitBtn.disabled = false;
              submitBtn.textContent = 'Valider l\'achat';
            }
          } catch (err) {
            console.error('Fetch error:', err);
            cardErrors.textContent = 'Network error: Unable to connect to payment provider.';
            cardErrors.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Valider l\'achat';
          }
        } else {
          // Submit form to commandeController.php for non-card payments
          form.action = '../../Controller/commandeController.php';
          form.method = 'POST';
          form.submit();
        }
      });

      function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
      }

      const quantite = parseInt(getQueryParam('quantite')) || 1;
      const prixTotal = parseFloat(getQueryParam('prix_total')) || 0;

      quantiteInput.value = quantite;
      const prixTotalInput = document.getElementById('prix_total');
      const prix_total_value = document.getElementById('prix_total_value');
      if (prixTotalInput && prix_total_value) {
        prixTotalInput.value = prixTotal.toFixed(2) + ' TND';
        prix_total_value.value = prixTotal.toFixed(2);
      }

      const previewPrix = document.getElementById('previewPrix');
      if (previewPrix) {
        previewPrix.textContent = prixTotal.toFixed(2) + ' TND';
      }

      let panier = JSON.parse(localStorage.getItem('panier')) || [];
      panierDataInput.value = JSON.stringify(panier);

      if (getQueryParam('error')) {
        cardErrors.textContent = 'Le paiement a échoué. Veuillez réessayer.';
        cardErrors.style.display = 'block';
      }
    });

    function goBack() {
      const resultSection = document.getElementById('resultSection');
      resultSection.classList.remove('active');
      setTimeout(() => {
        resultSection.style.display = 'none';
      }, 500);
    }
  </script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const sessionUser = <?php echo json_encode($_SESSION['user'] ?? []); ?>;

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

    if (sessionUser.email) {
      const emailInput = document.getElementById('email');
      emailInput.value = sessionUser.email;
      emailInput.readOnly = true;
      emailInput.classList.add('readonly');
    }

    if (sessionUser.num_user) {
      const phoneInput = document.getElementById('telephone');
      phoneInput.value = sessionUser.num_user;
      phoneInput.readOnly = true;
      phoneInput.classList.add('readonly');
    }
  });
</script>
 
</body>
</html>