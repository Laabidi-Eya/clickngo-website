<?php
/**
 * Class to generate images for travel destinations
 */
class ImageGenerator {
    // Pixabay API key - you need to register for a free API key at https://pixabay.com/api/docs/
    private $apiKey = '50179716-ab86b94afe69b4b8f33a8ad4d'; // Replace with your actual API key
    
    // Backup image URLs in case the API fails or no results are found
    private $fallbackImages = [
        'https://cdn.pixabay.com/photo/2017/06/05/11/01/airport-2373727_1280.jpg',
        'https://cdn.pixabay.com/photo/2017/08/06/12/06/people-2591874_1280.jpg',
        'https://cdn.pixabay.com/photo/2016/01/09/18/27/journey-1130732_1280.jpg',
        'https://cdn.pixabay.com/photo/2017/12/15/13/51/polynesia-3021072_1280.jpg',
        'https://cdn.pixabay.com/photo/2016/11/22/22/21/adventure-1850912_1280.jpg',
        'https://cdn.pixabay.com/photo/2016/01/19/15/48/luggage-1149289_1280.jpg',
        'https://cdn.pixabay.com/photo/2016/11/23/15/14/beach-1853442_1280.jpg',
        'https://cdn.pixabay.com/photo/2016/01/19/17/57/car-1149997_1280.jpg'
    ];
    
    // Cache to store already fetched images
    private static $imageCache = [];
    
    /**
     * Get an image URL for a specific location
     * 
     * @param string $location The location to get an image for
     * @return string The URL of the image
     */
    public function getImageForLocation($location) {
        // Check if we already have this location in cache
        if (isset(self::$imageCache[$location])) {
            return self::$imageCache[$location];
        }
        
        // Try to get an image from Pixabay
        $imageUrl = $this->fetchFromPixabay($location);
        
        // Store in cache
        self::$imageCache[$location] = $imageUrl;
        
        return $imageUrl;
    }
    
    /**
     * Fetch an image from Pixabay API
     * 
     * @param string $location The location to search for
     * @return string The URL of the image
     */
    private function fetchFromPixabay($location) {
        // Clean the location name for the search
        $searchTerm = urlencode($location . ' travel');
        
        // Build the API URL
        $url = "https://pixabay.com/api/?key={$this->apiKey}&q={$searchTerm}&image_type=photo&orientation=horizontal&category=travel&per_page=3";
        
        try {
            // Initialize cURL session
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 seconds timeout
            
            // Execute cURL session
            $response = curl_exec($ch);
            
            // Check for cURL errors
            if (curl_errno($ch)) {
                curl_close($ch);
                return $this->getFallbackImage();
            }
            
            // Close cURL session
            curl_close($ch);
            
            // Decode JSON response
            $data = json_decode($response, true);
            
            // Check if we got any hits
            if (isset($data['hits']) && count($data['hits']) > 0) {
                // Return the URL of the first image
                return $data['hits'][0]['webformatURL'];
            }
            
            // If no hits, try a more generic search
            return $this->fetchFromPixabay('travel');
            
        } catch (Exception $e) {
            // If any error occurs, return a fallback image
            return $this->getFallbackImage();
        }
    }
    
    /**
     * Get a random fallback image
     * 
     * @return string The URL of a fallback image
     */
    private function getFallbackImage() {
        // Return a random fallback image
        return $this->fallbackImages[array_rand($this->fallbackImages)];
    }
    
    /**
     * Alternative method using Unsplash Source API (no API key required)
     * 
     * @param string $location The location to get an image for
     * @return string The URL of the image
     */
    public function getUnsplashImage($location) {
        $searchTerm = urlencode($location . ' travel');
        return "https://source.unsplash.com/300x200/?" . $searchTerm;
    }
    
    /**
     * Alternative method using Picsum Photos (completely random images)
     * 
     * @return string The URL of a random image
     */
    public function getRandomImage() {
        // Generate a random ID between 1 and 1000
        $randomId = rand(1, 1000);
        return "https://picsum.photos/id/{$randomId}/300/200";
    }
}
?>
