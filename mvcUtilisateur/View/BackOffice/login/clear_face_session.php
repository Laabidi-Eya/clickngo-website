<?php
session_start();
// Nettoyer les variables de session liées à l'enregistrement facial
unset($_SESSION['register_face']);
unset($_SESSION['new_user_id']);
unset($_SESSION['show_face_register']);
header("Location: /View/BackOffice/login/login.php");
exit;
?>