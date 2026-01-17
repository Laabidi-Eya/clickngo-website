<?php
session_start();
require 'vendor/autoload.php';

$pdo = new PDO("mysql:host=localhost;dbname=click'n'go;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

\Stripe\Stripe::setApiKey('sk_test_51RWP0vC1UtVHyc1Xdptio40hObkMap33k947ojEFDJLHankVCZT01cJ9IrPeKAHYpJlyIgm33RR4ydFl5rIqnTGT00n3juW2bw');

function dtToUsdCents($dt) {
    return round(($dt / 3) * 100);
}

if (isset($_GET['reset'])) {
    unset($_SESSION['clickbox_surprise']);
    header('Location: clickbox.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['price'])) {
    $priceDT = intval($_POST['price']);
    $usdCents = dtToUsdCents($priceDT);

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => "ClickBox Mystère ({$priceDT} DT)"],
                'unit_amount' => $usdCents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost/Projet%20Web/clickbox.php?success=true&amount=' . $priceDT,
        'cancel_url' => 'http://localhost/Projet%20Web/clickbox.php?cancel=true',
    ]);

    header('Location: ' . $session->url);
    exit;
}

function generateTounsiPhraseGroq($prompt) {
    $apiKey = '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';

    $data = [
        'messages' => [
            ['role' => 'system', 'content' => 'انت شاعر تونسي تكتب باللهجة التونسية. الجمل تكون قصيرة، شاعرية، ومفرحة.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'model' => 'mixtral-8x7b-32768',
        'temperature' => 0.8
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 10
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents('https://api.groq.com/openai/v1/chat/completions', false, $context);

    if ($result === FALSE) {
        $fallbacks = [
            "الدنيا تضحك كي تضحك انت.",
            "شمس تونس تشرق كي تشرق الضحكة.",
            "ما أحلى الحياة في قلب الحومة.",
            "قهوتي بنينة كي عينيك الصافية.",
            "ربي يفرّحك قد ما فرّحت غيرك.",
            "اللمة الحلوة تعمّر القلب فرحة.",
            "تونس ديما في القلب، وبالضحكة تزيد تنور.",
            "النسمة اللي تعدي من الحومة تعطر نهاري.",
            "نهار جديد، ضحكة جديدة، وقلب فرحان.",
        ];
        return $fallbacks[array_rand($fallbacks)];
    }

    $response = json_decode($result, true);
    return trim($response['choices'][0]['message']['content'] ?? "الدنيا تضحك كي تضحك انت.");
}

if (!isset($_SESSION['clickbox_surprise']) && isset($_GET['success'], $_GET['amount']) && $_GET['success'] === 'true') {
    $priceDT = intval($_GET['amount']);

    $stmt = $pdo->query("SELECT name, image FROM activities");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $randomActivity = $activities[array_rand($activities)];

    $stmt = $pdo->query("SELECT name, photo FROM products WHERE stock > 0");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $randomProduct = $products[array_rand($products)];

    $promos = ["CLICK5", "FUN10", "BOX2025", "MYSTERY20"];
    $prompt = "اكتبلي جملة باللهجة التونسية على الضحكة";

    $surprise = [
        'activity' => $randomActivity['name'],
        'activity_image' => $randomActivity['image'],
        'promo' => $promos[array_rand($promos)],
    ];

    if ($priceDT >= 250) {
        $surprise['product'] = $randomProduct['name'];
        $surprise['product_image'] = 'http://localhost/Projet%20Web/mvcProduit/' . $randomProduct['photo'];
        $surprise['poetry'] = generateTounsiPhraseGroq($prompt);
    }

    $_SESSION['clickbox_surprise'] = $surprise;
    header('Location: clickbox.php');
    exit;
}

$surprise = $_SESSION['clickbox_surprise'] ?? null;
?>
<?php
function imageToBase64($url) {
    $imageData = @file_get_contents($url);
    if ($imageData === false) return null;
    $base64 = base64_encode($imageData);
    $info = getimagesizefromstring($imageData);
    return 'data:' . $info['mime'] . ';base64,' . $base64;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>ClickBox Mystère</title>
  <style>
    body {
      font-family: Arial;
      background: linear-gradient(to right, #ff91e1, #a084ff);
      color: #333;
      text-align: center;
      padding: 50px;
    }
    .clickbox-card {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      max-width: 400px;
      margin: auto;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    select, button {
      margin: 15px;
      padding: 10px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
    }
    button {
      background-color: #ff77cc;
      color: white;
      cursor: pointer;
    }
    button:hover {
      background-color: #e066b5;
    }
    #result-box {
      margin-top: 30px;
      background: #f8f8ff;
      padding: 20px;
      border-radius: 12px;
    }
    blockquote {
      font-style: italic;
      color: #7a00aa;
    }
    .box-animation {
      width: 100px;
      height: 100px;
      margin: 20px auto;
      background: url('box-open.png') no-repeat center center;
      background-size: contain;
    }
    img {
      max-width: 100%;
      border-radius: 12px;
      margin-top: 10px;
    }
    .instruction {
      color: #444;
      font-size: 14px;
      margin-top: 10px;
    }
    .share-buttons a {
      display: inline-block;
      margin: 10px;
      padding: 10px 14px;
      background: #a084ff;
      color: white;
      border-radius: 8px;
      text-decoration: none;
      transition: background 0.3s;
    }
    .share-buttons a:hover {
      background: #7a5cff;
    }

    button {
  background-color: #ff77cc;
  color: white;
  cursor: pointer;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 16px;
  margin-top: 10px;
  transition: background 0.3s;
}

button:hover {
  background-color: #e066b5;
}

  </style>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
</head>
<body>

<form method="get" action="http://localhost/Projet Web/mvcProduit/view/front office/produit.php">
  <button type="submit">⬅️ Retour</button>
</form>


  <div class="clickbox-card">
    <h2>🎁 ClickBox Mystère</h2>
    <p>Bienvenue<?= isset($_SESSION['user']['full_name']) ? ', ' . htmlspecialchars($_SESSION['user']['full_name']) : '' ?> !</p>
    <p id="clickbox-choice-text">Choisis ta ClickBox :</p>
    <form method="POST"  id="purchase-form">
      <select name="price" required >
        <option value="150">ClickBox 150 DT (activité + promo)</option>
        <option value="250">ClickBox 250 DT (activité + produit + poésie + promo)</option>
      </select><br>
      <button type="submit">Acheter maintenant</button>
    </form>

    <script>
  <?php if ($surprise): ?>
    // Masquer le formulaire d'achat
    document.getElementById('purchase-form').style.display = 'none';
  <?php endif; ?>
</script>
<script>
  <?php if ($surprise): ?>
    // Cacher le texte de choix et le formulaire
    const choiceText = document.getElementById('clickbox-choice-text');
    const purchaseForm = document.getElementById('purchase-form');
    if (choiceText) choiceText.style.display = 'none';
    if (purchaseForm) purchaseForm.style.display = 'none';

    // Remonter le bloc principal
    const card = document.getElementById('clickbox-container');
    if (card) card.style.marginTop = '0';
  <?php endif; ?>
</script>


    <?php if ($surprise): ?>
      <div class="box-animation"></div>
      <div id="result-box">
        <h3>🎉 Ta ClickBox contient :</h3>
        <p>🎯 Activité surprise : <?= htmlspecialchars($surprise['activity']) ?></p>
<?php
if (!empty($surprise['activity_image'])) {
    $base64Act = imageToBase64($surprise['activity_image']);
    if ($base64Act) {
        echo '<img src="' . $base64Act . '" alt="Image activité">';
    } else {
        echo '<p>(Image activité non disponible)</p>';
    }
}
?>

        <?php if (isset($surprise['product'])): ?>
          <p>🛍️ Produit artisanal : <?= htmlspecialchars($surprise['product']) ?></p>
          <?php if (!empty($surprise['product_image'])): ?>
            <img src="<?= htmlspecialchars($surprise['product_image']) ?>" alt="Image produit">
          <?php endif; ?>
        <?php endif; ?>
        <p>🔑 Code promo : <?= htmlspecialchars($surprise['promo']) ?></p>
        <?php if (!empty($surprise['poetry'])): ?>
          <blockquote>📜 <?= htmlspecialchars($surprise['poetry']) ?></blockquote>
        <?php endif; ?>
        <p class="instruction">
          🧭 Rends-toi directement à l’activité qui t’a été attribuée.<br>
          💸 Le paiement se fait <strong>sur place</strong>.<br>
          📸 Montre simplement cette image comme preuve de ta ClickBox !
        </p>
        <form method="get">
          <button type="submit" name="reset" value="1">🔁 Nouvelle ClickBox</button>
        </form>
        <div class="share-buttons">
          <a href="https://www.facebook.com/sharer/sharer.php?u=http://localhost/Projet%20Web/clickbox.php" target="_blank">📢 Facebook</a>
          <a href="https://wa.me/?text=J'ai%20reçu%20une%20ClickBox%20Mystère%20🎁%20sur%20Click'n'Go%20!%20http://localhost/Projet%20Web/clickbox.php" target="_blank">📲 WhatsApp</a>
          <a href="https://www.instagram.com/" target="_blank">📷 Instagram</a>
        </div>
      </div>
      <script>
        window.addEventListener('load', () => {
          html2canvas(document.getElementById('result-box')).then(canvas => {
            const link = document.createElement('a');
            link.download = 'ma-clickbox.png';
            link.href = canvas.toDataURL();
            link.click();
          });
        });
      </script>
    <?php endif; ?>
  </div>
</body>
</html>
