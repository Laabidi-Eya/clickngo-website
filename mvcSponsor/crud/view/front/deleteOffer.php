<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "../../../../../mvcEvent/Config.php");

if (isset($_GET['id'])) {
    $id_offer = (int)$_GET['id'];
    $controller = new sponsorController();
    $success = $controller->deleteOffer($id_offer);
    
    if ($success) {
        header("Location: ../back/back.php?delete_success=1");
    } else {
        header("Location: ../back/back.php?delete_error=1");
    }
    exit();
} else {
    header("Location: ../back/back.php?error=missing_id");
    exit();
}
?>
