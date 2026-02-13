<?php
session_start();
require_once(__DIR__ . "/../../controller/controller.php");

// Vérification immédiate de la connexion
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['id_user']);
if (!$isLoggedIn) {
    header("Location: /mvcUtilisateur/View/BackOffice/login/login.php");
    exit();
}

// Charger les données seulement si l'utilisateur est connecté
$controller = new sponsorController();
$propositions = $controller->listSponser(); // Will only return sponsors for the logged-in user
?>
<?php
if (isset($_SESSION['sponsor_error'])) {
    echo '<div style="color: #e74c3c; background: #ffe6e6; padding: 10px; margin: 10px; border-radius: 5px;">' . htmlspecialchars($_SESSION['sponsor_error']) . '</div>';
    unset($_SESSION['sponsor_error']);
}
if (isset($_SESSION['sponsor_success'])) {
    echo '<div class="success-notification">';
    echo '<i class="fas fa-check-circle"></i>';
    echo '<span class="message">' . htmlspecialchars($_SESSION['sponsor_success']) . '</span>';
    echo '<button class="view-btn">Voir</button>';
    echo '</div>';
    echo '<script>
        setTimeout(function() {
            document.querySelector(".success-notification").style.display = "none";
        }, 5000);
        
        document.querySelector(".success-notification .view-btn").addEventListener("click", function() {
            document.getElementById("btnShowTracking").click();
            document.querySelector(".success-notification").style.display = "none";
        });
    </script>';
    unset($_SESSION['sponsor_success']);
}


// Fonction pour générer une couleur basée sur le nom de l'utilisateur
function stringToColor($str)
{
    // Liste de couleurs inspirées du thème Funbooker (rose, violet, orange, etc.)
    $Colors = [
        '#FF6B6B', // Rose vif
        '#FF8E53', // Orange clair
        '#6B5B95', // Violet moyen
        '#88B04B', // Vert doux
        '#F7CAC9', // Rose pâle
        '#92A8D1', // Bleu pastel
        '#955251', // Rouge bordeaux
        '#B565A7', // Violet rose
        '#DD4124', // Rouge-orange vif
        '#D65076', // Rose foncé
    ];

    // Générer un index déterministe basé sur la chaîne
    $hash = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $hash = ord($str[$i]) + (($hash << 5) - $hash);
    }

    // Sélectionner une couleur du tableau
    $index = abs($hash) % count($Colors);
    return $Colors[$index];
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <link rel="stylesheet" href="st.css">
    <style>
        /* Modal styles */
        .event-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .event-modal.show {
            display: block;
        }

        .event-modal-content {
            background: white;
            margin: 2% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            background: none;
            border: none;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.8em;
            display: none;
        }

        input:invalid,
        textarea:invalid {
            border-color: #e74c3c;
        }

        input:valid,
        textarea:valid {
            border-color: #2ecc71;
        }

        /* Added styles for payment form to match mvcProduit style */
        #paymentForm {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-family: 'Poppins', sans-serif;
        }

        #paymentForm .form-group {
            margin-bottom: 15px;
        }

        #paymentForm label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        #paymentForm input[type="text"],
        #paymentForm input[type="email"],
        #paymentForm input[type="number"],
        #paymentForm input[type="tel"],
        #paymentForm textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        #paymentForm input[type="text"]:focus,
        #paymentForm input[type="email"]:focus,
        #paymentForm input[type="number"]:focus,
        #paymentForm input[type="tel"]:focus,
        #paymentForm textarea:focus {
            border-color: #be3cf0;
            outline: none;
            box-shadow: 0 0 5px rgba(190, 60, 240, 0.3);
        }

        #paymentForm button#submitButton {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #b72de3, #ff4fc9, #ff708d);
            background-size: 200% 200%;
            color: #fff;
            border: none;
            border-radius: 30px;
            font-size: 17px;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 10px rgba(255, 105, 180, 0.4);
        }

        #paymentForm button#submitButton:hover {
            background-position: right center;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 14px rgba(255, 105, 180, 0.6);
        }
    </style>
</head>

<body>










     <script>


  function hideMessage() {
    const botMessage = document.getElementById("bot-message");
    botMessage.style.display = "none";
  }

  const navMenuLinks = document.querySelectorAll(".nav-links a");

  navMenuLinks.forEach(link => {
    link.addEventListener("mouseenter", () => {
      const section = link.textContent.trim().toLowerCase();
      let message = "";

      switch (section) {
        case "accueil":
          message = "Bienvenue chez ClickNgo 🏠";
          break;
        case "activités":
          message = "Prêt pour l’aventure ? Découvre nos activités ! 🧗";
          break;
        case "événements":
          message = "Ne rate pas nos événements exclusifs 🎉";
          break;
        case "produits":
          message = "Regarde ce qu'on a en boutique ! 🛍️";
          break;
        case "transports":
          message = "Besoin d’un covoiturage ? On a ce qu’il faut 🚗";
          break;
        case "sponsors":
          message = "Un grand merci à nos sponsors 🤝";
          break;
        default:
          message = `Tu veux explorer "${section}" ? 🤖`;
      }

      showMessage(message);
    });

    link.addEventListener("mouseleave", hideMessage);

    link.addEventListener("click", (e) => {
      e.preventDefault();
      const url = link.getAttribute("href");
      showMessage("Super choix ! On y va... 🚀");

      setTimeout(() => {
        window.location.href = url;
      }, 1000);
    });
  });
</script>

  
  <!-- Face-api.js pour la détection des émotions -->
  <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

  <!-- Spline pour le robot 3D -->
  <script type="module" src="https://unpkg.com/@splinetool/viewer@1.10.2/build/spline-viewer.js"></script>

  <!-- STYLES -->
  <style>

    spline-viewer {
      position: fixed;
      bottom: 20px;
      left: 20px;
      width: 300px;
      height: 300px;
      pointer-events: none;
      z-index: 1000;
      background: transparent !important;
    }

    #search1, #exploreBtn {
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      font-size: 16px;
      z-index: 2000;
      border-radius: 25px;
    }

    #search1 {
      top: 40px;
      padding: 12px 20px;
      border: 1px solid #ccc;
      width: 300px;
    }

    #exploreBtn {
      top: 100px;
      padding: 12px 24px;
      background: #ff66cc;
      color: white;
      border: none;
      cursor: pointer;
    }

    .bubble {
      position: fixed;
      bottom: 160px;
      left: 150px;
      background: #fff;
      padding: 14px 20px;
      border-radius: 22px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 16px;
      max-width: 260px;
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 1100;
      color: #333;
      font-weight: 500;
    }

    .bubble.show {
      opacity: 1;
      animation: bubblePop 0.3s ease;
    }

    @keyframes bubblePop {
      0% { transform: scale(0.8); opacity: 0; }
      60% { transform: scale(1.05); opacity: 1; }
      100% { transform: scale(1); }
    }

    .bubble::after {
      content: '';
      position: absolute;
      left: 30px;
      bottom: -5px;
      border: 15px solid transparent;
      border-top-color: #fff;
      border-bottom: 0;
      rotate: 160deg;
    }

    #webcam {
      position: fixed;
      top: 20px;
      right: 20px;
      width: 160px;
      height: 120px;
      border: 2px solid #ccc;
      border-radius: 8px;
      z-index: 1500;
      display: none;
    }

    #scanMoodBtn, #toggleVoice {
      position: fixed;
      z-index: 1500;
      font-size: 14px;
      font-weight: bold;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    #scanMoodBtn {
      bottom: 40px;
      left: 160px;
      padding: 10px 18px;
      background: linear-gradient(135deg, #d36fff, #ff66cc);
      color: white;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    #scanMoodBtn:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0,0,0,0.3);
    }

    #toggleVoice {
      bottom: 40px;
      left: 30px;
      background: white;
      border: 2px solid #ff66cc;
      color: #ff66cc;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    #toggleVoice.active {
      background: #ff66cc;
      color: white;
    }

    #toggleVoice:hover {
      transform: scale(1.1);
    }
  </style>

  <!-- Interface utilisateur -->
<input type="text" id="search1" placeholder="Recherche..." style="display: none;">
<button id="exploreBtn" style="display: none;">Voir les activités</button>

<!-- Interface utilisateur -->
<div style="text-align: center;">
  <button id="scanMoodBtn">Scanner mon humeur</button>

</div>

<script>
  let hasShownScrollMessage = false;

  window.addEventListener("scroll", () => {
    if (!hasShownScrollMessage && window.scrollY > 100) {
      showMessage("Tu explores notre monde ? Fais-moi signe si tu veux de l’aide 🤖📜");
      hasShownScrollMessage = true;

      setTimeout(hideMessage, 6000); // Masquer après 6 secondes
    }
  });
</script>


<script>




  const moodBtn = document.getElementById('scanMoodBtn');

  moodBtn.addEventListener('mouseenter', () => {
    showMessage("Envie de connaître ton humeur ? Clique ici ! 😊");
  });

  moodBtn.addEventListener('mouseleave', hideMessage);

  moodBtn.addEventListener('click', () => {
    showMessage("Analyse de ton humeur en cours... 🤖");

    // Affiche la vidéo si ce n'est pas déjà visible
    const video = document.getElementById('videoInput');
    video.style.display = 'block';

    // Lance la détection
    startCameraAndDetect(); // Cette fonction doit être définie ailleurs
  });
</script>




  <video id="webcam" autoplay muted></video>
<script>
window.addEventListener("DOMContentLoaded", () => {
  // Vérifie si le message de bienvenue a déjà été montré
  if (!sessionStorage.getItem("welcomeShown")) {
    const welcomeMessage = "👋 Bienvenue chez Click’n’Go ! Je suis ClickBot, ton assistant virtuel 🤖";
    showMessage(welcomeMessage);

    // Cache le message après 5 secondes
    setTimeout(hideMessage, 5000);

    // Marque comme déjà affiché
    sessionStorage.setItem("welcomeShown", "true");
  }
});
</script>


  <!-- Robot 3D -->
  <spline-viewer id="robot" url="https://prod.spline.design/IWzKfSYNM8KCQbZm/scene.splinecode" alpha="true"></spline-viewer>

  <!-- Message bulle -->
  <div class="bubble" id="robotMessage">Tu cherches quoi ? 😊</div>

  <!-- Bouton voix -->
  <button id="toggleVoice" title="Activer/désactiver la voix">🔊</button>

  <script>
  const voiceBtn = document.getElementById('toggleVoice');

  voiceBtn.addEventListener('mouseenter', () => {
    showMessage("Clique pour activer ou désactiver la voix 🔈🗣️");
  });

  voiceBtn.addEventListener('mouseleave', hideMessage);

  voiceBtn.addEventListener('click', () => {
    showMessage("Changement de l'état de la voix... 🎧");
    toggleVoice(); // Appelle ta fonction pour activer/désactiver la voix
  });
</script>


  <!-- SCRIPT JS -->
  <script>
    let voiceEnabled = false;
    const toggleVoiceBtn = document.getElementById('toggleVoice');

    toggleVoiceBtn.addEventListener('click', () => {
      voiceEnabled = !voiceEnabled;
      toggleVoiceBtn.classList.toggle('active');
    });

    function speak(text) {
      if (!voiceEnabled || !window.speechSynthesis) return;
      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'fr-FR';
      utterance.rate = 1;
      utterance.pitch = 1;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(utterance);
    }

    const viewer = document.getElementById('robot');
    const message = document.getElementById('robotMessage');
    const searchInput = document.getElementById('search1');
    const exploreBtn = document.getElementById('exploreBtn');
    const webcam = document.getElementById('webcam');
    const scanMoodBtn = document.getElementById('scanMoodBtn');

    let currentMood = null;
    let detectionInterval = null;
    let mediaStream = null;

    // Bouton arrêt du scan
    const stopBtn = document.createElement('button');
    stopBtn.textContent = "Arrêter le scan";
    Object.assign(stopBtn.style, {
      position: "fixed",
      bottom: "40px",
      left: "160px",
      padding: "10px 18px",
      background: "linear-gradient(135deg, #ff4d6d, #ff66cc)",
      color: "white",
      fontWeight: "bold",
      border: "none",
      borderRadius: "20px",
      boxShadow: "0 4px 12px rgba(0,0,0,0.2)",
      cursor: "pointer",
      zIndex: "1500",
      fontSize: "14px",
      transition: "transform 0.2s, box-shadow 0.2s",
      display: "none"
    });

    stopBtn.addEventListener("mouseenter", () => {
      stopBtn.style.transform = "scale(1.05)";
      stopBtn.style.boxShadow = "0 6px 16px rgba(0,0,0,0.3)";
    });
    stopBtn.addEventListener("mouseleave", () => {
      stopBtn.style.transform = "scale(1)";
      stopBtn.style.boxShadow = "0 4px 12px rgba(0,0,0,0.2)";
    });

    document.body.appendChild(stopBtn);

    viewer.addEventListener('load', () => {
      let head = null;
      try { head = viewer.scene.getObjectByName('Head'); } catch {}
      window.addEventListener('mousemove', (e) => {
        if (!head) return;
        const x = (e.clientX / window.innerWidth - 0.5) * 2;
        const y = (e.clientY / window.innerHeight - 0.5) * -2;
        head.rotation.y = x * 0.5;
        head.rotation.x = y * 0.5;
      });
    });

function showMessage(txt) {
  message.textContent = txt;
  message.classList.add("show");
  speak(txt);

  // Supprimer le message après 3 secondes (3000 millisecondes)
  setTimeout(() => {
    message.classList.remove("show");
  }, 4000);
}


    function hideMessage() {
      message.classList.remove("show");
    }

    searchInput.addEventListener('focus', () => showMessage("Dis-moi ce que tu veux trouver 😉"));
    searchInput.addEventListener('input', () => {
      const val = searchInput.value.trim();
      showMessage(val ? `Hmm… "${val}" ? 🔍` : "Tape quelque chose 👀");
    });
    searchInput.addEventListener('blur', hideMessage);

    exploreBtn.addEventListener('mouseenter', () => showMessage(`Tu veux "${exploreBtn.textContent}" ? 😎`));
    exploreBtn.addEventListener('mouseleave', hideMessage);
    exploreBtn.addEventListener('click', () => {
      showMessage("D'accord, je t'y emmène ! 🛸");
    });

    scanMoodBtn.addEventListener('click', async () => {
      try {
        scanMoodBtn.style.display = 'none';
        stopBtn.style.display = 'block';
        await initFaceDetection();
      } catch {
        showMessage("Je ne peux pas accéder à ta caméra 😢");
        scanMoodBtn.style.display = 'block';
        stopBtn.style.display = 'none';
      }
    });

    stopBtn.addEventListener('click', () => {
      if (mediaStream) mediaStream.getTracks().forEach(track => track.stop());
      clearInterval(detectionInterval);
      webcam.style.display = 'none';
      scanMoodBtn.style.display = 'block';
      stopBtn.style.display = 'none';
      currentMood = null;
      showMessage("Scan arrêté 👋");
      setTimeout(hideMessage, 3000);
    });

    async function initFaceDetection() {
      try {
        mediaStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        webcam.srcObject = mediaStream;
        webcam.style.display = 'block';

        await faceapi.nets.tinyFaceDetector.loadFromUri('https://justadudewhohacks.github.io/face-api.js/models');
        await faceapi.nets.faceExpressionNet.loadFromUri('https://justadudewhohacks.github.io/face-api.js/models');

        detectExpression();
      } catch (error) {
        showMessage("Permission refusée 😢 Active ta caméra !");
        throw error;
      }
    }

    function detectExpression() {
      detectionInterval = setInterval(async () => {
        if (!webcam.srcObject) return;
        const detection = await faceapi.detectSingleFace(webcam, new faceapi.TinyFaceDetectorOptions()).withFaceExpressions();
        if (!detection) {
          showMessage("On dirait que tu es metghachech 😶 Retire le cache pour que je t'aide !");
          return;
        }

        const sorted = Object.entries(detection.expressions).sort((a, b) => b[1] - a[1]);
        const expr = sorted[0][0];
        if (expr !== currentMood) {
          currentMood = expr;
          reactToEmotion(expr);
        }
      }, 3500);
    }

    function reactToEmotion(expr) {
      const activities = {
        happy: "🎉 Viens essayer notre atelier de danse ou un concert live !",
        sad: "🧘‍♂️ Essaie une session de méditation ou une balade en forêt",
        angry: "🥊 Viens faire un peu de boxe ou du sport pour te défouler",
        fearful: "🛶 Et si on faisait un tour en kayak paisible ?",
        surprised: "🎭 Tu vas adorer notre pièce de théâtre comique !",
        disgusted: "🍫 Viens goûter nos desserts maison pour te changer les idées 😋",
        neutral: "🎨 Atelier créatif ou expo photo ? Tu choisis !"
      };

      const reaction = {
        happy: "Tu souris ! On va faire la fête ? 🎉",
        sad: "Hmm... tu as l'air triste. Je te fais un câlin virtuel 🤗",
        neutral: "Tu as l'air concentré. Besoin d'aide ? 🤔",
        surprised: "Oh, surpris ? Viens découvrir une surprise ! 🎁",
        angry: "Prends une grande respiration... tout va bien se passer 🌿",
        disgusted: "Quelque chose te dégoûte ? Changeons de sujet ! 🌈",
        fearful: "N'aie pas peur, je suis là pour toi 🛡️"
      };

      showMessage(reaction[expr] + "\n\n" + activities[expr]);
      setTimeout(hideMessage, 7000);
    }
  </script>
















    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Réinitialisation des styles */
        * {
            margin: 0;
            padding: 0 !important;
            box-sizing: border-box;
        }

        /* Navbar fixe avec z-index élevé */
        .navbar {
            position: fixed !important;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: transparent;
            padding: 1rem 2rem;
            display: flex;
            justify-content: center; /* nhbha fil wosst */
            align-items: center;
            transition: opacity 0.3s ease;
            opacity: 1;
        }

        .navbar.hidden {
            opacity: 0;
            pointer-events: none;
        }


        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: black;
            padding-left: 110px !important;
        }

        .navbar .logo span {
            color: #7A2EE5;
            /* Couleur violette du N' */
        }

        .nav-center {
            display: flex;
            justify-content: center;
            flex-grow: 1;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
        }













        .nav-link.active {
            color: rgb(243, 47, 164);
        }






















        .nav-user {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .auth-section a {
            color: white;
            background-color: #4CAF50;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
        }

        /* Vidéo en plein écran derrière la navbar */
        .video-container {
            position: relative;
            width: 100%;
            height: 100vh;
            /* Plein écran */
            overflow: hidden;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            /* Derrière la navbar */
        }

        /* Contenu principal (défile sous la vidéo) */
        .container {
            margin-top: 100px;
            /* Commence après la vidéo */
            background: white;
            /* Fond pour le contenu */
            position: relative;
            z-index: 1;
        }
    </style>
    </head>

    <body>
        <!-- Barre de navigation fixe -->
        <nav class="navbar">
            <div class="logo-container">
                <img src="images/logo.png" alt="Logo Click'N'Go" class="logo">
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="/mvcUtilisateur/View/FrontOffice/index.php" class="nav-link">Accueil</a></li>
                <li class="nav-item"><a href="/mvcact/view/front office/activite.php" class="nav-link">Activités</a></li>
                <li class="nav-item"><a href="/mvcEvent/View/FrontOffice/evenemant.php" class="nav-link">Événements</a></li>
                <li class="nav-item"><a href="/mvcProduit/view/front office/produit.php" class="nav-link">Produits</a></li>
                <li class="nav-item"><a href="/mvcCovoiturage/view/index.php" class="nav-link">Transports</a></li>
                <li class="nav-item"><a href="#" class="nav-link active">Sponsors</a></li>

                 <!-- Vérification de l'état de connexion -->
            <?php if (!isset($_SESSION['user'])): ?>
                <!-- 🔒 Utilisateur non connecté : bouton vers login -->
                <a href="/mvcUtilisateur/View/BackOffice/login/login.php" class="register-btn" title="Connexion/Inscription">
                    <i class="fas fa-user"></i>
                </a>
            <?php else: ?>
                <!-- 👤 Utilisateur connecté -->
                <div class="user-profile" style="position: relative; display: inline-block;">
                    <?php
                    $user = $_SESSION['user'];
                    $fullName = $user['full_name'] ?? 'U';
                    $initial = strtoupper(substr($fullName, 0, 1));
                    $profilePicture = $user['profile_picture'] ?? '';
                    $verified = isset($user['is_verified']) && $user['is_verified'] == 1;
                    ?>

                    <?php if (!empty($profilePicture) && file_exists($profilePicture)): ?>
                        <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Photo de profil" class="profile-photo" onclick="toggleDropdown()">
                    <?php else: ?>
                        <div class="profile-circle"
                            style="background-color: <?= stringToColor($fullName) ?>;"
                            onclick="toggleDropdown()">
                            <?= $initial ?>
                        </div>
                    <?php endif; ?>

                    <!-- ✅ Badge vérification -->
                    <div class="verification-status" style="position: absolute; bottom: -5px; right: -5px;">
                        <?php if ($verified): ?>
                            <img src="/mvcUtilisateur/assets/icons/verified.png"
                                alt="Compte vérifié"
                                title="Compte Vérifié"
                                style="width: 20px; height: 20px;">
                        <?php else: ?>
                            <img src="/mvcUtilisateur/assets/icons/not_verified.png"
                                alt="Compte non vérifié"
                                title="Compte Non Vérifié"
                                style="width: 20px; height: 20px; cursor: pointer;"
                                onclick="showVerificationPopup()">
                        <?php endif; ?>
                    </div>

                    <!-- Menu déroulant -->
                    <div class="dropdown-menu" id="dropdownMenu" style="display: none; position: absolute; top: 120%; right: 0; background-color: white; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 100;">
                        <a href="/mvcUtilisateur/View/FrontOffice/profile.php" style="display: block; padding: 10px;">👤 Mon Profil</a>
                        <a href="/mvcUtilisateur/View/BackOffice/login/logout.php" style="display: block; padding: 10px;">🚪 Déconnexion</a>
                    </div>
                </div>
            <?php endif; ?>

            <script>
                function toggleDropdown() {
                    const menu = document.getElementById("dropdownMenu");
                    if (menu) {
                        menu.style.display = menu.style.display === "block" ? "none" : "block";
                    }
                }

                function showVerificationPopup() {
                    Swal.fire({
                        title: 'Vérification requise',
                        text: 'Veuillez vérifier votre compte via l’email que vous avez reçu.',
                        icon: 'info',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#6c63ff'
                    });
                }

                // Fermer dropdown quand on clique en dehors
                document.addEventListener("click", function(event) {
                    const dropdown = document.getElementById("dropdownMenu");
                    const profile = document.querySelector(".user-profile");
                    if (dropdown && profile && !profile.contains(event.target)) {
                        dropdown.style.display = "none";
                    }
                });
            </script>
            <style>
                .user-profile {
                    position: relative;
                    display: inline-block;
                }

                .profile-photo {
                    width: 55px;
                    height: 55px;
                    border-radius: 50%;
                    object-fit: cover;
                    cursor: pointer;
                    border: 2px solid purple;
                    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
                }

                .profile-circle {
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-weight: bold;
                    font-size: 16px;
                    cursor: pointer;
                }

                .dropdown-menu {
                    position: absolute;
                    top: 45px;
                    right: 0;
                    background-color: white;
                    border: 1px solid #ddd;
                    padding: 10px;
                    display: none;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                    z-index: 100;
                }

                .user-profile:hover .dropdown-menu {
                    display: block;
                }
            </style>


            </ul>


                            
      <script>
        function hideMessage() {
          const botMessage = document.getElementById("bot-message");
          botMessage.style.display = "none";
        }

        const menuLinks = document.querySelectorAll(".nav-links a");

        menuLinks.forEach(link => {
          link.addEventListener("mouseenter", () => {
            const section = link.textContent.trim().toLowerCase();
            let message = "";

            switch (section) {
              case "accueil":
                message = "Bienvenue chez ClickNgo 🏠";
                break;
              case "activités":
                message = "Prêt pour l’aventure ? Découvre nos activités ! 🧗";
                break;
              case "événements":
                message = "Ne rate pas nos événements exclusifs 🎉";
                break;
              case "produits":
                message = "Regarde ce qu'on a en boutique ! 🛍️";
                break;
              case "transports":
                message = "Besoin d’un covoiturage ? On a ce qu’il faut 🚗";
                break;
              case "sponsors":
                message = "Un grand merci à nos sponsors 🤝";
                break;
              default:
                message = `Tu veux explorer "${section}" ? 🤖`;
            }

            showMessage(message);
          });

          link.addEventListener("mouseleave", hideMessage);

          link.addEventListener("click", (e) => {
            e.preventDefault();
            const url = link.getAttribute("href");
            showMessage("Super choix ! On y va... 🚀");

            setTimeout(() => {
              window.location.href = url;
            }, 1000);
          });
        });
      </script>


            

           

        </nav>

        <!-- Image en plein écran remplaçant la vidéo -->
        <div class="video-container" style="width: 100%; height: 100vh; overflow: hidden;">
            <video autoplay muted loop playsinline style="width: 100%; height: 100vh; object-fit: cover; display: block; margin: 0;" poster="images/video-poster.jpg">
                <source src="video1.mp4" type="video/mp4">
                Vidéo promotionnelle de ClickNGo montrant des personnes souriantes participant à des événements et activités dans un environnement urbain animé, ambiance conviviale et dynamique.
            </video>
        </div>


        <!-- Sponsors -->
        <div class="container">
            <h1>Nos sponsors</h1>
            <div class="sponsors-wrapper" style="position: relative; display: flex; align-items: center; justify-content: center;">
                <div class="sponsors-scroll-container" style="overflow-x: hidden; display: flex; gap: 1rem; width: 100%; max-width: 100%; padding: 1rem 0;">
                    <?php
                    $sponsors = $controller->listSponser();
                    foreach ($sponsors as $sponsor) {
                        if ($sponsor['status'] !== 'accepted') continue;
                        $logoPath = !empty($sponsor['logo']) ? "images/sponsors/" . htmlspecialchars($sponsor['logo']) : "images/default_sponsor.png";
                        $companyName = htmlspecialchars($sponsor['nom_entreprise']);
                        echo '<div class="card" style="width: 200px; height: 220px; flex-shrink: 0; background: #fff; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; overflow: hidden;">';
                        echo '<img src="' . $logoPath . '" alt="Logo de ' . $companyName . '" style="width: 100%; height: 140px; object-fit: contain; padding: 10px;">';
                        echo '<div class="card-content" style="padding: 10px;">';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <script>
                const container = document.querySelector('.sponsors-scroll-container');
                let scrollSpeed = 0.5;

                function autoScroll() {
                    container.scrollLeft += scrollSpeed;
                    if (container.scrollLeft >= container.scrollWidth - container.clientWidth) {
                        container.scrollLeft = 0;
                    }
                    requestAnimationFrame(autoScroll);
                }

                requestAnimationFrame(autoScroll);
            </script>
        </div>





        <!-- Main Sections -->
        <div class="container">
            <div class="options" id="options" style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 3rem;">
                <button id="btnShowSponsorships" class="option-button" type="button" style="flex:1; min-width: 120px;">Opportunités</button>
                <button id="btnShowTracking" class="option-button" type="button" style="flex:1; min-width: 120px;">Suivis</button>
                <button id="btnShowPayment" class="option-button" type="button" style="flex:1; min-width: 120px;height: 40px;">Paiement</button>
            </div>

            <!-- Tracking Section -->
            <div class="tracking-section" id="tracking-section" style="display:none; max-width: 1000px; margin: 0 auto; padding: 0 20px;">
                <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2rem;">Suivi de vos Propositions</h2>

                <?php if (!empty($propositions)): ?>
                    <?php foreach ($propositions as $p): ?>
                        <div class="proposal-card" style="background: white; border-radius: 16px; padding: 2rem; margin-bottom: 2rem;">
                            <br>
                            <h3 style="text-align: center; margin-bottom: 1.5rem; font-size: 1.5rem;"><?= htmlspecialchars($p['nom_entreprise']) ?></h3>

                            <div class="proposal-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; text-align: center;">
                                <p style="margin: 0;"><strong>Email:</strong><br><?= htmlspecialchars($p['email']) ?></p>
                                <p style="margin: 0;"><strong>Téléphone:</strong><br><?= htmlspecialchars($p['telephone']) ?></p>
                                <p style="margin: 0;"><strong>Montant:</strong><br><?= htmlspecialchars($p['montant']) ?> dt</p>
                                <p style="margin: 0;"><strong>Durée:</strong><br><?= htmlspecialchars($p['duree']) ?></p>
                                <p style="margin: 0;"><strong>Avantage proposé:</strong><br><?= htmlspecialchars($p['avantage']) ?></p>
                                <p style="margin: 0;"><strong>Résultat:</strong><br><?= htmlspecialchars($p['status']) ?></p>
                            </div>




                            <?php if (!empty($p['logo'])): ?>
                                <div style="text-align: center; margin-top: 1.5rem;">
                                    <img src="images/sponsors/<?= htmlspecialchars($p['logo']) ?>" alt="Logo de <?= htmlspecialchars($p['nom_entreprise']) ?>" style="max-width: 150px; height: auto;">
                                </div>
                            <?php endif; ?>



                            <div class="proposal-actions">
                                <a style="
       display: inline-flex;
       align-items: center;
       justify-content: center;
       margin: 20px;
       padding: 1.2rem 2.5rem;
       min-height: 50px;
       background: linear-gradient(135deg, #C574E8, #A94ED6);
       color: white;
       border: none;
       border-radius: 2.5rem;
       font-size: 1.1rem;
       font-weight: 600;
       text-decoration: none;
       cursor: pointer;
       transition: all 0.3s ease;
       box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
       line-height: 1.5;
   "
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 16px rgba(255, 107, 107, 0.5)'"
                                    onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(255, 107, 107, 0.4)'"
                                    onmousedown="this.style.transform='translateY(1px)'; this.style.boxShadow='0 2px 8px rgba(255, 107, 107, 0.3)'"
                                    onmouseup="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 16px rgba(255, 107, 107, 0.5)'"
                                    class="button-secondary" href="modifier.php?id=<?= $p['id_sponsor'] ?>">Modifier</a>
                                <a href="delete.php?id=<?= $p['id_sponsor'] ?>"
                                    onclick="return confirm('Supprimer cette proposition ?');"
                                    style="
       display: inline-flex;
       align-items: center;
       justify-content: center;
       margin: 20px;
       padding: 1.2rem 2.5rem;
       min-height: 50px;
       background: linear-gradient(135deg, #C574E8, #A94ED6);
       color: white;
       border: none;
       border-radius: 2.5rem;
       font-size: 1.1rem;
       font-weight: 600;
       text-decoration: none;
       cursor: pointer;
       transition: all 0.3s ease;
       box-shadow: 0 4px 12px rgba(255, 107, 107, 0.4);
       line-height: 1.5;
   "
                                    onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 16px rgba(255, 107, 107, 0.5)'"
                                    onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(255, 107, 107, 0.4)'"
                                    onmousedown="this.style.transform='translateY(1px)'; this.style.boxShadow='0 2px 8px rgba(255, 107, 107, 0.3)'"
                                    onmouseup="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 16px rgba(255, 107, 107, 0.5)'">
                                    Supprimer
                                </a>
                            </div>



                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; padding: 2rem;">Vous n'avez aucune proposition de sponsoring pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Payment Section -->
            <div class="payment-section" id="payment-section" style="display:none; max-width: 500px; margin: 2rem auto; background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 8px 20px rgba(110, 72, 170, 0.15);">
                <br>
                <h2 style="text-align: center; color: #6e48aa; margin-bottom: 1.5rem; font-size: 1.8rem; font-weight: 600;">Formulaire de Paiement</h2>

                <form id="paymentForm" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <label for="card-element" style="color: #6e48aa; font-weight: 500; font-size: 1rem;">Détails de la carte</label>
                        <div id="card-element" class="StripeElement" style="padding: 1rem; border: 1px solid #e0e0e0; border-radius: 12px; transition: all 0.3s ease;"></div>
                        <div id="card-errors" role="alert" style="color: #ff6b6b; font-size: 0.9rem; margin-top: 0.5rem;"></div>
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; align-items: center; margin: 2rem auto;">
                        <label for="paymentCode" style="color: #6e48aa; font-weight: 500; font-size: 1.1rem; margin-bottom: 0.5rem;">
                            Code de paiement
                        </label>
                        <input type="text" id="paymentCode" name="paymentCode" required readonly
                            placeholder="Entrez le code reçu par email"
                            style="padding: 1.5rem 2rem; 
                width: 100%; 
                max-width: 700px;
                font-size: 1.2rem; 
                border: 1px solid #e0e0e0; 
                border-radius: 16px; 
                transition: all 0.3s ease;"
                            onfocus="this.style.borderColor='#9d50bb'; this.style.boxShadow='0 0 0 2px rgba(157, 80, 187, 0.2)'"
                            onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none'">
                    </div>


                    <button type="submit" id="submitButton"
                        style="padding: 1rem; 
           background: linear-gradient(135deg, #6e48aa, #9d50bb); 
           color: white; 
           border: none; 
           border-radius: 12px; 
           font-size: 1rem; 
           font-weight: 600; 
           cursor: pointer; 
           transition: all 0.3s ease; 
           margin: 1rem auto; 
           display: block;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(110, 72, 170, 0.3)'"
                        onmouseout="this.style.transform=''; this.style.boxShadow='none'"
                        onmousedown="this.style.transform='translateY(1px)'"
                        onmouseup="this.style.transform='translateY(-2px)'">
                        Valider le paiement
                    </button>

                    <br>
                </form>
            </div>

            <!-- Sponsorships Section -->
            <div class="sponsorships-section" id="sponsorships-section">
                <h2 style="color: #DFA7FF;">Opportunités de Sponsoring Disponibles</h2>
                <div class="search-container" style="position: relative; max-width: 500px; margin: 0 auto 2rem auto;">
                    <input type="text" id="searchInput" placeholder="Rechercher une offre par titre..." style="padding-right: 2.5rem; text-align: left;height: 40px;">
                    <i class="fas fa-search" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #c122c1;"></i>
                </div>
                <?php
                $offers = $controller->listOffers();
                $displayedOffers = [];
                ?>
                <?php if (!empty($offers)): ?>
                    <div style="position: relative; width: 100%; overflow: hidden;">
                        <button id="scrollLeftOffers" aria-label="Scroll Left">&#8249;</button>
                        <div class="offers-scroll-container" style="display: flex; gap: 1rem; overflow-x: hidden; scroll-behavior: smooth; padding: 10px 60px;">
                            <?php foreach ($offers as $offer): ?>
                                <?php
                                $key = $offer['titre_offre'] . '|' . $offer['evenement'];
                                if (in_array($key, $displayedOffers)) {
                                    continue;
                                }
                                $displayedOffers[] = $key;
                                ?>
                                <div class="sponsorship-card" data-evenement="<?= htmlspecialchars($offer['evenement']) ?>" data-id-offre="<?= htmlspecialchars($offer['id_offre']) ?>" style="min-width: 300px; flex-shrink: 0; border: 1px solid #ccc; border-radius: 8px; padding: 15px; background: white;">
                                    <h3 style="text-align: center;"><?= htmlspecialchars($offer['titre_offre']) ?></h3>
                                    <?php if (!empty($offer['image'])): ?>
                                        <img src="images/<?= htmlspecialchars($offer['image']) ?>" alt="Image de l'offre" style="max-width: 100%; height: 60%; object-fit: cover; border-radius: 8px; margin-bottom: 12px;" />
                                    <?php else: ?>
                                        <img src="images/default.png" alt="Image par défaut" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />
                                    <?php endif; ?>
                                    <p style="margin-left: 10px;"><?= htmlspecialchars($offer['description_offre']) ?></p>
                                    <div class="sponsorship-footer">
                                        <span class="amount"><?= htmlspecialchars($offer['montant_debut']) ?> dt</span>
                                        <?php if ($offer['montant_offre'] <= 0): ?>
                                            <img src="images/sold.png" alt="Événement occupé" style="max-width: 120px; height: 30px; margin-top: 0.5rem; display: block; margin-right: 20px;" />
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($offer['montant_offre'] <= 0): ?>
                                        <button class="request-sponsor-btn" type="button" disabled style="cursor: not-allowed; opacity: 0.6;" title="Offre épuisée">Demander ce sponsoring</button>
                                    <?php else: ?>
                                        <button class="request-sponsor-btn" type="button">Demander ce sponsoring</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button id="scrollRightOffers" aria-label="Scroll Right">&#8250;</button>


                        <style>
                            #scrollLeftOffers,
                            #scrollRightOffers {
                                position: absolute;
                                top: 50%;
                                transform: translateY(-50%);
                                z-index: 10;
                                width: 45px;
                                height: 45px;
                                border-radius: 50%;
                                background-color: white;
                                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                                border: none;
                                font-size: 24px;
                                font-weight: bold;
                                color: #7A2EE5;
                                cursor: pointer;
                                transition: background-color 0.3s ease, transform 0.3s ease;
                            }

                            #scrollLeftOffers:hover,
                            #scrollRightOffers:hover {
                                background-color: #f3e7ff;
                                transform: translateY(-50%) scale(1.1);
                            }

                            #scrollLeftOffers {
                                left: -30px;
                            }

                            #scrollRightOffers {
                                right: -30px;
                            }
                        </style>

                    </div>
                    <script>
                        const offersScrollContainer = document.querySelector('.offers-scroll-container');
                        const scrollLeftOffersBtn = document.getElementById('scrollLeftOffers');
                        const scrollRightOffersBtn = document.getElementById('scrollRightOffers');
                        const scrollAmountOffers = 320; // Adjust based on card width + gap

                        scrollLeftOffersBtn.addEventListener('click', () => {
                            offersScrollContainer.scrollBy({
                                left: -scrollAmountOffers,
                                behavior: 'smooth'
                            });
                        });

                        scrollRightOffersBtn.addEventListener('click', () => {
                            offersScrollContainer.scrollBy({
                                left: scrollAmountOffers,
                                behavior: 'smooth'
                            });
                        });
                    </script>
                <?php else: ?>
                    <p>Aucune opportunité de sponsoring disponible pour le moment.</p>
                <?php endif; ?>
            </div>

            <!-- Sponsor Request Modal -->
            <div id="sponsorRequestModal" class="event-modal" aria-hidden="true" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
                <div class="event-modal-content">
                    <button class="close-modal" aria-label="Fermer">×</button>
                    <div id="offerDetailsSection">
                        <br>
                        <br>
                        <h2 style="text-align: center;margin-top:20px;" id="offerTitle"></h2>
                        <img style="margin-left: 15px;" id="offerImage" src="" alt="" style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 12px;" />
                        <p style="margin-left: 20px;" id="offerDescription"></p>
                        <p style="margin-left: 20px;"><strong>Événement:</strong> <span id="offerEvent"></span></p>
                        <p style="margin-left: 20px;"><strong>Montant:</strong> <span id="offerAmount"></span> dt</p>
                        <ul id="offerBenefits" class="benefits-list"></ul>
                        <button id="btnGoToForm" type="button">Demander ce sponsoring</button>
                        <br>
                    </div>
                    <form method="post" action="addSponsor.php" id="modalSponsorForm" novalidate style="display:none; margin-top: 1rem;" enctype="multipart/form-data">
                        <br>
                        <br>
                        <h2 style="font-family: 'Playfair Display', serif; color: #C33B9E;" id="modalTitle">Formulaire de demande de sponsoring</h2>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalCompanyName">Nom de l'entreprise</label>
                            <input style="margin-left: 20px;margin-right: 20px;" type="text" id="modalCompanyName" name="companyName" pattern="[A-Za-z0-9\u00C0-\u017F\s\-&]{2,100}" title="2-100 caractères alphanumériques" required>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalEmail">Email</label>
                            <input style="margin-left: 20px;margin-right: 20px;" type="email" id="modalEmail" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required readonly>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalPhone">Téléphone</label>
                            <input style="margin-left: 20px;margin-right: 20px;" type="tel" id="modalPhone" name="phone" pattern="^(\+216\s)?[0-9]{8}$" title="Format: +216 XXXXXXXX ou XXXXXXXX" required readonly value="+216 " maxlength="13" />
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalIdOffre">Sélectionnez une offre</label>
                            <select style="margin-left: 20px;" id="modalIdOffre" name="id_offre" required>
                                <option value="">-- Choisissez une offre --</option>
                                <?php foreach ($offers as $offer): ?>
                                    <option value="<?= htmlspecialchars($offer['id_offre']) ?>"><?= htmlspecialchars($offer['titre_offre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalDescription">Description du sponsoring</label>
                            <textarea style="margin-left: 20px;" id="modalDescription" name="description" rows="4" minlength="20" maxlength="1000" required></textarea>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalAmount">Montant proposé (dt)</label>
                            <input style="margin-left: 20px;" type="number" id="modalAmount" name="amount" min="100" step="1" required>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalDuration">Durée du sponsoring</label>
                            <input style="margin-left: 20px;" type="text" id="modalDuration" name="duration" pattern="[0-9]+\s*(mois|an|ans|jours|semaines)" placeholder="Ex: 3 mois, 1 an..." required>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalBenefits">Avantages souhaités</label>
                            <textarea style="margin-left: 20px;" id="modalBenefits" name="benefits" rows="4" minlength="10" maxlength="500" placeholder="Ex: Logo sur les affiches, mentions sur les réseaux sociaux..." required></textarea>
                            <small class="error-message"></small>
                        </div>
                        <div class="form-group">
                            <label style="margin-left: 20px;" for="modalLogo">Logo de l'entreprise</label>
                            <input style="margin-left: 20px;" type="file" id="modalLogo" name="logo" accept="image/*" required>
                            <small class="error-message"></small>
                        </div>
                        <button style="
    margin: 20px auto; /* Centrage horizontal */
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.25rem 3rem; /* Encore plus grand */
    background: linear-gradient(135deg, #a29bfe, #ffb1d3);
    color: white;
    border: none;
    border-radius: 2rem;
    font-size: 1.1rem; /* Texte plus grand */
    font-weight: 600; /* Texte en gras */
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(162, 155, 254, 0.5);
    width: 80%; /* Largeur augmentée */
    max-width: 300px; /* Largeur maximale */
    max-height: 100px; /* Hauteur maximale */
    position: relative;
    overflow: hidden;
">
                            Envoyer la proposition
                            <span style="
        position: absolute;
        background: white;
        transform: translate(-50%, -50%);
        pointer-events: none;
        border-radius: 50%;
        animation: ripple 1s linear infinite;
        opacity: 0;
    "></span>
                        </button>
                        <br>
                    </form>
                </div>
            </div>
        </div>


    <div class="footer-wrapper">

      <div class="newsletter">
        <div class="newsletter-left">
          <h3>Abonnez-vous à notre</h3>
          <h1>Click'N'Go</h1>
        </div>
        <div class="newsletter-right">
          <div class="newsletter-input">
            <input type="text" placeholder="Entrez votre adresse e-mail" />
            <button class="fotter-btn">Valider</button>
          </div>
        </div>
      </div>

      <div class="footer-content">
        <div class="footer-main">
          <div class="footer-brand">
            <img src="images/logo.png" alt="click'N'go Logo" class="footer-logo">
          </div>
          <p>Rejoignez nous aussi sur :</p>
          <div class="social-icons">
            <a href="#" style="--color: #0072b1" class="icon"><i class="fa-brands fa-linkedin"></i></a>
            <a href="#" style="--color: #E1306C" class="icon"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" style="--color: #FF0050" class="icon"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" style="--color: #4267B2" class="icon"><i class="fa-brands fa-facebook"></i></a>
          </div>
        </div>

        <div class="links">
          <p>Moyens de paiement</p>
          <div class="payment-methods">
            <img src="images/visa.webp" alt="Visa">
            <img src="images/mastercard-v2.webp" alt="Mastercard">
            <img src="images/logo-cb.webp" alt="CB" class="cb-logo">
            <img src="images/paypal.webp" alt="Paypal" class="paypal">
          </div>
        </div>

        <div class="links">
          <p>À propos</p>
          <a href="/mvcact/view/front%20office/about.php">À propos</a>
          <script>
            const aboutLink = document.querySelector('a[href*="about.php"]');

            if (aboutLink) {
              aboutLink.addEventListener('mouseenter', () => {
                showMessage("Tu veux en savoir plus sur nous ? C’est par ici ! 📖✨");
              });

              aboutLink.addEventListener('mouseleave', hideMessage);

              aboutLink.addEventListener('click', (e) => {
                showMessage("Chargement de la page À propos... ⏳");
                // Optionnel : attends avant redirection
                // e.preventDefault();
                // setTimeout(() => {
                //   window.location.href = aboutLink.getAttribute('href');
                // }, 1500);
              });
            }
          </script>


          <a href="/mvcact/view/front%20office/presse.php">Presse</a>
          <script>
            const presseLink = document.querySelector('a[href*="presse.php"]');

            if (presseLink) {
              presseLink.addEventListener('mouseenter', () => {
                showMessage("Curieux de voir ce que la presse dit de nous ? 📰✨");
              });

              presseLink.addEventListener('mouseleave', hideMessage);

              presseLink.addEventListener('click', (e) => {
                showMessage("Chargement des articles de presse... ⏳");
                // Optionnel : petite pause avant redirection
                e.preventDefault();
                setTimeout(() => {
                  window.location.href = presseLink.getAttribute('href');
                }, 1500);
              });
            }
          </script>

          <a href="/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>
          <script>
            const joinLink = document.querySelector('a[href*="nous-rejoindre.php"]');

            if (joinLink) {
              joinLink.addEventListener('mouseenter', () => {
                showMessage("Envie de faire partie de l’aventure ? Rejoins-nous ! 🚀");
              });

              joinLink.addEventListener('mouseleave', hideMessage);

              joinLink.addEventListener('click', (e) => {
                showMessage("Préparation du formulaire d’inscription... ✍️");
                e.preventDefault();
                setTimeout(() => {
                  window.location.href = joinLink.getAttribute('href');
                }, 1500);
              });
            }
          </script>

        </div>

        <div class="links">
          <p>Liens utiles</p>
          <a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>

          <script>
            const partnerLinkFooter = document.getElementById('partnerLink');

            partnerLinkFooter.addEventListener('mouseenter', () => {
              showMessage("Envie de collaborer avec nous ? Deviens partenaire ! 🤝");
            });

            partnerLinkFooter.addEventListener('mouseleave', hideMessage);

            partnerLinkFooter.addEventListener('click', (e) => {
              showMessage("On t’emmène vers la page partenaire... 🚀");
              e.preventDefault();
              setTimeout(() => {
                window.location.href = partnerLinkFooter.getAttribute('href');
              }, 1500);
            });
          </script>

          <a href="/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>

          <script>
            const faqLink = document.getElementById('faqLink');

            faqLink.addEventListener('mouseenter', () => {
              showMessage("Des questions ? La FAQ est là pour t’aider ! ❓");
            });

            faqLink.addEventListener('mouseleave', hideMessage);

            faqLink.addEventListener('click', () => {
              showMessage("Tu es redirigé vers la FAQ... 📖");
            });
          </script>

          <a href="/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
          <script>
            const avisLink = document.getElementById('avisLink');

            avisLink.addEventListener('mouseenter', () => {
              showMessage("Découvrez ce que les autres pensent de nous ! ⭐️");
            });

            avisLink.addEventListener('mouseleave', hideMessage);

            avisLink.addEventListener('click', () => {
              showMessage("Redirection vers les avis... 📢");
            });
          </script>

        </div>
      </div>

      <div class="footer-section">
        <hr>
        <div class="footer-separator"></div>
        <div class="footer-bottom">
          <p>© click'N'go 2025 - tous droits réservés</p>
          <div class="footer-links-bottom">
            <a href="/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]" id="conditionsLink">Conditions générales</a>

            <script>
              const conditionsLink = document.getElementById('conditionsLink');

              conditionsLink.addEventListener('mouseenter', () => {
                showMessage("Consultez nos conditions générales pour en savoir plus 📜");
              });

              conditionsLink.addEventListener('mouseleave', hideMessage);

              conditionsLink.addEventListener('click', () => {
                showMessage("Redirection vers les conditions générales...");
              });
            </script>

            <a href="/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]" id="partnerLink">Devenir partenaire</a>

            <script>
              const partnerLinkBottom = document.getElementById('partnerLink');

              partnerLinkBottom.addEventListener('mouseenter', () => {
                showMessage("Envie de collaborer avec nous ? Découvrez comment devenir partenaire 🤝");
              });

              partnerLinkBottom.addEventListener('mouseleave', hideMessage);

              partnerLinkBottom.addEventListener('click', () => {
                showMessage("Redirection vers la page Devenir partenaire...");
              });
            </script>

          </div>
        </div>
      </div>
    </div>

        <style>
 /* ===== FOOTER STYLES ===== */

.footer-wrapper {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    background-color: white;
    background-attachment: fixed;
    background-position: center;
    background-size: cover;
    color: #333;
}



.footer-logo {
    width: 200px;
    margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
}

.newsletter {
    display: flex;
    width: 100%;
    position: relative;
    top: 60px;
    max-width: 1000px;
    margin: auto;
    background-color: #303035;
    justify-content: space-around;
    align-items: center;
    padding: 20px 15px;
    border-radius: 10px;
}

.newsletter-left h2 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1rem;
    opacity: 0.5;
    letter-spacing: 1px;
}

.newsletter-left h1 {
    color: #ffffff;
    text-transform: uppercase;
    font-size: 1.5rem;
}

.newsletter-right {
    width: 500px;
}

.newsletter-input {
    background-color: #ffffff;
    padding: 5px;
    border-radius: 20px;
    display: flex;
    justify-content: space-between;
}

.newsletter-input input {
    border: none;
    outline: none;
    background: transparent;
    width: 80%;
    padding-left: 10px;
    font-weight: 600;
}

.newsletter-input button {
    background-color: #201e1e;
    padding: 9px 15px;
    border-radius: 15px;
    color: #ffffff;
    cursor: pointer;
    border: none;
}

.newsletter-input button:hover {
    background-color: #3a3939;
}

.footer-content {
    background-color:  #f4f4f4;
    padding: 100px 40px 40px;
    width: 100%;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footer-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    margin-bottom: 20px;
}

.footer-main h2 {
    color: #ffffff;
    font-size: 1.6rem;
}

.footer-main p {
    color: 1c3f50;
    font-size: 0.8rem;
    line-height: 1.3rem;
}
.footer-bottom {
  background-color:  #f4f4f4;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #666;
    padding: 20px 40px 20px;
}

.footer-links-bottom a {
    margin-left: 20px;
    color: #666;
    text-decoration: none;
}

.footer-links-bottom a:hover {
    text-decoration: underline;
}
.payment-methods {
    display: flex;
    align-items: center;
    gap: 12px;
}

.payment-methods img {
    width: 50px;
    height: auto;
    margin-right: 10px;
    vertical-align: middle;
    object-fit: contain; /* garde le ratio sans déformer */
}

/* Spécifique au logo CB si besoin d’ajustement fin */
.cb-logo {
    transform: translateY(2px); /* ajuste légèrement verticalement */
    width: 50px; /* assure qu’il ait la même taille */
}



.social-links {
    margin: 15px 0px;
    display: flex;
    gap: 8px;
}

.social-links a {
    padding: 5px;
    background-color: black;
    border-radius: 5px;
    transition: 0.5s;
    text-decoration: none;
}

.social-links a:hover {
    opacity: 0.7;
}

.social-links a i {
    margin: 2px;
    font-size: 1.1rem;
    color: #201e1e;
}

.links {
    display: flex;
    flex-direction: column;
    width: 200px;
    margin: 40px 20px;
}

.links p {
    color: #1c3f50;
    font-size: 1.1rem;
    margin-bottom: 10px;
    font-weight: bold;
}

.links a {
    color: #1c3f50;
    text-decoration: none;
    margin: 5px 0;
    opacity: 0.7;
    font-size: 0.9rem;
}

.links a:hover {
    opacity: 1;
}

.social-icons {
    display: flex;
    flex-direction: row; /* ✅ forcer l'affichage en ligne */
    flex-wrap: nowrap;   /* ✅ pas de retour à la ligne */
    justify-content: center; /* ✅ centrer les icônes horizontalement */
    align-items: center;
    gap: 15px;
    margin-top: 10px;
}


@import url(https://use.fontawesome.com/releases/v5.0.8/css/all.css);

.icon {
    margin: 0 10px;
    margin-bottom: 30px;
    border-radius: 50%;
    box-sizing: border-box;
    background: transparent;
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none !important;
    transition: 0.5s;
    color: var(--color);
    font-size: 2.5em;
    -webkit-box-reflect: below 5px linear-gradient(to bottom, rgba(0, 0, 0, 0),rgba(0, 0, 0, 0.2));
}

.icon i {
    color: var(--color);
}

.icon:hover {
    background: var(--color);
    box-shadow: 0 0 5px var(--color),
                0 0 25px var(--color), 
                0 0 50px var(--color),
                0 0 200px var(--color);
}

/* ✅ changer la couleur de l’icône en noir au survol */
.icon:hover i {
    color: #050801;
}

.fotter-btn{
  background: linear-gradient(to right, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  letter-spacing: 0.5px; /* Espace entre les lettres */  
}

.fotter-btn:hover {
  background: linear-gradient(to left, #be3cf0, #dc46d7 17%, #ff50aa 68%, #ff6666);
  transform: scale(1.05);
}

        </style>

        <script>
            // Pass PHP variables to JavaScript
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            const loginUrl = '/mvcUtilisateur/View/BackOffice/login/login.php';
            const userEmail = <?php echo json_encode($_SESSION['user']['email'] ?? ''); ?>;
            const userPhone = <?php echo json_encode($_SESSION['user']['num_user'] ?? ''); ?>;
            // Stripe Initialization
            const stripe = Stripe('pk_test_51RLtBORvSkgkxHMRC9pvstztm4myG6sE7n04iYjq8BfaQJKxNp1dtd5dWzLFRSruZTCQpQsyUSlHYnVKI88h8C2F00mmuKWGO3');
            const elements = stripe.elements();
            const card = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#e74c3c'
                    }
                }
            });
            card.mount('#card-element');

            card.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                } else {
                    displayError.textContent = '';
                    displayError.style.display = 'none';
                }
            });

            // Main JavaScript
            document.addEventListener('DOMContentLoaded', function() {
                // Section toggling
                const btnShowSponsorships = document.getElementById('btnShowSponsorships');
                const btnShowTracking = document.getElementById('btnShowTracking');
                const btnShowPayment = document.getElementById('btnShowPayment');
                const sponsorshipsSection = document.getElementById('sponsorships-section');
                const trackingSection = document.getElementById('tracking-section');
                const paymentSection = document.getElementById('payment-section');

                btnShowSponsorships.addEventListener('click', () => {
                    sponsorshipsSection.style.display = 'block';
                    trackingSection.style.display = 'none';
                    paymentSection.style.display = 'none';
                });

                btnShowTracking.addEventListener('click', () => {
                    sponsorshipsSection.style.display = 'none';
                    trackingSection.style.display = 'block';
                    paymentSection.style.display = 'none';
                });

                btnShowPayment.addEventListener('click', () => {
                    // Check if URL has valid code parameter for payment access
                    const urlParams = new URLSearchParams(window.location.search);
                    const code = urlParams.get('code');
                    if (!code) {
                        alert('Votre demande doit d\'abord être acceptée. Veuillez consulter votre email pour le lien d\'accès au paiement.');
                        // Do not show payment section
                        sponsorshipsSection.style.display = 'block';
                        trackingSection.style.display = 'none';
                        paymentSection.style.display = 'none';
                    } else {
                        sponsorshipsSection.style.display = 'none';
                        trackingSection.style.display = 'none';
                        paymentSection.style.display = 'block';
                    }
                });

                // Handle URL parameters for payment section
                function getQueryParam(param) {
                    const urlParams = new URLSearchParams(window.location.search);
                    return urlParams.get(param);
                }

                if (getQueryParam('payment') === '1') {
                    sponsorshipsSection.style.display = 'none';
                    trackingSection.style.display = 'none';
                    paymentSection.style.display = 'block';
                    const code = getQueryParam('code');
                    if (code) {
                        document.getElementById('paymentCode').value = code;
                    }
                }

                // Payment form submission
                const paymentForm = document.getElementById('paymentForm');
                const submitButton = document.getElementById('submitButton');
                paymentForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    submitButton.disabled = true;

                    const paymentCode = document.getElementById('paymentCode').value.trim();
                    const sponsorId = getQueryParam('id');

                    if (!paymentCode) {
                        alert('Veuillez entrer le code de paiement.');
                        submitButton.disabled = false;
                        return;
                    }

                    try {
                        const createResponse = await fetch('payment_handler.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'create',
                                id_sponsor: sponsorId,
                                payment_code: paymentCode
                            })
                        });

                        if (!createResponse.ok) {
                            throw new Error(`HTTP error! Status: ${createResponse.status}`);
                        }

                        const createData = await createResponse.json();
                        if (!createData.success) {
                            alert(createData.message);
                            submitButton.disabled = false;
                            return;
                        }

                        const result = await stripe.confirmCardPayment(createData.clientSecret, {
                            payment_method: {
                                card: card,
                                billing_details: {}
                            }
                        });

                        if (result.error) {
                            document.getElementById('card-errors').textContent = result.error.message;
                            document.getElementById('card-errors').style.display = 'block';
                            submitButton.disabled = false;
                        } else if (result.paymentIntent.status === 'succeeded') {
                            const verifyResponse = await fetch('payment_handler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    action: 'verify',
                                    id_sponsor: sponsorId,
                                    payment_code: paymentCode,
                                    payment_intent_id: result.paymentIntent.id
                                })
                            });

                            if (!verifyResponse.ok) {
                                throw new Error(`HTTP error! Status: ${verifyResponse.status}`);
                            }

                            const verifyData = await verifyResponse.json();
                            if (verifyData.success) {
                                alert('Paiement effectué avec succès !');
                                setTimeout(() => {
                                    window.location.href = 'http://localhost:8000/mvcSponsor/crud/view/front/index.php';
                                }, 100); // petit délai pour laisser le temps à l'alerte de se fermer
                            } else {
                                alert('Erreur lors de la vérification du paiement : ' + verifyData.message);
                            }

                            submitButton.disabled = false;
                        }
                    } catch (error) {
                        console.error('Fetch Error:', error);
                        alert('Une erreur est survenue. Veuillez vérifier votre connexion et réessayer.');
                        submitButton.disabled = false;
                    }
                });

                // Modal and Sponsorship Request Logic
                const modal = document.getElementById('sponsorRequestModal');
                const closeModalBtn = modal.querySelector('.close-modal');
                const requestButtons = document.querySelectorAll('.request-sponsor-btn');
                const modalIdOffre = document.getElementById('modalIdOffre');
                const offerDetailsSection = document.getElementById('offerDetailsSection');
                const modalSponsorForm = document.getElementById('modalSponsorForm');
                const btnGoToForm = document.getElementById('btnGoToForm');

                function clearForm() {
                    modalSponsorForm.reset();
                    modalIdOffre.value = '';
                    document.querySelectorAll('#modalSponsorForm .error-message').forEach(msg => msg.style.display = 'none');
                }

                function fillFormWithOffer(offerId) {
                    modalIdOffre.value = offerId;
                }

                function showOfferDetails() {
                    offerDetailsSection.style.display = 'block';
                    modalSponsorForm.style.display = 'none';
                }

                function showForm() {
                    offerDetailsSection.style.display = 'none';
                    modalSponsorForm.style.display = 'block';

                    // Pre-fill email and phone fields
                    document.getElementById('modalEmail').value = userEmail;
                    document.getElementById('modalPhone').value = userPhone;

                    // Make fields read-only
                    document.getElementById('modalEmail').readOnly = true;
                    document.getElementById('modalPhone').readOnly = true;
                }

                function populateOfferDetails(card) {
                    const title = card.querySelector('h3').textContent;
                    const description = card.querySelector('p').textContent;
                    const event = card.getAttribute('data-evenement') || 'Non spécifié';
                    const amount = card.querySelector('.amount').textContent.replace(' dt', '');
                    const img = card.querySelector('img');
                    const imageSrc = img ? img.src : 'images/default.png';
                    const imageAlt = img ? img.alt : 'Image par défaut';

                    document.getElementById('offerTitle').textContent = title;
                    document.getElementById('offerImage').src = imageSrc;
                    document.getElementById('offerImage').alt = imageAlt;
                    document.getElementById('offerDescription').textContent = description;
                    document.getElementById('offerEvent').textContent = event;
                    document.getElementById('offerAmount').textContent = amount;
                    document.getElementById('offerBenefits').innerHTML = ''; // Clear benefits as not provided in data
                }

                requestButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const card = button.closest('.sponsorship-card');
                        if (!card) {
                            console.error('No sponsorship card found for button');
                            return;
                        }

                        populateOfferDetails(card);
                        clearForm();
                        fillFormWithOffer(card.getAttribute('data-id-offre'));
                        showOfferDetails();

                        modal.classList.add('show');
                        modal.setAttribute('aria-hidden', 'false');
                    });
                });

                btnGoToForm.addEventListener('click', () => {
                    showForm();
                });

                closeModalBtn.addEventListener('click', () => {
                    modal.classList.remove('show');
                    modal.setAttribute('aria-hidden', 'true');
                    clearForm();
                });

                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('show');
                        modal.setAttribute('aria-hidden', 'true');
                        clearForm();
                    }
                });

                // Search filter
                const searchInput = document.getElementById('searchInput');
                const sponsorshipCards = document.querySelectorAll('.sponsorship-card');
                searchInput.addEventListener('input', () => {
                    const filter = searchInput.value.toLowerCase();
                    sponsorshipCards.forEach(card => {
                        const title = card.querySelector('h3').textContent.toLowerCase();
                        card.style.display = title.includes(filter) ? '' : 'none';
                    });
                });

                // Form validation
                modalSponsorForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    document.querySelectorAll('#modalSponsorForm [required]').forEach(field => {
                        const errorMsg = field.nextElementSibling;
                        if (!field.checkValidity()) {
                            errorMsg.textContent = field.validationMessage || 'Ce champ est invalide';
                            errorMsg.style.display = 'block';
                            isValid = false;
                        } else {
                            errorMsg.style.display = 'none';
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        alert('Veuillez corriger les erreurs avant soumission');
                    }
                });

                document.querySelectorAll('#modalSponsorForm input, #modalSponsorForm textarea, #modalSponsorForm select').forEach(field => {
                    field.addEventListener('input', function() {
                        const errorMsg = field.nextElementSibling;
                        if (!this.checkValidity()) {
                            errorMsg.textContent = this.validationMessage || 'Valeur invalide';
                            errorMsg.style.display = 'block';
                        } else {
                            errorMsg.style.display = 'none';
                        }
                    });
                });

                // Navbar visibility toggle on scroll
                // The navbar remains fixed at top (position 0) but becomes invisible after scrolling past the video container height
                const navbar = document.querySelector('.navbar');
                const videoContainer = document.querySelector('.video-container');
                const hideThreshold = videoContainer ? videoContainer.offsetHeight : 300;

                window.addEventListener('scroll', () => {
                    if (window.scrollY > hideThreshold) {
                        navbar.classList.add('hidden');
                    } else {
                        navbar.classList.remove('hidden');
                    }
                });
            });
        </script>

    </body>

</html>