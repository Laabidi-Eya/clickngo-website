<?php
echo '<pre>';
echo 'Fichier actuel : ' . __FILE__ . "\n";
echo 'DIR actuel : ' . __DIR__ . "\n";
echo 'Test config path: ' . realpath(__DIR__ . '/../../config.php');
echo '</pre>';
