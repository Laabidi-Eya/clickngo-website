<?php
// Start Flask app in the background
$python_path = 'C:\\Users\\msi\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';
$script_path = 'C:\\xampp\\htdocs\\clickngo_api\\chatbot_api.py';
$command = "$python_path $script_path > NUL 2>&1";
exec("$command &");
?>