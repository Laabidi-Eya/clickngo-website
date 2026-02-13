<?php
$conn = new mysqli("localhost", "root", "", "clickngo_db");
if ($conn->connect_error) die("Erreur connexion DB");

// Modifiez la requête SQL pour inclure l'ID
$sql = "SELECT id, name, description, price, place_name, image_url FROM evenements ORDER BY created_at DESC LIMIT 50";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tinder Style Swipe - Rose Theme</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    .back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 100;
      background-color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      cursor: pointer;
    }

    .back-button i {
      color: #FF69B4;
      font-size: 20px;
    }


    *,
    *:before,
    *:after {
      box-sizing: border-box;
      padding: 0;
      margin: 0;
    }

    body {
      background: linear-gradient(135deg, #D6B4FC, #E9D8FD);

      overflow: hidden;
      font-family: 'Segoe UI', sans-serif;
    }

    .tinder {
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
      opacity: 0;
      transition: opacity 0.1s ease-in-out;
    }

    .loaded.tinder {
      opacity: 1;
    }

    .tinder--status {
      position: absolute;
      top: 50%;
      margin-top: -30px;
      z-index: 2;
      width: 100%;
      text-align: center;
      pointer-events: none;
    }

    .tinder--status i {
      font-size: 100px;
      opacity: 0;
      transform: scale(0.3);
      transition: all 0.2s ease-in-out;
      position: absolute;
      width: 100px;
      margin-left: -50px;
    }

    .tinder_love .fa-heart {
      opacity: 0.7;
      transform: scale(1);
    }

    .tinder_nope .fa-remove {
      opacity: 0.7;
      transform: scale(1);
    }

    .tinder--cards {
      flex-grow: 1;
      padding-top: 40px;
      text-align: center;
      display: flex;
      justify-content: center;
      align-items: flex-end;
      z-index: 1;
    }

    .tinder--card {
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      position: absolute;
      width: 90vw;
      max-width: 400px;
      height: 70vh;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .moving.tinder--card {
      transition: none;
      cursor: grabbing;
    }

    .tinder--card img {
      max-width: 100%;
      pointer-events: none;
    }

    .card-overlay {
      background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
      color: white;
      width: 100%;
      padding: 20px;
      text-align: left;
    }

    .card-overlay h3 {
      font-size: 24px;
      margin-bottom: 8px;
    }

    .card-overlay p {
      font-size: 16px;
      margin: 4px 0;
      color: #FFFFFF;
    }

    .tinder--buttons {
      flex: 0 0 100px;
      text-align: center;
      padding-top: 20px;
    }

    .tinder--buttons button {
      border-radius: 50%;
      line-height: 60px;
      width: 60px;
      border: 0;
      background: #FFFFFF;
      display: inline-block;
      margin: 0 12px;
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease-in-out;
    }

    .tinder--buttons button:focus {
      outline: 0;
    }

    .tinder--buttons button:hover {
      transform: scale(1.1);
    }

    .fa-heart {
      color: #FF69B4;
    }

    .fa-remove {
      color: #D8B7DD;
    }

    .animate-left {
      animation: swipeLeft 0.5s forwards;
    }

    .animate-right {
      animation: swipeRight 0.5s forwards;
    }

    @keyframes swipeLeft {
      0% {
        transform: translateX(0) rotate(0deg);
      }

      100% {
        transform: translateX(-150%) rotate(-20deg);
        opacity: 0;
      }
    }

    @keyframes swipeRight {
      0% {
        transform: translateX(0) rotate(0deg);
      }

      100% {
        transform: translateX(150%) rotate(20deg);
        opacity: 0;
      }
    }

    .removed {
      display: none;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body>


  <button class="back-button" onclick="window.location.href='http://localhost:8000/mvcEvent/View/FrontOffice/evenemant.php'">
    <i class="fa fa-arrow-left"></i>
  </button>


  <div class="tinder">
    <div class="tinder--status">
      <i class="fa fa-remove"></i>
      <i class="fa fa-heart"></i>
    </div>

    <div class="tinder--cards">
      <?php while ($row = $result->fetch_assoc()): ?>
<div class="tinder--card" data-id="<?= htmlspecialchars($row['id']) ?>" style="background-image: url('<?= htmlspecialchars($row['image_url']) ?>');">

          <div class="card-overlay">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
<p><strong>📍 <?= htmlspecialchars($row['place_name']) ?></strong></p>

            <p><strong>💸 <?= htmlspecialchars($row['price']) ?> TND</strong></p>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="tinder--buttons">
      <button id="nope"><i class="fa fa-remove"></i></button>
      <button id="love"><i class="fa fa-heart"></i></button>
    </div>

    <!-- Bloc de fin -->
    <div id="no-more-cards" style="
  display: none;
  text-align: center;
  padding: 60px 20px;
  animation: fadeIn 0.6s ease-in-out;
">
      <h2 style="color: #fff; font-size: 24px; margin-bottom: 20px;">
        😅 Il n'y a plus d'événement pour le moment
      </h2>
      <a href="suggest.php" style="
    background-color: #FF69B4;
    color: white;
    padding: 14px 28px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 5px 10px rgba(0,0,0,0.3);
    transition: background 0.3s;
  ">
        ➕ Proposer une événement
      </a>
    </div>

  </div>


  <script src="https://hammerjs.github.io/dist/hammer.min.js"></script>
  <script>
    function initCards() {
      var newCards = document.querySelectorAll('.tinder--card:not(.removed)');
      var noMoreDiv = document.getElementById('no-more-cards');

      newCards.forEach(function(card, index) {
        card.style.zIndex = allCards.length - index;
        card.style.transform = 'scale(' + (20 - index) / 20 + ') translateY(-' + 30 * index + 'px)';
        card.style.opacity = (10 - index) / 10;
      });

      tinderContainer.classList.add('loaded');

      // 👉 Affiche le bouton s'il n'y a plus de cartes
      if (newCards.length === 0) {
        noMoreDiv.style.display = 'block';
      } else {
        noMoreDiv.style.display = 'none';
      }
    }




    document.addEventListener('DOMContentLoaded', function() {
      var tinderContainer = document.querySelector('.tinder');
      var allCards = document.querySelectorAll('.tinder--card');
      var nope = document.getElementById('nope');
      var love = document.getElementById('love');

      function initCards() {
        var newCards = document.querySelectorAll('.tinder--card:not(.removed)');

        newCards.forEach(function(card, index) {
          card.style.zIndex = allCards.length - index;
          card.style.transform = 'scale(' + (20 - index) / 20 + ') translateY(-' + 30 * index + 'px)';
          card.style.opacity = (10 - index) / 10;
        });

        tinderContainer.classList.add('loaded');
      }

      initCards();

      function handleChoice(isLike) {
        var cards = document.querySelectorAll('.tinder--card:not(.removed)');
        if (!cards.length) return false;

        var card = cards[0];
        var activityId = card.getAttribute('data-id'); // Récupère l'ID caché

        if (isLike) {
          card.classList.add('animate-right');
          // Redirection vers la page de réservation après l'animation
          setTimeout(function() {
            window.location.href = 'http://localhost:8000/mvcEvent/View/FrontOffice/reservation.php?event_id=' + activityId;
          }, 500);
        } else {
          card.classList.add('animate-left');
        }

        setTimeout(function() {
          card.classList.add('removed');
          card.classList.remove('animate-left', 'animate-right');

          initCards();

          if (document.querySelectorAll('.tinder--card:not(.removed)').length === 0) {
            document.querySelector('.tinder--buttons').style.display = 'none';
            document.getElementById('no-more-cards').style.display = 'block';
          }
        }, isLike ? 1000 : 500);
      }


      allCards.forEach(function(el) {
        var hammertime = new Hammer(el);

        hammertime.on('pan', function(event) {
          el.classList.add('moving');

          // Affiche les icônes like/nope pendant le swipe
          tinderContainer.classList.toggle('tinder_love', event.deltaX > 50);
          tinderContainer.classList.toggle('tinder_nope', event.deltaX < -50);

          var xMulti = event.deltaX * 0.03;
          var yMulti = event.deltaY / 80;
          var rotate = xMulti * yMulti;

          event.target.style.transform = 'translate(' + event.deltaX + 'px, ' + event.deltaY + 'px) rotate(' + rotate + 'deg)';
        });

        hammertime.on('panend', function(event) {
          el.classList.remove('moving');
          tinderContainer.classList.remove('tinder_love');
          tinderContainer.classList.remove('tinder_nope');

          var moveOutWidth = document.body.clientWidth;
          var direction = event.deltaX >= 0 ? 1 : -1;

          // Force le mouvement même si swipe zghir
          var isLike = direction === 1;
          handleChoice(isLike);
        });

      });

      nope.addEventListener('click', function() {
        handleChoice(false);
      });
      love.addEventListener('click', function() {
        handleChoice(true);
      });
    });
  </script>
</body>

</html>