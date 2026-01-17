<?php
// Enable all error reporting but don't display errors (store in log instead)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON before any output
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // 1. Verify POST method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Only POST requests allowed");
    }

    // 2. Include files with exact case-sensitive paths
    require_once realpath(__DIR__.'/../../config.php');
    require_once realpath(__DIR__.'/../../Model/Avis.php');
    require_once realpath(__DIR__.'/../../Controller/AvisController.php'); // Corrected case

    // 3. Validate all required fields
    $required = [
        'id_passager' => 'Passenger ID',
        'id_conducteur' => 'Driver ID', 
        'note' => 'Rating',
        'commentaire' => 'Comment',
        'titre' => 'Title',
        'auteur' => 'Author'
    ];

    $data = [];
    foreach ($required as $field => $name) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing $name");
        }
        
        // Special validation for rating
        if ($field === 'note') {
            $note = (float)$_POST[$field];
            if ($note < 1 || $note > 5) {
                throw new Exception("Rating must be 1-5");
            }
            $data[$field] = $note;
        } else {
            $data[$field] = trim($_POST[$field]);
        }
    }

    // 4. Process with controller
    $controller = new AvisController(); // Corrected case
    $controller->createAvis(
        $data['id_passager'],
        $data['id_conducteur'],
        $data['note'],
        $data['commentaire'],
        $data['titre'],
        $data['auteur']
    );

    $response = [
        'success' => true,
        'message' => 'Review submitted successfully!'
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Make sure there's no whitespace or other output before or after the JSON
echo json_encode($response);
exit;
