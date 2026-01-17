<?php
session_start();
require_once __DIR__ . '/config.php';

$pdo = config::getConnexion();
if (!$pdo) {
    die("Erreur connexion base");
}

// Vérification connexion utilisateur
if (!isset($_SESSION['user']['id_user'])) {
    die("❌ Connecte-toi d'abord pour accéder à la roulette.");
}

$user_id = $_SESSION['user']['id_user'];
$is_verified = $_SESSION['user']['is_verified'] ?? 0;

if ((int)$is_verified !== 1) {
    die("⚠️ Tu dois vérifier ton compte pour jouer à la roulette.");
}

// PRIZES array must be accessible côté JS et PHP, on le définit ici
$prizes = ["🎁 10% réduction", "😢 Rien", "🎉 1 point", "🔥 Accès VIP", "💥 Deuxième chance"];

// API roulette
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'spin') {
    header('Content-Type: application/json');

    if (isset($_SESSION['roulette_spun']) && $_SESSION['roulette_spun'] === true) {
        echo json_encode(['error' => 'Tu as déjà tourné la roulette une fois.']);
        exit;
    }

    // Tirage au hasard côté serveur
    $gain_index = array_rand($prizes);
    $gain = $prizes[$gain_index];

    $_SESSION['roulette_spun'] = true;

    echo json_encode([
        'gain' => $gain,
        'index' => $gain_index // on retourne l'index pour JS pour l'animation
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Roulette une fois</title>
<style>
  body {
    font-family: Arial, sans-serif;
    text-align: center;
    padding: 30px;
  }

  #roulette {
    width: 300px;
    height: 300px;
    border-radius: 50%;
    border: 8px solid #444;
    margin: 30px auto;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 15px #888;
    transition: transform 4s cubic-bezier(0.33, 1, 0.68, 1);
  }

  /* Les segments : on va faire un cercle divisé en 5 parties égales (72° chacune) */
  .segment {
    position: absolute;
    width: 50%;
    height: 50%;
    background: #f1c40f;
    border: 1px solid #fff;
    transform-origin: 100% 100%;
    top: 50%;
    left: 50%;
    font-weight: bold;
    color: #333;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding-left: 10px;
    box-sizing: border-box;
  }

  /* couleurs alternées */
  .segment:nth-child(odd) {
    background: #f39c12;
  }
  .segment:nth-child(even) {
    background: #f1c40f;
  }

  /* Rotation de chaque segment */
  .segment:nth-child(1) { transform: rotate(0deg)   skewY(-54deg); }
  .segment:nth-child(2) { transform: rotate(72deg)  skewY(-54deg); }
  .segment:nth-child(3) { transform: rotate(144deg) skewY(-54deg); }
  .segment:nth-child(4) { transform: rotate(216deg) skewY(-54deg); }
  .segment:nth-child(5) { transform: rotate(288deg) skewY(-54deg); }

  /* Texte dans chaque segment, on inverse la skew */
  .segment span {
    transform: skewY(54deg);
  }

  /* Flèche indicatrice */
  #pointer {
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-bottom: 30px solid #e74c3c;
    margin: 0 auto;
  }

  #spinBtn {
    font-size: 18px;
    padding: 12px 24px;
    cursor: pointer;
  }

  #result {
    margin-top: 20px;
    font-size: 22px;
    font-weight: bold;
    color: green;
    min-height: 30px;
  }
</style>
</head>
<body>

<h1>Bienvenue <?= htmlspecialchars($_SESSION['user']['full_name']) ?> !</h1>

<div id="pointer"></div>
<div id="roulette">
  <?php foreach ($prizes as $i => $prize): ?>
    <div class="segment"><span><?= htmlspecialchars($prize) ?></span></div>
  <?php endforeach; ?>
</div>

<button id="spinBtn">Tourner la roulette (1 fois seulement)</button>
<p id="result"></p>

<script>
const spinBtn = document.getElementById('spinBtn');
const resultEl = document.getElementById('result');
const roulette = document.getElementById('roulette');

const totalSegments = <?= count($prizes) ?>;
const segmentAngle = 360 / totalSegments;
let spinning = false;

spinBtn.addEventListener('click', () => {
  if (spinning) return;

  spinBtn.disabled = true;
  resultEl.textContent = "Roulette en cours...";
  spinning = true;

  fetch('', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=spin'
  })
  .then(res => res.json())
  .then(data => {
    if (data.error) {
      resultEl.textContent = data.error;
      spinBtn.disabled = false;
      spinning = false;
      return;
    }

    // Calcul rotation pour que le segment gagné soit en haut (flèche)
    // Chaque segment fait segmentAngle degrés
    // On veut tourner plusieurs tours + s’arrêter sur le segment gagnant
    // Ajout d’une rotation aléatoire pour variation visuelle
    const rotations = 5; // nombre de tours complets
    const stopAngle = 360 - (data.index * segmentAngle) - (segmentAngle / 2);

    const totalRotation = 360 * rotations + stopAngle;

    // Lancer l'animation CSS
    roulette.style.transition = 'transform 4s cubic-bezier(0.33, 1, 0.68, 1)';
    roulette.style.transform = `rotate(${totalRotation}deg)`;

    // Après l'animation, afficher le résultat et bloquer le bouton
    setTimeout(() => {
      resultEl.textContent = "🎉 Tu as gagné : " + data.gain;
      spinning = false;
    }, 4000);
  })
  .catch(() => {
    resultEl.textContent = "Erreur lors de la requête.";
    spinBtn.disabled = false;
    spinning = false;
  });
});
</script>

</body>
</html>
