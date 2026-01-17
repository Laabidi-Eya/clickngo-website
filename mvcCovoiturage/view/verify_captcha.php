<?php
header('Content-Type: application/json');

$secretKey = '6Lf2fSopAAAAAO5hK9jX8Y7Z1Q2W3E4R5T6Y7U8I';
$captchaResponse = $_POST['g-recaptcha-response'];

if (empty($captchaResponse)) {
    echo json_encode(['success' => false, 'error' => 'CAPTCHA non complété']);
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $secretKey,
    'response' => $captchaResponse
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($verifyUrl, false, $context);
$response = json_decode($result);

if ($response->success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Vérification CAPTCHA échouée']);
}
?>