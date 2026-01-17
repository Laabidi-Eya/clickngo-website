<?php
header('Content-Type: text/plain');
$faq = json_decode(file_get_contents('faq.json'), true);
$question = $_GET['question'];

// Cherche la réponse dans la FAQ
foreach ($faq as $item) {
  if (strtolower($item['question']) == strtolower($question)) {
    echo $item['reponse'];
    exit;
  }
}
echo "Je n'ai pas compris. Essayez une autre question.";
?>