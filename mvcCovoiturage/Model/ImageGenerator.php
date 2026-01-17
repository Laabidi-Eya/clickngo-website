<?php
/**
 * Class to generate images for Tunisian travel destinations
 */
class ImageGenerator {
    private $apiKey = '50179716-ab86b94afe69b4b8f33a8ad4d'; // Ensure this is valid
    
    private $locationMapping = [
        'tunis' => 'Tunis Medina',
        'sidi bou said' => 'Sidi Bou Said',
        'carthage' => 'Carthage ruins',
        'la marsa' => 'La Marsa beach',
        'hammamet' => 'Hammamet beach',
        'sousse' => 'Sousse Medina',
        'monastir' => 'Monastir Ribat',
        'djerba' => 'Djerba island',
        'tozeur' => 'Tozeur oasis',
        'douz' => 'Sahara Desert',
        'kairouan' => 'Kairouan mosque',
        'bizerte' => 'Bizerte harbor',
        'mahdia' => 'Mahdia beach',
        'nabeul' => 'Nabeul pottery',
        'el jem' => 'El Jem Amphitheatre',
        'tabarka' => 'Tabarka coast',
        'gafsa' => 'Gafsa oasis',
        'kelibia' => 'Kelibia beach',
        'zaghouan' => 'Zaghouan aqueduct',
        'sfax' => 'Sfax Medina',
    ];
    
    private $fallbackImages = [
        'https://cdn.pixabay.com/photo/2017/08/01/01/33/beach-2561470_1280.jpg', // Sidi Bou Said
        'https://cdn.pixabay.com/photo/2016/11/21/12/42/beach-1845906_1280.jpg', // Hammamet
        'https://cdn.pixabay.com/photo/2017/02/08/12/24/city-2046856_1280.jpg', // Tunis Medina
        'https://cdn.pixabay.com/photo/2016/11/14/03/53/landscape-1822595_1280.jpg', // Carthage
    ];
    
    private $usedIndices = [];
    private $currentIndex = 0;

    public function __construct(array $usedIndices = []) {
        $this->usedIndices = $usedIndices;
        $this->currentIndex = count($this->usedIndices) % count($this->fallbackImages);
    }

    public function getImageForLocation($location, &$usedIndices = null) {
        if ($usedIndices === null) {
            $this->usedIndices = [];
            $this->currentIndex = 0;
        } else {
            $this->usedIndices = $usedIndices;
            $this->currentIndex = count($this->usedIndices) % count($this->fallbackImages);
        }

        $location = strtolower(trim($location));
        $searchTerm = $this->locationMapping[$location] ?? $location . ' Tunisia';

        // Fetch a new image every time
        $imageUrl = $this->fetchFromPixabay($searchTerm);
        if (!$imageUrl) {
            $imageUrl = $this->getUnsplashImage($searchTerm);
        }
        if (!$imageUrl) {
            $imageUrl = $this->fallbackImages[$this->currentIndex];
            $this->usedIndices[] = $this->currentIndex;
            $this->currentIndex = ($this->currentIndex + 1) % count($this->fallbackImages);
        }

        if ($usedIndices !== null) $usedIndices = $this->usedIndices;
        $_SESSION['used_indices'] = $this->usedIndices;

        return $imageUrl;
    }

    private function fetchFromPixabay($searchTerm) {
        $url = "https://pixabay.com/api/?key={$this->apiKey}&q=" . urlencode($searchTerm) . "&image_type=photo&orientation=horizontal&category=travel&per_page=10&min_width=600&safesearch=true";
        
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log("Pixabay API error: " . curl_error($ch));
                curl_close($ch);
                return null;
            }
            
            curl_close($ch);
            $data = json_decode($response, true);
            
            if (isset($data['hits']) && is_array($data['hits']) && count($data['hits']) > 0) {
                // Randomly select an image from the top 10 results
                $randomIndex = rand(0, min(9, count($data['hits']) - 1));
                return $data['hits'][$randomIndex]['largeImageURL'] ?? $data['hits'][$randomIndex]['webformatURL'];
            }
            
            error_log("Pixabay API: No images found for '$searchTerm'");
            return null;
        } catch (Exception $e) {
            error_log("Pixabay API exception: " . $e->getMessage());
            return null;
        }
    }

    public function getUnsplashImage($searchTerm) {
        $timestamp = time();
        return "https://source.unsplash.com/800x600/?" . urlencode($searchTerm) . "&t=" . $timestamp;
    }

    public function resetUsedIndices() {
        $this->usedIndices = [];
        $this->currentIndex = 0;
        $_SESSION['used_indices'] = [];
    }
}
?>