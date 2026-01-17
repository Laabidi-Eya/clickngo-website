<?php
require_once(__DIR__ . "/../../controller/controller.php");
require_once(__DIR__ . "/../../model/model.php");
require_once(__DIR__ . "../../../../../mvcEvent/Config.php");

if (isset($_GET['id'])) {
    $id_sponsor = (int)$_GET['id'];
    $controller = new sponsorController();
    $success = $controller->deleteSponsor($id_sponsor);
    
    if ($success) {
        header("Location: index.php?delete_success=1");
    } else {
        header("Location: index.php?delete_error=1");
    }
    exit();
} else {
    header("Location: index.php?error=missing_id");
    exit();
}
?>