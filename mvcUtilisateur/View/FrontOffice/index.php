<?php
session_start(); // Démarre la session pour vérifier l'état de connexion



if (!isset($_SESSION['video_seen'])) {
  $_SESSION['video_seen'] = true;
  header("Location: intro.php");
  exit();
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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>click'N'go</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <script type="module" crossorigin src="https://panorama-slider.uiinitiative.com/assets/index.d2ce9dca.js"></script>
  <link rel="modulepreload" href="https://panorama-slider.uiinitiative.com/assets/vendor.dba6b2d2.js">
  <link rel="stylesheet" href="https://panorama-slider.uiinitiative.com/assets/index.c1d53924.css">


  <script src="../FrontOffice/main.js"></script>

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

    .search-bar {
      align-items: center;
      background: rgba(255, 255, 255, 0.1);
      border: 2px solid var(--spanish-gray);
      border-radius: 50px;
      overflow: hidden;
      margin: 20px auto;
      max-width: 800px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(5px);
      height: 40px;
      margin-top: 400px !important;
    }

    .background-video {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: -1;
      filter: brightness(0.6);
      /* pour l'effet sombre */
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

    #search1,
    #exploreBtn {
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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
      0% {
        transform: scale(0.8);
        opacity: 0;
      }

      60% {
        transform: scale(1.05);
        opacity: 1;
      }

      100% {
        transform: scale(1);
      }
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

    #scanMoodBtn,
    #toggleVoice {
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
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    #scanMoodBtn:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
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
      try {
        head = viewer.scene.getObjectByName('Head');
      } catch {}
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
        mediaStream = await navigator.mediaDevices.getUserMedia({
          video: {
            width: 640,
            height: 480
          }
        });
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




  <div class="header">

    <video autoplay muted loop playsinline class="background-video">
      <source src="video/campfire.mp4" type="video/mp4">
      Votre navigateur ne supporte pas les vidéos HTML5.
    </video>

    <nav>
      <img src="images/logo.png" class="logo">
      <ul class="nav-links">
        <li><a href="#">Accueil</a></li>
        <li><a href="/Projet Web/mvcact/view/front office/activite.php">Activités</a></li>
        <li><a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php">Événements</a></li>
        <li><a href="/Projet Web/mvcProduit/view/front office/produit.php">Produits</a></li>
        <li><a href="/Projet Web/mvcCovoiturage/view/index.php">Transports</a></li>
        <li><a href="/Projet%20Web/mvcSponsor/crud/view/front/index.php">Sponsors</a></li>
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


      <style>
        #reels-btn {
          position: fixed;
          bottom: 20px;
          /* éloigné du bas */
          right: 20px;
          /* éloigné de la droite */
          z-index: 1000;

          width: 60px;
          height: 60px;
          border-radius: 50%;

          background: linear-gradient(45deg, #FF0050, #FF4500);
          color: white;
          border: none;
          font-size: 24px;
          font-weight: bold;
          cursor: pointer;

          box-shadow: 0 4px 15px rgba(255, 0, 80, 0.4);
          transition: transform 0.3s ease;
        }

        #reels-btn:hover {
          transform: scale(1.1);
        }

        .video-container {
          height: 100vh;
          overflow-y: scroll;
          scroll-snap-type: y mandatory;
          display: none;
          position: relative;
          background: #000;
          scroll-behavior: smooth;
        }

        .video-item {
          width: 100%;
          height: 100vh;
          scroll-snap-align: start;
          position: relative;
          display: flex;
          justify-content: center;
          align-items: center;
          background: #000;
        }

        .video-wrapper {
          width: 100%;
          max-width: 375px;
          /* Largeur réduite comme Instagram Reels */
          height: calc(375px * 1.777);
          /* Ratio 9:16 */
          position: relative;
          margin: 0 auto;
        }

        .video-item video {
          width: 100%;
          height: 100%;
          object-fit: cover;
          border-radius: 8px;
        }

        .video-info {
          position: absolute;
          bottom: 80px;
          left: 15px;
          max-width: 70%;
          background: rgba(0, 0, 0, 0.6);
          padding: 10px;
          border-radius: 8px;
          backdrop-filter: blur(5px);
        }

        .video-info h3 {
          margin: 0 0 5px 0;
          font-size: 16px;
        }

        .video-info p {
          margin: 0;
          font-size: 12px;
          opacity: 0.8;
        }

        .action-buttons {
          position: fixed;
          right: 15px;
          bottom: 80px;
          display: flex;
          flex-direction: column;
          gap: 20px;
          z-index: 1000;
          display: none;
        }

        .action-btn {
          background: rgba(255, 255, 255, 0.15);
          border: none;
          border-radius: 50%;
          width: 45px;
          height: 45px;
          color: white;
          font-size: 18px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.2s;
          backdrop-filter: blur(5px);
          margin-right: 20px;
        }

        .action-btn:hover {
          transform: scale(1.12);
          background: rgba(255, 255, 255, 0.25);
        }

        #like-btn.liked {
          color: #FF0050;
          background: rgba(255, 0, 80, 0.2);
        }

        .loader {
          display: none;
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: white;
          z-index: 1001;
        }

        .fa-spinner {
          animation: spin 1s linear infinite;
        }

        @keyframes spin {
          0% {
            transform: rotate(0deg);
          }

          100% {
            transform: rotate(360deg);
          }
        }

        .progress-bar {
          position: fixed;
          top: 0;
          left: 0;
          width: 0%;
          height: 3px;
          background: #FF0050;
          z-index: 1002;
          transition: width 0.3s ease;
        }
      </style>


      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

      <button id="reels-btn" data-target="reels.html" data-hover-message="Vidéos tendance">
        <i class="fas fa-clapperboard"></i>
      </button>
      <script>
        // Interaction améliorée avec le bouton Reels
        const reelsBtn = document.getElementById('reels-btn');

        if (reelsBtn) {
          // Au survol
          reelsBtn.addEventListener('mouseenter', () => {
            const messages = [
              "Prêt pour un moment divertissant ? 🎥",
              "Découvre nos vidéos tendance ! 📹",
              "Nos meilleurs moments t'attendent ! 🍿"
            ];
            const randomMsg = messages[Math.floor(Math.random() * messages.length)];
            showMessage(randomMsg);
          });

          // Quand la souris quitte
          reelsBtn.addEventListener('mouseleave', hideMessage);

          // Au clic
          reelsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const loadingMessages = [
              "Préparation des pop-corns... 🍿",
              "Chargement des meilleures vidéos... 📼",
              "Montage en cours... ✂️"
            ];
            const randomLoadingMsg = loadingMessages[Math.floor(Math.random() * loadingMessages.length)];

            showMessage(randomLoadingMsg);

            // Animation du bouton
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
              this.style.transform = 'scale(1)';
            }, 200);

            // Redirection après 1.5 secondes
            setTimeout(() => {
              window.location.href = this.getAttribute('data-target') || 'reels.html';
            }, 1500);
          });
        }



        document.getElementById('reels-btn').addEventListener('click', function() {
          window.location.href = 'reels.html';
        });
      </script>



      <!-- Vérification de l'état de connexion -->
      <?php if (!isset($_SESSION['user'])): ?>
        <!-- 🔒 Utilisateur non connecté : bouton vers login -->
        <a href="../BackOffice/login/login.php" class="register-btn" title="Connexion/Inscription">
          <i class="fas fa-user"></i>
        </a>
      <?php else: ?>
        <!-- 👤 Utilisateur connecté -->
        <div class="user-profile" style="position: relative;">

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
              <img src="/Projet%20Web/mvcUtilisateur/assets/icons/verified.png"
                alt="Compte vérifié"
                title="Compte Vérifié"
                style="width: 20px; height: 20px;">
            <?php else: ?>
              <img src="/Projet%20Web/mvcUtilisateur/assets/icons/not_verified.png"
                alt="Compte non vérifié"
                title="Compte Non Vérifié"
                style="width: 20px; height: 20px; cursor: pointer;"
                onclick="showVerificationPopup()">
            <?php endif; ?>
          </div>

          <!-- Menu déroulant -->
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="/Projet%20Web/mvcUtilisateur/View/FrontOffice/profile.php">👤 Mon Profil</a>
            <a href="/Projet%20Web/mvcUtilisateur/View/BackOffice/login/logout.php">🚪 Déconnexion</a>
          </div>
        </div>
      <?php endif; ?>


      <script>
        function showVerificationPopup() {
          Swal.fire({
            title: 'Vérification requise',
            text: 'Veuillez vérifier votre compte via l’email que vous avez reçu.',
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#6c63ff'
          });
        }
      </script>



    </nav>

    <div class="search-bar">
      <div class="search-inputs">
        <div class="search-location">
          <span class="icon-bar">📍</span>
          <span class="static-text">N'importe où</span>
        </div>
        <div class="search-keywords">
          <span class="icon-bar">🔍</span>
          <input type="text" id="searchInput" placeholder="Rechercher par mots-clés" />
        </div>
      </div>
      <button class="search-btn" id="searchBtn">🔍</button>
    </div>
    <script>
      // Récupération des éléments
      const searchInput2 = document.getElementById('searchInput');
      const searchBtn = document.getElementById('searchBtn');
      const exploreBtn2 = document.getElementById('exploreBtn');

      // Interactions avec le champ de recherche
      searchInput2.addEventListener('focus', () => showMessage("Dis-moi ce que tu veux trouver 😉"));
      searchInput2.addEventListener('input', () => {
        const val = searchInput2.value.trim();
        showMessage(val ? `Hmm… "${val}" ? 🔍` : "Tape quelque chose 👀");
      });
      searchInput2.addEventListener('blur', hideMessage);

      // Fonction de recherche
      function performSearch() {
        const searchTerm = searchInput2.value.trim();

        if (!searchTerm) {
          showMessage("Écris quelque chose à rechercher ! ✏️");
          searchInput2.focus();
          return;
        }

        showMessage(`Je cherche "${searchTerm}" pour toi... 🔍`);

        // Masquer les éléments non pertinents
        document.querySelectorAll('.container > *').forEach(el => {
          el.style.display = 'none';
        });

        // Afficher les résultats (simulés)
        const resultsContainer = document.createElement('div');
        resultsContainer.className = 'search-results';
        resultsContainer.innerHTML = `
    <h2>Résultats pour "${searchTerm}"</h2>
    <div class="result-item">
      <h3>Activité en rapport</h3>
      <p>Description de l'activité trouvée...</p>
    </div>
    <button id="resetSearch" class="see-all-link">Voir tous les résultats</button>
  `;

        document.querySelector('.container').appendChild(resultsContainer);

        // Gestion du bouton reset
        document.getElementById('resetSearch').addEventListener('click', () => {
          resultsContainer.remove();
          document.querySelectorAll('.container > *').forEach(el => {
            el.style.display = '';
          });
          showMessage("Voici à nouveau toutes nos activités ! 🌟");
        });
      }

      // Écouteurs d'événements
      searchBtn.addEventListener('click', performSearch);
      searchInput2.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') performSearch();
      });

      // Interactions avec le bouton Explorer
      exploreBtn2.addEventListener('mouseenter', () => showMessage(`Tu veux "${exploreBtn2.textContent}" ? 😎`));
      exploreBtn2.addEventListener('mouseleave', hideMessage);
      exploreBtn2.addEventListener('click', () => {
        showMessage("D'accord, je t'y emmène ! 🛸");
        // window.location.href = "/activites"; // Décommentez pour la redirection réelle
      });
    </script>

  </div>


  <div class="container">
    <h2 class="subtitle">Sponsors</h2>
    <div class="slider">
      <div class="slide-track">

        <!-- 🎯 PREMIER BLOC SPONSORS -->
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image1.png" alt="Shell" />
            <div class="caption">
              <h3>Shell</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image2.png" alt="Traveltodo" />
            <div class="caption">
              <h3>Traveltodo</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image3.png" alt="Dabchy" />
            <div class="caption">
              <h3>Dabchy</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image4.png" alt="Pathe" />
            <div class="caption">
              <h3>Pathe</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image5.jpeg" alt="Saida" />
            <div class="caption">
              <h3>Saida</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image6.jpeg" alt="Lella" />
            <div class="caption">
              <h3>Lella</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image7.jpg" alt="ooredoo" />
            <div class="caption">
              <h3>ooredoo</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image8.png" alt="Vitalait" />
            <div class="caption">
              <h3>Vitalait</h3>
            </div>
          </a>
        </div>

        <!-- 🔁 DUPLICATION SPONSORS -->
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image1.png" alt="Shell" />
            <div class="caption">
              <h3>Shell</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image2.png" alt="Traveltodo" />
            <div class="caption">
              <h3>Traveltodo</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image3.png" alt="Dabchy" />
            <div class="caption">
              <h3>Dabchy</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image4.png" alt="Pathe" />
            <div class="caption">
              <h3>Pathe</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image5.jpeg" alt="Saida" />
            <div class="caption">
              <h3>Saida</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image6.jpeg" alt="Lella" />
            <div class="caption">
              <h3>Lella</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image7.jpg" alt="ooredoo" />
            <div class="caption">
              <h3>ooredoo</h3>
            </div>
          </a>
        </div>
        <div class="slide">
          <a href="/Projet Web/mvcSponsor/crud/view/front/index.php">
            <img src="/Projet Web/mvcSponsor/crud/view/front/images/sponsors/image8.png" alt="Vitalait" />
            <div class="caption">
              <h3>Vitalait</h3>
            </div>
          </a>
        </div>

      </div>
    </div>

    <script>
      const sponsorSlider = document.querySelector(".slider");

      if (sponsorSlider) {
        sponsorSlider.addEventListener("mouseenter", () => {
          showMessage("Merci à nos sponsors pour leur soutien ! 🤝");
          setTimeout(hideMessage, 5000); // Disparaît après 5 secondes
        });
      }
    </script>

    <div style="text-align: center; margin: 30px 0;">
      <a href="/Projet Web/mvcSponsor/crud/view/front/index.php" class="see-all-link">proposer un sposoring &gt;</a>
    </div>

    <script>
      const sponsorSeeAllLink = document.querySelector(".see-all-link");

      if (sponsorSeeAllLink) {
        sponsorSeeAllLink.addEventListener("mouseenter", () => {
          showMessage("Tu veux devenir sponsor ? C’est par ici ! 🌟");
          setTimeout(hideMessage, 5000); // Cache le message après 5 secondes
        });

        sponsorSeeAllLink.addEventListener("mouseleave", hideMessage);
      }
    </script>

    <script>
      // Interaction avec les sponsors
      document.querySelectorAll('.sponsor-slide .slide').forEach(slide => {
        const link = slide.querySelector('a');
        const sponsorName = slide.querySelector('h3').textContent;

        // Au survol d'un sponsor
        slide.addEventListener('mouseenter', () => {
          const messages = [
            `${sponsorName} est un de nos précieux partenaires ! 💼`,
            `Découvrez les offres ${sponsorName} ! 🎁`,
            `Merci à ${sponsorName} pour son soutien ! 🙏`,
            `${sponsorName} vous réserve des surprises ! 🎉`
          ];
          const randomMsg = messages[Math.floor(Math.random() * messages.length)];
          showMessage(randomMsg);
        });

        // Quand on quitte le sponsor
        slide.addEventListener('mouseleave', hideMessage);

        // Au clic sur un sponsor
        link.addEventListener('click', (e) => {
          e.preventDefault();
          showMessage(`Redirection vers ${sponsorName}...`);
          setTimeout(() => {
            window.location.href = link.getAttribute('href');
          }, 1500);
        });
      });

      // Interaction avec le lien "proposer un sponsoring"
      const sponsorLink = document.querySelector('a[href*="mvcSponsor"]');
      if (sponsorLink) {
        sponsorLink.addEventListener('mouseenter', () => {
          showMessage("Vous voulez devenir sponsor ? C'est par ici ! 💰");
        });

        sponsorLink.addEventListener('mouseleave', hideMessage);

        sponsorLink.addEventListener('click', (e) => {
          e.preventDefault();
          showMessage("Super ! Remplissez le formulaire de sponsoring... ✍️");
          setTimeout(() => {
            window.location.href = sponsorLink.getAttribute('href');
          }, 2000);
        });
      }
    </script>




    <h2 class="subtitle">Catégories des activités</h2>
    <div class="trending">
      <div class="activity-card" data-activity="Ateliers"><img src="images/atelier.jpg" alt="Image 1">
        <h3>Ateliers</h3>
      </div>
      <div class="activity-card" data-activity="Bien-être"><img src="images/bien.jpg" alt="Image 2">
        <h3>Bien-être</h3>
      </div>
      <div class="activity-card" data-activity="Aérien"><img src="images/air.jpg" alt="Image 3">
        <h3>Aérien</h3>
      </div>
      <div class="activity-card" data-activity="Croisières"><img src="images/cro.jpg" alt="Image 4">
        <h3>Croisières</h3>
      </div>
      <div class="activity-card" data-activity="Jeux & énigmes"><img src="images/esq.jpg" alt="Image 5">
        <h3>Jeux & énigmes</h3>
      </div>
      <div class="activity-card" data-activity="Pilotage"><img src="images/pilotage.jpg" alt="Image 6">
        <h3>Pilotage</h3>
      </div>
      <div class="activity-card" data-activity="Visites"><img src="images/visit.jpg" alt="Image 7">
        <h3>Visites</h3>
      </div>
      <div class="activity-card" data-activity="Parcs de loisirs"><img src="images/zone.jpg" alt="Image 8">
        <h3>Parcs de loisirs</h3>
      </div>
      <div class="activity-card" data-activity="Nature"><img src="images/nature.jpg" alt="Image 9">
        <h3>Nature</h3>
      </div>
      <div class="activity-card" data-activity="Aquatique"><img src="images/aqu.jpg" alt="Image 10">
        <h3>Aquatique</h3>
      </div>
      <div class="activity-card" data-activity="Simulateurs"><img src="images/sim.jpg" alt="Image 11">
        <h3>Simulateurs</h3>
      </div>
    </div>

    <!-- Message de ClickBot -->
    <div id="bot-message" style="display: none; position: fixed; bottom: 20px; left: 20px; background: #fff; padding: 10px 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.15); font-size: 16px; z-index: 1000; transition: all 0.3s ease;">
      <!-- Le message s'affiche ici -->
    </div>

    <script>
      const activityCards = document.querySelectorAll(".activity-card");

      const hoverMessages = {
        "Ateliers": "Découvre ton talent caché ! 🎨",
        "Bien-être": "Détends-toi, tu le mérites 😌",
        "Aérien": "Monte plus haut que les nuages ☁️",
        "Croisières": "Une virée relax sur l’eau 🚢",
        "Jeux & énigmes": "Es-tu prêt à relever le défi ? 🧠",
        "Pilotage": "Tiens-toi prêt à décoller ✈️",
        "Visites": "Des lieux incroyables t’attendent 🗺️",
        "Parcs de loisirs": "Des sensations fortes à gogo 🎢",
        "Nature": "Reconnecte-toi avec la nature 🌳",
        "Aquatique": "Sautons dans l’eau ! 🏊‍♂️",
        "Simulateurs": "Prêt pour la réalité virtuelle ? 🕹️"
      };

      const clickMessages = {
        "Ateliers": "On va créer quelque chose ensemble 🖌️",
        "Bien-être": "Prêt pour un moment zen ? 🧘",
        "Aérien": "Envolez-vous vers de nouvelles sensations ✈️",
        "Croisières": "Naviguons vers l’aventure 🚢",
        "Jeux & énigmes": "Mets ton cerveau au défi 🧩",
        "Pilotage": "À fond les manettes ! 🏎️",
        "Visites": "Découvrons des lieux fascinants 🏛️",
        "Parcs de loisirs": "Amusement garanti 🎢",
        "Nature": "Respirons un bon bol d’air frais 🌿",
        "Aquatique": "Prêt à plonger ? 🌊",
        "Simulateurs": "Immersion totale garantie 🎮"
      };


      activityCards.forEach(card => {
        const activity = card.getAttribute("data-activity");

        card.addEventListener("mouseenter", () => {
          const message = hoverMessages[activity] || `Découvre "${activity}" 🤖`;
          showMessage(message);
        });

        card.addEventListener("click", () => {
          const message = clickMessages[activity] || "Prépare-toi à l'aventure ! 🤩";
          showMessage(message);

          setTimeout(() => {
            window.location.href = "/Projet Web/mvcact/view/front office/activite.php";
          }, 4000);
        });
      });
    </script>


    <div class="see-all-events">
      <a href="activite.html" class="see-all-link">Voir Toutes nos activités &gt;</a>
      <section class="atouts">
        <div class="atout">
          <img src="images/atout1.webp" alt="Activités" />
          <div>
            <h3>Des offres adaptées à votre événement</h3>
            <p>15 000 activités</p>
          </div>
        </div>
        <div class="atout">
          <img src="images/atout2.webp" alt="Prix" />
          <div>
            <h3>Même prix qu'en direct</h3>
            <p>Annulation gratuite</p>
          </div>
        </div>
        <div class="atout">
          <img src="images/atout3.webp" alt="Contact" />
          <div>
            <h3>Un contact dédié à votre projet</h3>
            <p>Joignable du lundi au vendredi</p>
          </div>
        </div>
      </section>
    </div>


    <h2 class="subtitle">Nos Événements</h2>
    <!-- HTML des événements -->
    <div class="events">
      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Festivals & Culture">
        <img src="images/fes.jpg" alt="Événement 1">
        <h3>Festivals & Culture</h3>
      </a>

      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Concerts & Musique">
        <img src="images/mus.jpg" alt="Événement 2">
        <h3>Concerts & Musique</h3>
      </a>

      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Famille & Enfants">
        <img src="images/fam.jpg" alt="Événement 3">
        <h3>Famille & Enfants (Kids Friendly)</h3>
      </a>

      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Challenges & Récompenses">
        <img src="images/groupe.jpg" alt="Événement 4">
        <h3>Récompenses ou Challenges</h3>
      </a>

      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Événements Thématiques">
        <img src="images/hallo.jpg" alt="Événement 5">
        <h3>Saisonniers ou Thématiques</h3>
      </a>

      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="event-card" data-type="Soirées privées">
        <img src="images/fete.jpg" alt="Événement 6">
        <h3>Privés / Fêtes</h3>
      </a>
    </div>

    <!-- Zone de message du robot -->

    <script>
      function hideMessage() {
        const msgBox = document.getElementById('message-box');
        if (msgBox) {
          msgBox.style.display = 'none';
        }
      }

      // Script pour gérer les interactions sur les événements
      const eventCards = document.querySelectorAll('.events .event-card');

      eventCards.forEach(card => {
        const eventName = card.getAttribute('data-type') || 'cet événement';

        const messages = [
          `Ne ratez pas nos événements ${eventName} ! 🎉`,
          `${eventName} vous attend avec des surprises ! 🌟`,
          `Prêt pour ${eventName} ? Cliquez ici ! 😉`,
          `Explorez les temps forts de ${eventName} ! 📅`
        ];

        card.addEventListener('mouseenter', () => {
          const randomMsg = messages[Math.floor(Math.random() * messages.length)];
          showMessage(randomMsg);
        });

        card.addEventListener('mouseleave', () => {
          hideMessage();
        });

        card.addEventListener('click', (e) => {
          e.preventDefault();
          showMessage(`Redirection vers ${eventName}...`);
          setTimeout(() => {
            window.location.href = card.getAttribute('href');
          }, 1500);
        });
      });
    </script>




    <div class="see-all-events">
      <a href="/Projet Web/mvcEvent/View/FrontOffice/evenemant.php" class="see-all-link">Voir tous les événements &gt;</a>

    </div>

    <h2 class="subtitle">Nos Produits</h2>
    <div class="panorama-slider">
      <div class="swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide"><img class="slide-image" src="images/p1.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p2.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p3.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p4.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p5.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p6.jpg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p7.jpeg" alt=""></div>
          <div class="swiper-slide"><img class="slide-image" src="images/p8.jpeg" alt=""></div>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>


    <script>
      function hideMessage() {
        const msgBox = document.getElementById('message-box');
        if (msgBox) {
          msgBox.style.display = 'none';
        }
      }

      // Message dynamique au survol du panorama
      const panoramaSlides = document.querySelectorAll('.panorama-slider .swiper-slide');

      panoramaSlides.forEach((slide, index) => {
        slide.addEventListener('mouseenter', () => {
          const messages = [
            "Enfile ton style sportif avec nos vêtements tendance 🏃‍♂️👕",
            "Découvre les dernières technologies pour te simplifier la vie 🔌📱",
            "Mange sain, vis mieux avec notre sélection healthy 🥗💪",
            "Équipe-toi pour t'entraîner comme un pro 🏋️‍♀️🧘‍♂️",
            "Exprime ta créativité avec notre matériel de peinture 🎨🖌️",
            "Stimule ton esprit avec nos puzzles et jeux de cartes 🧩🃏",
            "Prépare-toi à marquer des buts avec nos équipements de sport ⚽🏀",
            "ClickNgo t’apporte tout pour un quotidien actif et fun 🚀🛒"
          ];
          const msg = messages[index % messages.length];
          showMessage(msg);
        });

        slide.addEventListener('mouseleave', hideMessage);
      });
    </script>


    <div class="see-all-events">
      <a href="/Projet Web/mvcProduit/view/front office/produit.php" class="see-all-link">Voir tous les produits &gt;</a>

    </div>

    <!-- COVOITURAGE SECTION START -->
    <section class="carpooling-home">
      <div class="carpooling-container">
        <div class="carpooling-content">
          <h2>Covoiturez en groupe pour vos loisirs préférés</h2>
          <ul class="carpooling-features">
            <li class="feature-item">
              <img src="images/reser.png" alt="Check" class="feature-icon">
              Réservation simple et rapide en ligne
            </li>
            <li class="feature-item">
              <img src="images/tra.png" alt="Check" class="feature-icon">
              Flexibilité des horaires et des trajets
            </li>
            <li class="feature-item">
              <img src="images/eco.png" alt="Check" class="feature-icon">
              Économique et respectueux de l'environnement
            </li>
          </ul>
          <div style="text-align: center; margin-top: 40px;">
            <button class="carpooling-btn">Covoiturer</button>
          </div>

          <script>
            const carpoolingBtn = document.querySelector('.carpooling-btn');

            carpoolingBtn.addEventListener('mouseenter', () => {
              showMessage("Tu veux partager un trajet ? 🚗💨 Click ici !");
            });

            carpoolingBtn.addEventListener('mouseleave', hideMessage);

            carpoolingBtn.addEventListener('click', (e) => {
              e.preventDefault();
              showMessage("Redirection vers la page de covoiturage... 🚙");
              setTimeout(() => {
                window.location.href = "/Projet Web/mvcCovoiturage/view/index.php";
              }, 1500);
            });
          </script>


        </div>
        <div class="carpooling-image">
          <img src="images/cou.jpg" alt="Service de Covoiturage">
        </div>
      </div>
    </section>


    <!-- COVOITURAGE SECTION END -->*
    <section id="activites-tunisie" class="bg-gradient-to-b from-white to-[#f3f4f6] py-12 px-6 text-gray-800">
      <div class="max-w-5xl mx-auto text-center">
        <h2 style="font-size: 2.5rem; font-weight: bold; color: #a604ab; margin-bottom: 1.5rem;">
          Les meilleures activités en Tunisie pour s’amuser à fond
        </h2>
        <p class="text-lg mb-8">
          Vous cherchez une idée sortie ? Envie de passer un bon moment en famille, entre amis ou entre collègues ? En Tunisie, vous avez l’embarras du choix !
        </p>

        <div class="grid md:grid-cols-2 gap-8 text-left">
          <div>
            <h3 class="text-xl font-semibold" style="color: #FFA500; margin-bottom: 0.5rem;">🎂 Pour chaque occasion</h3>
            <ul class="list-disc list-inside space-y-1">
              <li><strong>Anniversaires enfants</strong> : chasse au trésor, aire de jeux, escape game, anniversaires à thème.</li>
              <li><strong>Cadeaux</strong> : balade à cheval, karting, ateliers créatifs ou sensations fortes.</li>
              <li><strong>Team building</strong> : kayak, pique-nique, pétanque, journée détente ou sportive.</li>
              <li><strong>EVG / EVJF</strong> : tir à l’arc, chasse aux trésors, fitness, randonnée, ULM...</li>
            </ul>
          </div>

          <div>
            <h3 class="text-xl font-semibold" style="color: #FFA500; margin-bottom: 0.5rem;">🎯 Activités par thème</h3>
            <ul class="list-disc list-inside space-y-1">
              <li><strong>Nature et sensations</strong> : accrobranche, quad, randonnée, rafting, parapente…</li>
              <li><strong>Urbaines & créatives</strong> : escape game, VR, ateliers cuisine, artisanat, bien-être.</li>
              <li><strong>Famille & amis</strong> : parcs, zoos, balades en calèche, excursions nature.</li>
            </ul>
          </div>
        </div>

        <p class="mt-8 text-base">
          Où que vous soyez – <strong>Tunis, Sousse, Djerba, Hammamet, Bizerte ou Tozeur</strong> – il y a toujours une activité à tester !
        </p>

        <p class="mt-4 font-semibold" style="color: #a604ab;">
          Des expériences fun, sportives ou relaxantes à vivre en solo ou en groupe. Alors, on s’y met ? 🌞
        </p>
      </div>
    </section>



    <hr class="trait-separateur">

    <section class="recherches-populaires">
      <h3>Les recherches les plus populaires</h3>
      <p class="sous-titre">Recherches associées</p>
      <ul>
        <li><a href="#">Activités à Tunis</a></li>
        <li><a href="#">50 activités géniales à faire à Sousse</a></li>
        <li><a href="#">Activités à Djerba</a></li>
        <li><a href="#">30 activités géniales à faire à Hammamet</a></li>
        <li><a href="#">Activités en pleine nature à Bizerte</a></li>
      </ul>
    </section>




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
          <a href="/Projet%20Web/mvcact/view/front%20office/about.php">À propos</a>
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


          <a href="/Projet%20Web/mvcact/view/front%20office/presse.php">Presse</a>
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

          <a href="/Projet%20Web/mvcact/view/front%20office/nous-rejoindre.php">Nous rejoindre</a>
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
          <a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]">Devenir partenaire</a>

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

          <a href="/Projet Web/mvcact/view/front office/faq.php" class="hover:text-[#be3cf0]">FAQ</a>

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

          <a href="/Projet Web/mvcact/view/front office/avis.php" class="hover:text-[#be3cf0]">Avis</a>
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
            <a href="/Projet Web/mvcact/view/front office/conditions-generales.php" class="hover:text-[#be3cf0]" id="conditionsLink">Conditions générales</a>

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

            <a href="/Projet Web/mvcact/view/front office/devenir-partenaire.php" class="hover:text-[#be3cf0]" id="partnerLink">Devenir partenaire</a>

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

</body>

</html>