<?php
// Configurer le fuseau horaire pour Tunis/Tunisie
date_default_timezone_set('Africa/Tunis');

// Charger les produits depuis le contrôleur
require_once '../../Controller/produitcontroller.php';

$controller = new ProductController();
$allProducts = $controller->getAllProducts(); // Charger tous les produits

// Récupérer les coordonnées GPS depuis l'URL si disponibles
$userLat = isset($_GET['lat']) ? round(floatval($_GET['lat']), 3) : null; // Arrondir à 3 décimales pour plus de stabilité
$userLon = isset($_GET['lon']) ? round(floatval($_GET['lon']), 3) : null; // Arrondir à 3 décimales pour plus de stabilité

// Définir les catégories et mots-clés par conditions météo
$weatherKeywords = [
    'sunny' => [
        'icon' => 'fa-sun',
        'title' => 'Idéal pour journées ensoleillées',
        'description' => 'Des produits parfaits pour profiter du beau temps et du soleil !',
        'keywords' => ['extérieur', 'plage', 'protection', 'soleil', 'lunettes', 'chapeau', 'crème', 'sport'],
        'categories' => ['Équipements Sportifs', 'Accessoires de Voyage & Mobilité', 'Vêtements et Accessoires']
    ],
    'rainy' => [
        'icon' => 'fa-cloud-rain',
        'title' => 'Pour les jours de pluie',
        'description' => 'Restez au sec avec notre sélection de produits pour temps pluvieux !',
        'keywords' => ['intérieur', 'imperméable', 'parapluie', 'protection', 'pluie'],
        'categories' => ['Vêtements et Accessoires', 'Accessoires de Voyage & Mobilité']
    ],
    'cold' => [
        'icon' => 'fa-snowflake',
        'title' => 'Pour se réchauffer quand il fait froid',
        'description' => 'Des produits qui vous garderont au chaud pendant les journées froides !',
        'keywords' => ['chaud', 'hiver', 'intérieur', 'froid', 'couverture', 'gants'],
        'categories' => ['Articles de Bien-être & Récupération', 'Vêtements et Accessoires']
    ],
    'hot' => [
        'icon' => 'fa-temperature-high',
        'title' => 'Pour rester frais quand il fait chaud',
        'description' => 'Des produits pour vous rafraîchir pendant les journées de forte chaleur !',
        'keywords' => ['rafraîchissant', 'été', 'ventilateur', 'eau', 'hydratation'],
        'categories' => ['Nutrition & Hydratation', 'Équipements Sportifs']
    ]
];

// Obtenir la condition météo depuis l'URL ou définir la valeur par défaut
$currentWeather = isset($_GET['weather']) ? $_GET['weather'] : 'sunny';
if (!array_key_exists($currentWeather, $weatherKeywords)) {
    $currentWeather = 'sunny';
}

// Filtrer les produits selon la météo
function getWeatherProducts($products, $weather, $weatherKeywords) {
    $filtered = [];
    $count = 0;
    
    if (isset($products['products'])) {
        foreach ($products['products'] as $product) {
            // Vérifier si la catégorie est pertinente pour la météo
            $categoryMatch = in_array($product['category'], $weatherKeywords[$weather]['categories']);
            
            // Vérifier si le nom du produit contient un mot-clé pertinent
            $nameMatch = false;
            foreach ($weatherKeywords[$weather]['keywords'] as $keyword) {
                if (stripos($product['name'], $keyword) !== false) {
                    $nameMatch = true;
                    break;
                }
            }
            
            // Le produit est pertinent s'il correspond à la catégorie ou au mot-clé
            if ($categoryMatch || $nameMatch) {
                $filtered[] = $product;
                $count++;
                
                // Limiter à 6 produits maximum
                if ($count >= 8) {
                    break;
                }
            }
        }
    }
    
    return $filtered;
}

// Obtenir les produits selon la météo
$weatherProducts = getWeatherProducts($allProducts, $currentWeather, $weatherKeywords);

// Simuler les données météo actuelles
function getCurrentWeatherData($lat = null, $lon = null) {
    // Utiliser l'API OpenWeatherMap pour obtenir les données météo réelles
    $apiKey = 'bd5e378503939ddaee76f12ad7a97608'; // Clé API publique fonctionnelle
    
    // Si lat et lon ne sont pas fournis, utiliser les coordonnées par défaut d'ENNASR
    if ($lat === null || $lon === null) {
        // Coordonnées GPS précises d'ENNASR (Tunis)
        $lat = 36.8489; 
        $lon = 10.1693;
    }
    
    // Construire l'URL avec coordonnées pour une plus grande précision
    $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&lang=fr&appid={$apiKey}";
    
    // Essayer d'obtenir les données météo réelles
    $weather_data = @file_get_contents($url);
    
    if ($weather_data) {
        $weather_json = json_decode($weather_data, true);
        
        if ($weather_json && isset($weather_json['main']) && isset($weather_json['weather'][0])) {
            $temp = round($weather_json['main']['temp']);
            $description = $weather_json['weather'][0]['description'];
            $main = $weather_json['weather'][0]['main'];
            $humidity = $weather_json['main']['humidity'];
            $wind_speed = isset($weather_json['wind']['speed']) ? round($weather_json['wind']['speed'] * 3.6) : 0; // Convert to km/h
            
            // Déterminer la condition météo selon les données reçues
            $condition = 'sunny'; // valeur par défaut
            
            if (stripos($main, 'rain') !== false || stripos($main, 'drizzle') !== false) {
                $condition = 'rainy';
            } else if (stripos($main, 'snow') !== false || $temp < 5) {
                $condition = 'cold';
            } else if ($temp > 30 || (stripos($main, 'clear') !== false && $temp > 25)) {
                $condition = 'hot';
            } else if (stripos($main, 'clear') !== false || stripos($main, 'sun') !== false) {
                $condition = 'sunny';
            } else if (stripos($main, 'cloud') !== false && $temp < 15) {
                $condition = 'cold';
            }
            
            return [
                'temp' => $temp,
                'condition' => $condition,
                'description' => ucfirst($description),
                'humidity' => $humidity,
                'wind_speed' => $wind_speed,
                'timestamp' => $weather_json['dt'],
                'city' => $weather_json['name'],
                'icon' => $weather_json['weather'][0]['icon']
            ];
        }
    }
    
    // Log error for debugging
    error_log("Échec de l'obtention des données météo depuis OpenWeatherMap: " . (isset($weather_json['message']) ? $weather_json['message'] : 'Réponse invalide'));
    
    // Fallback sur des données simulées en cas d'échec de l'API
    // C'est important d'avoir un fallback pour éviter que le site ne plante si l'API est indisponible
    $currentMonth = date('n');
    
    if ($currentMonth >= 6 && $currentMonth <= 8) {
        // Été
        return [
            'temp' => rand(25, 35),
            'condition' => rand(0, 10) > 2 ? 'sunny' : 'hot',
            'description' => rand(0, 10) > 2 ? 'Ensoleillé' : 'Très chaud',
            'humidity' => rand(40, 70),
            'wind_speed' => rand(5, 20),
            'timestamp' => time(),
            'city' => 'Tunis',
            'icon' => '01d'
        ];
    } else if ($currentMonth >= 9 && $currentMonth <= 11) {
        // Automne
        return [
            'temp' => rand(10, 20),
            'condition' => rand(0, 10) > 5 ? 'sunny' : 'rainy',
            'description' => rand(0, 10) > 5 ? 'Partiellement nuageux' : 'Pluvieux',
            'humidity' => rand(60, 85),
            'wind_speed' => rand(10, 30),
            'timestamp' => time(),
            'city' => 'Tunis',
            'icon' => rand(0, 10) > 5 ? '02d' : '10d'
        ];
    } else if ($currentMonth >= 3 && $currentMonth <= 5) {
        // Printemps
        return [
            'temp' => rand(15, 25),
            'condition' => rand(0, 10) > 4 ? 'sunny' : 'rainy',
            'description' => rand(0, 10) > 4 ? 'Ensoleillé' : 'Averses',
            'humidity' => rand(50, 75),
            'wind_speed' => rand(8, 25),
            'timestamp' => time(),
            'city' => 'Tunis',
            'icon' => rand(0, 10) > 4 ? '01d' : '09d'
        ];
    } else {
        // Hiver
        return [
            'temp' => rand(-5, 10),
            'condition' => rand(0, 10) > 5 ? 'cold' : 'rainy',
            'description' => rand(0, 10) > 5 ? 'Froid' : 'Pluie froide',
            'humidity' => rand(70, 90),
            'wind_speed' => rand(15, 40),
            'timestamp' => time(),
            'city' => 'Tunis',
            'icon' => rand(0, 10) > 5 ? '13d' : '09d'
        ];
    }
}

// Fonction pour mettre en cache les données météo
function getCachedWeatherData($forceRefresh = false, $lat = null, $lon = null) {
    // Créer une clé de cache unique basée sur les coordonnées arrondies
    $coordKey = ($lat !== null && $lon !== null) ? "_{$lat}_{$lon}" : "";
    $cacheFile = "../../cache/weather_data{$coordKey}.json";
    $cacheExpiry = 30 * 60; // 30 minutes de cache
    
    // Vérifier si le répertoire cache existe, sinon le créer
    if (!file_exists('../../cache')) {
        mkdir('../../cache', 0755, true);
    }
    
    // Tenter de nettoyer les anciens fichiers de cache (plus de 24h)
    $cacheDir = '../../cache/';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . 'weather_data*.json');
        $oneDayAgo = time() - (24 * 60 * 60);
        foreach ($files as $file) {
            if (filemtime($file) < $oneDayAgo) {
                @unlink($file); // Supprimer les fichiers de cache anciens
            }
        }
    }
    
    // Si le cache est valide et qu'on ne force pas le rafraîchissement
    if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheExpiry)) {
        // Utiliser les données en cache
        $cachedData = file_get_contents($cacheFile);
        $decodedData = json_decode($cachedData, true);
        
        // Vérifier si le fichier de cache est valide
        if ($decodedData !== null && isset($decodedData['temp'])) {
            return $decodedData;
        }
    }
    
    // Obtenir de nouvelles données
    $freshData = getCurrentWeatherData($lat, $lon);
    
    // Vérifier que les données sont valides avant de les mettre en cache
    if (isset($freshData['temp'])) {
        // Sauvegarder les nouvelles données dans le cache
        file_put_contents($cacheFile, json_encode($freshData));
    }
    
    return $freshData;
}

// Vérifier si un rafraîchissement est demandé
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === 'true';
$currentWeatherData = getCachedWeatherData($forceRefresh, $userLat, $userLon);

// Fonction pour obtenir le nom de la localité à partir des coordonnées GPS
function getLocationName($lat, $lon) {
    if ($lat === null || $lon === null) {
        return "Ennasr, Tunis"; // Localité par défaut
    }
    
    // Utiliser l'API de géocodage inverse pour obtenir le nom de la localité
    $apiKey = 'bd5e378503939ddaee76f12ad7a97608';
    $url = "https://api.openweathermap.org/geo/1.0/reverse?lat={$lat}&lon={$lon}&limit=1&appid={$apiKey}";
    
    $data = @file_get_contents($url);
    if ($data) {
        $location = json_decode($data, true);
        if (is_array($location) && count($location) > 0) {
            $city = $location[0]['name'];
            $district = isset($location[0]['local_names']['fr']) ? $location[0]['local_names']['fr'] : '';
            $state = isset($location[0]['state']) ? $location[0]['state'] : '';
            
            if ($district) {
                return $district . ", " . $city;
            } else if ($state) {
                return $city . ", " . $state;
            } else {
                return $city;
            }
        }
    }
    
    // Fallback si l'API échoue
    return "Votre position actuelle";
}

// Obtenir le nom de la localisation actuelle
$locationName = getLocationName($userLat, $userLon);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>click'N'go - Suggestions Météo</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .weather-section {
            margin: 60px 0;
            padding: 30px;
            border-radius: 25px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.95));
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        
        .weather-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .weather-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .weather-title {
            font-size: 28px;
            color: #333;
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .weather-title i {
            font-size: 32px;
        }

        .weather-tabs {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .weather-tab {
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-weight: 500;
        }

        .weather-tab.active {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
        }

        .weather-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .weather-description {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: center;
            margin: 0 auto 30px;
        }

        .weather-products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        /* Style différent selon la météo */
        .weather-sunny {
            background: linear-gradient(135deg, rgba(255, 158, 0, 0.08), rgba(255, 255, 255, 0.95));
            border-left: none;
            border-top: 5px solid #ff9e00;
            border-radius: 25px;
        }
        .weather-rainy {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.08), rgba(255, 255, 255, 0.95));
            border-left: none;
            border-top: 5px solid #4cc9f0;
            border-radius: 25px;
        }
        .weather-cold {
            background: linear-gradient(135deg, rgba(160, 196, 255, 0.08), rgba(255, 255, 255, 0.95));
            border-left: none;
            border-top: 5px solid #a0c4ff;
            border-radius: 25px;
        }
        .weather-hot {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.08), rgba(255, 255, 255, 0.95));
            border-left: none;
            border-top: 5px solid #ff6b6b;
            border-radius: 25px;
        }

        .weather-sunny .weather-title i {
            color: #ff9e00;
        }
        .weather-rainy .weather-title i {
            color: #4cc9f0;
        }
        .weather-cold .weather-title i {
            color: #a0c4ff;
        }
        .weather-hot .weather-title i {
            color: #ff6b6b;
        }

        .weather-product {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }

        .weather-product:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .weather-product-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            border-radius: 20px 20px 0 0;
        }

        .weather-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .weather-product:hover .weather-product-image img {
            transform: scale(1.1);
        }

        .weather-product-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .weather-product-name {
            font-size: 18px;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            height: 50px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .weather-product-price {
            font-size: 20px;
            font-weight: 600;
            color: #FF6F91;
            margin-bottom: 15px;
            text-align: center;
        }

        .weather-cart-btn {
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 25px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
            font-weight: 500;
            width: 70%;
            margin: 0 auto;
        }

        .weather-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }

        .no-products {
            text-align: center;
            color: #666;
            font-size: 16px;
            padding: 20px;
        }

        .current-weather-info {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.98));
            border-radius: 25px;
            font-size: 16px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .current-weather-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        }

        .current-weather-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            border-radius: 25px 25px 0 0;
        }

        .weather-main-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .current-weather-info i {
            font-size: 48px;
            color: #FF6F91;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .temperature {
            font-size: 42px;
            font-weight: 700;
            color: #333;
            text-shadow: 1px 1px 0 rgba(0,0,0,0.06);
        }

        .weather-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .weather-location {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            margin: 0;
        }

        .weather-status {
            font-size: 20px;
            font-weight: 500;
            color: #D86AD8;
            margin: 0;
        }

        .weather-metrics {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin: 15px 0;
        }
        
        .metric {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            color: #666;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }
        
        .metric i {
            color: #D86AD8;
            font-size: 18px;
            animation: none;
        }
        
        .weather-icon {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(216, 106, 216, 0.3));
        }

        .weather-suggestion {
            font-size: 16px;
            color: #666;
            margin: 10px 0 0;
            font-style: italic;
        }

        .weather-update-time {
            margin-top: 10px;
            color: #999;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .refresh-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
        }
        
        .refresh-button:hover {
            transform: rotate(180deg);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }
        
        .refresh-button.refreshing i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .refreshed-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            text-align: center;
            background: rgba(76, 175, 80, 0.8);
            color: white;
            padding: 8px;
            font-size: 14px;
            transform: translateY(-100%);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 5;
            border-radius: 25px 25px 0 0;
        }
        
        .refreshed-indicator.show {
            transform: translateY(0);
        }

        .back-button {
            position: fixed;
            top: 30px;
            left: 30px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            font-weight: 500;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }

        @media (max-width: 768px) {
            .weather-products {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .weather-title {
                font-size: 24px;
            }
            .weather-section {
                padding: 20px;
            }
            .weather-header {
                flex-direction: column;
                align-items: center;
            }
            .weather-tabs {
                justify-content: center;
                margin-top: 20px;
            }
        }

        .geo-button {
            margin-top: 15px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(216, 106, 216, 0.3);
            width: 100%;
            max-width: 280px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 500;
        }
        
        .geo-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(216, 106, 216, 0.4);
        }
        
        .geo-button i {
            font-size: 16px;
        }
        
        h1 {
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #FF6F91, #D86AD8);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <a href="produit.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Retour aux produits
    </a>

    <div class="container" style="padding-top: 100px;">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 36px; color: #333;">Suggestions selon la météo</h1>
        <p style="text-align: center; margin-bottom: 30px; font-size: 18px; color: #666;"><?php echo date('d/m/') . '2023'; ?></p>
        
        <?php if (isset($_GET['refresh']) && $_GET['refresh'] === 'true'): ?>
        <div class="refreshed-indicator show">Données météo actualisées !</div>
        <?php endif; ?>
        
        <!-- Informations météo actuelles -->
        <div class="current-weather-info">
            <?php
            $weatherIcons = [
                'sunny' => 'fa-sun',
                'rainy' => 'fa-cloud-rain',
                'cold' => 'fa-snowflake',
                'hot' => 'fa-temperature-high'
            ];
            ?>
            <div class="weather-main-info">
                <?php if(isset($currentWeatherData['icon'])): ?>
                    <img src="https://openweathermap.org/img/wn/<?php echo $currentWeatherData['icon']; ?>@2x.png" alt="Weather icon" class="weather-icon">
                <?php else: ?>
                    <i class="fas <?php echo $weatherIcons[$currentWeatherData['condition']]; ?>"></i>
                <?php endif; ?>
                <div class="temperature"><?php echo $currentWeatherData['temp']; ?>°C</div>
            </div>
            <div class="weather-details">
                <p class="weather-location">Actuellement à <?php echo $locationName; ?></p>
                <p class="weather-status"><?php echo $currentWeatherData['description']; ?></p>
                <?php if(isset($currentWeatherData['humidity']) || isset($currentWeatherData['wind_speed'])): ?>
                <div class="weather-metrics">
                    <?php if(isset($currentWeatherData['humidity'])): ?>
                        <div class="metric">
                            <i class="fas fa-tint"></i>
                            <span><?php echo $currentWeatherData['humidity']; ?>% d'humidité</span>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($currentWeatherData['wind_speed'])): ?>
                        <div class="metric">
                            <i class="fas fa-wind"></i>
                            <span><?php echo $currentWeatherData['wind_speed']; ?> km/h</span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <p class="weather-suggestion">Voici nos suggestions adaptées aux conditions météo actuelles !</p>
            </div>
            <div class="weather-update-time">
                <small>Dernière mise à jour : <?php echo date('d/m/') . '2023 ' . date('H:i'); ?></small>
                <a href="meteo_suggestions.php?refresh=true<?php echo isset($_GET['weather']) ? '&weather=' . htmlspecialchars($_GET['weather']) : ''; ?>" class="refresh-button" title="Actualiser les données météo">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </div>

        <div class="weather-section weather-<?php echo $currentWeather; ?>">
            <div class="weather-header">
                <h2 class="weather-title">
                    <i class="fas <?php echo $weatherKeywords[$currentWeather]['icon']; ?>"></i> 
                    <?php echo $weatherKeywords[$currentWeather]['title']; ?>
                </h2>
                <div class="weather-tabs">
                    <a href="?weather=sunny" class="weather-tab <?php echo $currentWeather == 'sunny' ? 'active' : ''; ?>">
                        <i class="fas fa-sun"></i> Ensoleillé
                    </a>
                    <a href="?weather=rainy" class="weather-tab <?php echo $currentWeather == 'rainy' ? 'active' : ''; ?>">
                        <i class="fas fa-cloud-rain"></i> Pluvieux
                    </a>
                    <a href="?weather=cold" class="weather-tab <?php echo $currentWeather == 'cold' ? 'active' : ''; ?>">
                        <i class="fas fa-snowflake"></i> Froid
                    </a>
                    <a href="?weather=hot" class="weather-tab <?php echo $currentWeather == 'hot' ? 'active' : ''; ?>">
                        <i class="fas fa-temperature-high"></i> Chaleur
                    </a>
                </div>
            </div>
            
            <p class="weather-description">
                <?php echo $weatherKeywords[$currentWeather]['description']; ?>
            </p>
            
            <div class="weather-products">
                <?php if (empty($weatherProducts)): ?>
                    <p class="no-products">Aucun produit disponible pour ces conditions météo.</p>
                <?php else: ?>
                    <?php foreach ($weatherProducts as $product): ?>
                        <div class="weather-product">
                            <div class="weather-product-image">
                                <?php 
                                $imagePath = $product['photo'] && $product['photo'] !== 'images/products/logo.png' 
                                    ? '../../' . $product['photo'] 
                                    : 'images/products/logo.png';
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='images/products/logo.png'">
                            </div>
                            <div class="weather-product-info">
                                <div>
                                    <h3 class="weather-product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <div class="weather-product-price"><?php echo number_format($product['price'], 2); ?> TND</div>
                                </div>
                                <button class="weather-cart-btn" onclick="addToCart('<?php echo $product['id']; ?>', '<?php echo addslashes($product['name']); ?>', '<?php echo $product['price']; ?>', '<?php echo $imagePath; ?>')">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px; color: #999; font-size: 12px;">
            Dernière mise à jour : <?php echo date('d/m/') . '2023 ' . date('H:i'); ?>
        </div>
    </div>

    <script>
        function updateCartCount() {
            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            let totalItems = panier.reduce((sum, item) => sum + item.quantite, 0);
            const cartCountElement = document.getElementById('cartCount');
            if (cartCountElement) {
                cartCountElement.textContent = totalItems;
            }
        }

        // Fonction pour obtenir la position de l'utilisateur
        function getUserLocation() {
            // Vérifier si on a déjà une position récente (moins de 1 heure)
            const savedLocation = localStorage.getItem('userLocation');
            if (savedLocation) {
                const locationData = JSON.parse(savedLocation);
                const oneHour = 60 * 60 * 1000; // 1 heure en millisecondes
                
                // Si la position enregistrée est récente (moins d'une heure), l'utiliser
                if (Date.now() - locationData.timestamp < oneHour) {
                    window.location.href = `meteo_suggestions.php?lat=${locationData.lat}&lon=${locationData.lon}&refresh=true${window.location.search.includes('weather') ? '&' + window.location.search.substring(1).split('&').find(param => param.startsWith('weather')) : ''}`;
                    return;
                }
            }
            
            if (navigator.geolocation) {
                // Afficher une notification de chargement
                const loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'refreshed-indicator show';
                loadingIndicator.textContent = 'Détection de votre position en cours...';
                loadingIndicator.id = 'location-indicator';
                document.body.appendChild(loadingIndicator);
                
                navigator.geolocation.getCurrentPosition(
                    // Succès
                    function(position) {
                        // Arrondir les coordonnées à 3 décimales pour une meilleure stabilité
                        const lat = Math.round(position.coords.latitude * 1000) / 1000;
                        const lon = Math.round(position.coords.longitude * 1000) / 1000;
                        
                        // Sauvegarder la position dans localStorage
                        localStorage.setItem('userLocation', JSON.stringify({
                            lat: lat,
                            lon: lon,
                            timestamp: Date.now()
                        }));
                        
                        // Rediriger vers la page avec les coordonnées GPS
                        window.location.href = `meteo_suggestions.php?lat=${lat}&lon=${lon}&refresh=true${window.location.search.includes('weather') ? '&' + window.location.search.substring(1).split('&').find(param => param.startsWith('weather')) : ''}`;
                    },
                    // Erreur
                    function(error) {
                        console.error("Erreur de géolocalisation:", error);
                        const loadingIndicator = document.getElementById('location-indicator');
                        
                        let errorMessage = "Impossible de détecter votre position. ";
                        
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += "Vous avez refusé l'accès à votre position.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += "Votre position n'est pas disponible actuellement.";
                                break;
                            case error.TIMEOUT:
                                errorMessage += "La demande de géolocalisation a expiré.";
                                break;
                            default:
                                errorMessage += "Une erreur inconnue s'est produite.";
                                break;
                        }
                        
                        if (loadingIndicator) {
                            loadingIndicator.textContent = errorMessage;
                            setTimeout(() => {
                                loadingIndicator.classList.remove('show');
                            }, 5000); // Afficher l'erreur pendant 5 secondes
                        } else {
                            alert(errorMessage);
                        }
                    },
                    // Options
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            } else {
                alert("La géolocalisation n'est pas prise en charge par votre navigateur.");
            }
        }

        async function checkStock(productId, quantity) {
            try {
                const response = await fetch(`../../Controller/produitcontroller.php?action=get_one&id=${productId}`);
                const data = await response.json();
                if (data.success && data.product.stock >= quantity) {
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Erreur lors de la vérification du stock:', error);
                return false;
            }
        }

        async function addToCart(productId, productName, productPrice, productImage) {
            const isAvailable = await checkStock(productId, 1);
            if (!isAvailable) {
                alert('Produit en rupture de stock !');
                return;
            }

            let panier = JSON.parse(localStorage.getItem('panier')) || [];
            const produitExistantIndex = panier.findIndex(p => p.id === productId);
            
            if (produitExistantIndex !== -1) {
                const newQuantity = panier[produitExistantIndex].quantite + 1;
                const canIncrement = await checkStock(productId, newQuantity);
                if (!canIncrement) {
                    alert('Stock insuffisant pour ajouter une unité supplémentaire !');
                    return;
                }
                panier[produitExistantIndex].quantite = newQuantity;
            } else {
                const produit = {
                    id: productId,
                    nom: productName,
                    prix: parseFloat(productPrice),
                    image: productImage,
                    quantite: 1
                };
                panier.push(produit);
            }
            
            localStorage.setItem('panier', JSON.stringify(panier));
            updateCartCount();
            alert(`${productName} a été ajouté au panier !`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Masquer l'indicateur de rafraîchissement après 3 secondes
            const refreshedIndicator = document.querySelector('.refreshed-indicator');
            if (refreshedIndicator && refreshedIndicator.classList.contains('show')) {
                setTimeout(() => {
                    refreshedIndicator.classList.remove('show');
                }, 3000);
            }
            
            // Ajouter l'animation au bouton de rafraîchissement lors du clic
            const refreshButton = document.querySelector('.refresh-button');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    this.classList.add('refreshing');
                });
            }
            
            // Ajouter un bouton de géolocalisation
            const weatherInfo = document.querySelector('.current-weather-info');
            if (weatherInfo) {
                const geoButton = document.createElement('button');
                geoButton.className = 'geo-button';
                
                // Vérifier si on a déjà utilisé la géolocalisation
                const savedLocation = localStorage.getItem('userLocation');
                if (savedLocation) {
                    const locationData = JSON.parse(savedLocation);
                    const oneHour = 60 * 60 * 1000; // 1 heure en millisecondes
                    
                    // Si la position enregistrée est récente (moins d'une heure), modifier le texte du bouton
                    if (Date.now() - locationData.timestamp < oneHour) {
                        const dateFormat = new Date(locationData.timestamp);
                        const heureFormat = dateFormat.getHours().toString().padStart(2, '0') + ':' + 
                                           dateFormat.getMinutes().toString().padStart(2, '0');
                        // Utiliser l'heure actuelle plutôt que celle stockée, pour éviter les problèmes d'année
                        const now = new Date();
                        const currentHour = now.getHours().toString().padStart(2, '0') + ':' + 
                                           now.getMinutes().toString().padStart(2, '0');
                        geoButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Actualiser ma position (dernière: ' + currentHour + ')';
                    } else {
                        geoButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Utiliser ma position actuelle';
                    }
                } else {
                    geoButton.innerHTML = '<i class="fas fa-map-marker-alt"></i> Utiliser ma position actuelle';
                }
                
                geoButton.addEventListener('click', getUserLocation);
                
                const weatherDetails = weatherInfo.querySelector('.weather-details');
                if (weatherDetails) {
                    weatherDetails.appendChild(geoButton);
                }
            }
        });
    </script>
</body>
</html> 