<?php
if (isset($_GET['spin'])) {
    header('Content-Type: application/json');
    $items = [
        ["type"=>"Musée","nom"=>"Entrée gratuite 🎟️","image"=>"https://via.placeholder.com/150?text=Musee"],
        ["type"=>"Glace","nom"=>"Glace offerte 🍦","image"=>"https://via.placeholder.com/150?text=Glace"],
        ["type"=>"Playlist","nom"=>"Musique tunisienne 🎧","image"=>"https://via.placeholder.com/150?text=Playlist"],
        ["type"=>"Bon d'achat","nom"=>"Bon 5 DT 🎁","image"=>"https://via.placeholder.com/150?text=5DT"],
        ["type"=>"Boisson","nom"=>"Café ou thé ☕","image"=>"https://via.placeholder.com/150?text=Cafe"],
        ["type"=>"Plante","nom"=>"Menthe 🌿","image"=>"https://via.placeholder.com/150?text=Menthe"],
        ["type"=>"Citation","nom"=>"Sagesse tunisienne ✨","image"=>"https://via.placeholder.com/150?text=Citation"],
        ["type"=>"Promo","nom"=>"-10 % 🎫","image"=>"https://via.placeholder.com/150?text=Promo"]
    ];
    echo json_encode($items[array_rand($items)]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>🎡 Roue ClickNgo Complète</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #ffe3ec, #f3e5f5);
      text-align: center;
      padding: 40px;
    }
    canvas {
      margin-top: 20px;
      border-radius: 50%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    #spinBtn {
      margin: 25px;
      padding: 14px 28px;
      font-size: 18px;
      background: linear-gradient(135deg, #e85ed2, #d63384);
      color: white;
      border: none;
      border-radius: 40px;
      cursor: pointer;
    }
    #spinBtn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    #result {
      margin-top: 30px;
      padding: 20px;
      max-width: 400px;
      margin-inline: auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    #result img {
      max-width: 200px;
      border-radius: 10px;
      margin-top: 15px;
    }
    .arrow {
      width: 0;
      height: 0;
      margin: 0 auto;
      border-left: 20px solid transparent;
      border-right: 20px solid transparent;
      border-bottom: 40px solid #d63384;
    }
  </style>
</head>
<body>

  <h1>🎡 ClickNgo - Tourne la roue !</h1>
  <div class="arrow"></div>
  <canvas id="wheel" width="400" height="400"></canvas><br>
  <button id="spinBtn">Lancer la roue</button>
  <div id="result"></div>

  <script>
    const canvas = document.getElementById("wheel");
    const ctx = canvas.getContext("2d");
    const spinBtn = document.getElementById("spinBtn");
    const result = document.getElementById("result");

    const options = [
      "Entrée musée", "Glace offerte", "Playlist",
      "Bon 5 DT", "Thé/Café", "Menthe 🌿",
      "Citation ✨", "Promo -10%"
    ];

    const colors = ['#ffcad4','#bdb2ff','#ffc6ff','#fffffc','#caffbf','#fdffb6','#a0c4ff','#ffc9de'];
    const arcSize = 2 * Math.PI / options.length;
    let angle = 0;
    let isSpinning = false;

    function drawWheel(rotation = 0) {
      ctx.clearRect(0, 0, 400, 400);
      for (let i = 0; i < options.length; i++) {
        const start = rotation + i * arcSize;
        const end = start + arcSize;

        // segment
        ctx.beginPath();
        ctx.moveTo(200, 200);
        ctx.arc(200, 200, 180, start, end);
        ctx.fillStyle = colors[i % colors.length];
        ctx.fill();
        ctx.save();

        // texte
        ctx.translate(200, 200);
        ctx.rotate(start + arcSize / 2);
        ctx.textAlign = "right";
        ctx.fillStyle = "#333";
        ctx.font = "16px 'Segoe UI'";
        ctx.fillText(options[i], 160, 10);
        ctx.restore();
      }
    }

    drawWheel();

    spinBtn.addEventListener("click", () => {
      if (isSpinning) return;
      isSpinning = true;
      spinBtn.disabled = true;
      result.innerHTML = "<p>La roue tourne...</p>";

      const spinAngle = Math.floor(Math.random() * 360 + 360 * 5);
      const targetAngle = spinAngle % 360;
      const targetIndex = Math.floor((360 - targetAngle) / (360 / options.length)) % options.length;

      let start = null;

      function animate(time) {
        if (!start) start = time;
        const elapsed = time - start;
        const duration = 4000;
        const progress = Math.min(elapsed / duration, 1);
        const easeOut = 1 - Math.pow(1 - progress, 4);

        angle = easeOut * spinAngle;
        drawWheel((angle * Math.PI) / 180);

        if (progress < 1) {
          requestAnimationFrame(animate);
        } else {
          fetch(window.location.href + '?spin=1')
            .then(res => res.json())
            .then(data => {
              result.innerHTML = `
                <h3>🎉 ${data.type}</h3>
                <p>${data.nom}</p>
                <img src="${data.image}" alt="${data.nom}">
              `;
            })
            .catch(() => {
              result.innerHTML = "<p>Erreur lors du tirage</p>";
            })
            .finally(() => {
              isSpinning = false;
              spinBtn.disabled = false;
            });
        }
      }

      requestAnimationFrame(animate);
    });
  </script>

</body>
</html>
