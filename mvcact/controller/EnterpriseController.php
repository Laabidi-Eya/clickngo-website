<?php
require_once __DIR__ . '/../model/EnterpriseModel.php';

class EnterpriseController {
    private $model;

    public function __construct() {
        $this->model = new EnterpriseModel();
    }

    public function index() {
        // Récupérer toutes les activités d'entreprise par catégorie
        $data = [
            'team-building' => $this->model->getActivitiesByCategory('team-building'),
            'animation' => $this->model->getActivitiesByCategory('animation'),
            'seminaire' => $this->model->getActivitiesByCategory('seminaire'),
            'reunion' => $this->model->getActivitiesByCategory('reunion'),
            'soiree' => $this->model->getActivitiesByCategory('soiree'),
            'repas' => $this->model->getActivitiesByCategory('repas'),
            'fundays' => $this->model->getActivitiesByCategory('fundays'),
            'projets-sur-mesure' => $this->model->getActivitiesByCategory('projets-sur-mesure')
        ];
        
        return [
            'section' => 'enterprise',
            'activities' => $data
        ];
    }

    public function add() {
        // Renvoie la section d'ajout d'activité d'entreprise
        $category = isset($_GET['category']) ? $_GET['category'] : 'team-building';
        
        return [
            'section' => 'add_enterprise',
            'category' => $category
        ];
    }

    public function edit($id) {
        // Renvoie la section de modification d'activité d'entreprise
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $activity = $this->model->getEnterpriseActivityById($id, $category);
        
        if (!$activity) {
            return [
                'section' => 'edit_enterprise',
                'error' => 'Activité non trouvée'
            ];
        }
        
        return [
            'section' => 'edit_enterprise',
            'activity' => $activity,
            'category' => $category
        ];
    }

    public function delete($id) {
        // Rediriger vers process_enterprise.php pour la suppression
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        header("Location: process_enterprise.php?operation=delete&id=$id&category=$category");
        exit;
    }

    public function search() {
        $searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        $results = $this->model->searchEnterpriseActivities($searchTerm, $category);
        
        return [
            'section' => 'enterprise_search_results',
            'results' => $results,
            'searchTerm' => $searchTerm,
            'category' => $category,
            'count' => count($results)
        ];
    }
}
?> 