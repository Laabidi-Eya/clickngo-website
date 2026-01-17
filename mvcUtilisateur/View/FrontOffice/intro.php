<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Intro avec Animation</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
      font-family: sans-serif;
    }

    video {
      position: fixed;
      top: 0; left: 0;
      width: 100vw;
      height: 100vh;
      object-fit: cover;
      z-index: 0;
      filter: brightness(0.5); /* foncer un peu */
    }

    #startScreen {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1;
    }

    #enterBtn {
      font-size: 4rem;
      color: white;
      padding: 30px 70px;
      border: 3px solid white;
      border-radius: 15px;
      cursor: pointer;
      animation: fadeZoom 2s ease-in-out infinite alternate;
      backdrop-filter: blur(5px);
    }

    @keyframes fadeZoom {
      from {
        transform: scale(1);
        opacity: 0.6;
      }
      to {
        transform: scale(1.1);
        opacity: 1;
      }
    }

    #skip {
      display: none;
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255, 255, 255, 0.8);
      padding: 10px 20px;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
      z-index: 2;
    }
  </style>
</head>
<body>

  <!-- Vidéo déjà visible -->
  <video id="introVideo" muted playsinline>
    <source src="video/intro.mp4" type="video/mp4">
    Votre navigateur ne supporte pas la vidéo.
  </video>

  <!-- Écran d'accueil "ENTRER" -->
  <div id="startScreen">
    <div id="enterBtn">ENTRER</div>
  </div>

  <!-- Bouton Skip -->
  <div id="skip" onclick="window.location.href='index.php'">⏩ Passer</div>

  <script>
    const video = document.getElementById('introVideo');
    const startScreen = document.getElementById('startScreen');
    const enterBtn = document.getElementById('enterBtn');
    const skipBtn = document.getElementById('skip');

    // On met la vidéo en pause au chargement
    window.onload = () => {
      video.pause();
    };

    enterBtn.addEventListener('click', () => {
      startScreen.style.display = "none";
      skipBtn.style.display = "block";
      video.muted = true;
      video.play();
    });

    video.onended = () => {
      window.location.href = "index.php";
    };
  </script>

</body>
</html>
