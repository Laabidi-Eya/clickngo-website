<?php
session_start();
require_once '../Model/ImageGenerator.php';

header('Content-Type: application/json');

// Get locations from POST request
$input = file_get_contents('php://input');
$locations = json_decode($input, true);

if (!is_array($locations)) {
    echo json_encode([]);
    exit;
}

// Initialize ImageGenerator
$usedIndices = [];
$imageGenerator = new ImageGenerator($usedIndices);

// Generate new images for each location
$result = [];
foreach ($locations as $location) {
    $result[$location] = $imageGenerator->getImageForLocation($location, $usedIndices);
}

// Update session
$_SESSION['used_indices'] = $usedIndices;

echo json_encode($result);
?>