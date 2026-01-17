<?php
     header('Content-Type: application/json');
     $data = json_decode(file_get_contents('php://input'), true);
     $user_id = $data['user_id'] ?? 0;
     $event_id = $data['event_id'] ?? 0;

     if (!is_numeric($user_id) || $user_id <= 0 || !is_numeric($event_id) || $event_id <= 0) {
         http_response_code(400);
         echo json_encode(['success' => false, 'error' => 'Invalid user_id or event_id']);
         exit;
     }

     $output = shell_exec("python track_click.py $user_id $event_id");
     $result = json_decode($output, true);
     echo json_encode($result);
     ?>