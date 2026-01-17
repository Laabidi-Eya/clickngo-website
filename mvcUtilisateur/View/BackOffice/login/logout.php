<?php
session_start();
unset($_SESSION['user']);
session_destroy();
header("Location: ../../FrontOffice/index.php");
exit;
?>