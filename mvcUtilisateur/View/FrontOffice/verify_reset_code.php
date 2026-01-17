<?php
session_start();
$email = $_SESSION['user']['email'] ?? $_SESSION['reset_email'] ?? null;

if (!$email) {
    echo "Email manquant.";
    exit;
}

if (!isset($_POST['entered_code']) || !isset($_SESSION['verification_code'])) {
    echo "missing";
    exit;
}

$entered = trim($_POST['entered_code']);
$expected = (string)$_SESSION['verification_code'];

if ($entered === $expected) {
    echo "valid";
} else {
    echo "invalid";
}
