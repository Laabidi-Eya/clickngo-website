<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gRecaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA
    $secretKey = 'YOUR_SECRET_KEY'; // Replace with your reCAPTCHA secret key
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$gRecaptchaResponse}&remoteip=" . $_SERVER['REMOTE_ADDR'];

    $response = file_get_contents($verifyUrl);
    $data = json_decode($response);

    if ($data->success) {
        // reCAPTCHA verified, redirect to test_sms.php
        header("Location: ../view/test_sms.php?phone=" . urlencode($phone));
        exit;
    } else {
        $_SESSION['error'] = 'Échec de la vérification reCAPTCHA. Veuillez réessayer.';
        header("Location: ../view/demandes.php?id=" . urlencode($_GET['id'] ?? ''));
        exit;
    }
}
?>