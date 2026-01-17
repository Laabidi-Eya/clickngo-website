<?php
require_once __DIR__ . '/../model/ActivityModel.php';
require_once __DIR__ . '/../model/ReservationModel.php';

class ActivityController {
    private $model;
    private $reservationModel;

    public function __construct() {
        $this->model = new ActivityModel();
        $this->reservationModel = new ReservationModel();
    }

    public function index() {
        $activities = $this->model->getAllActivities();
        $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
        
        if (!empty($searchTerm)) {
            $activities = $this->model->searchActivitiesByName($searchTerm);
        }
        
        return [
            'section' => 'control_data',
            'activities' => $activities,
            'searchTerm' => $searchTerm
        ];
    }

    public function add() {
        // Si le formulaire est soumis, l'action est gérée par process_activity.php
        return [
            'section' => 'add_activity'
        ];
    }

    public function edit($id) {
        $activity = $this->model->getActivityById($id);
        
        if (!$activity) {
            return [
                'section' => 'edit_activity',
                'error' => 'Activité non trouvée'
            ];
        }
        
        return [
            'section' => 'edit_activity',
            'activity' => $activity
        ];
    }

    public function delete($id) {
        // Cette méthode sert uniquement à rediriger vers process_activity.php
        // L'opération de suppression est effectuée dans process_activity.php
        $baseUrl = dirname(dirname($_SERVER['PHP_SELF']));
        header("Location: $baseUrl/view/back office/process_activity.php?operation=delete&id=$id");
        exit;
    }

    public function notifications() {
        $data = $this->model->getNotifications();
        
        return [
            'section' => 'notifications',
            'notifications' => $data
        ];
    }

    public function calendar() {
        // Par défaut, on récupère les activités à venir pour initialiser le calendrier
        return [
            'section' => 'calendar'
        ];
    }

    /**
     * Récupère les activités pour une date spécifique sélectionnée dans le calendrier
     */
    public function getActivitiesBySelectedDate() {
        if(isset($_GET['date']) && !empty($_GET['date'])) {
            $date = $_GET['date'];
            
            // Récupérer activités régulières pour cette date
            $activities = $this->model->getActivitiesByDate($date);
            
            // Si un modèle d'entreprise est disponible, récupérer aussi ces activités
            $enterpriseActivities = [];
            if(class_exists('EnterpriseModel')) {
                require_once __DIR__ . '/../model/EnterpriseModel.php';
                $enterpriseModel = new EnterpriseModel();
                $enterpriseActivities = $enterpriseModel->getEnterpriseActivitiesByDate($date);
            }
            
            // Envoyer les résultats au format JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'date' => $date,
                'activities' => $activities,
                'enterpriseActivities' => $enterpriseActivities
            ]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Date non spécifiée'
            ]);
            exit;
        }
    }

    public function statistics() {
        $stats = $this->model->getStatistics();
        $participantsByMonth = $this->model->getParticipantsByMonth();
        $activitiesByCategory = $this->model->getActivitiesByCategory();

        // --- Statistiques Entreprise ---
        require_once __DIR__ . '/../model/EnterpriseModel.php';
        $enterpriseModel = new \EnterpriseModel();
        // Répartition par catégorie
        $enterpriseCategories = [
            'team-building', 'animation', 'reunion', 'soiree', 'repas', 'fundays', 'projets-sur-mesure'
        ];
        $enterpriseByCategory = [];
        foreach ($enterpriseCategories as $cat) {
            $count = count($enterpriseModel->getActivitiesByCategory($cat));
            $enterpriseByCategory[] = [
                'category' => $cat,
                'count' => $count
            ];
        }
        // Par mois
        $enterpriseByMonth = [];
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count FROM enterprise_activities GROUP BY month ORDER BY month ASC";
        $stmt = $enterpriseModel->getDb()->query($sql);
        $enterpriseByMonth = $stmt->fetchAll();
        if (empty($enterpriseByMonth)) {
            $enterpriseByMonth = [
                ['month' => '2024-01', 'count' => 2],
                ['month' => '2024-02', 'count' => 3],
                ['month' => '2024-03', 'count' => 1],
            ];
        }

        // --- Statistiques Réservations ---
        require_once __DIR__ . '/../model/ReservationModel.php';
        $reservationModel = new \ReservationModel();
        // Par mois
        $reservationsByMonth = [];
        $sql = "SELECT DATE_FORMAT(reservation_date, '%Y-%m') AS month, COUNT(*) AS count FROM reservations GROUP BY month ORDER BY month ASC";
        $stmt = $reservationModel->getDb()->query($sql);
        $reservationsByMonth = $stmt->fetchAll();
        if (empty($reservationsByMonth)) {
            $reservationsByMonth = [
                ['month' => '2024-01', 'count' => 5],
                ['month' => '2024-02', 'count' => 7],
                ['month' => '2024-03', 'count' => 3],
            ];
        }
        // Par statut
        $reservationsByStatus = [];
        $sql = "SELECT payment_status AS status, COUNT(*) AS count FROM reservations GROUP BY payment_status";
        $stmt = $reservationModel->getDb()->query($sql);
        $reservationsByStatus = $stmt->fetchAll();
        if (empty($reservationsByStatus)) {
            $reservationsByStatus = [
                ['status' => 'confirmed', 'count' => 4],
                ['status' => 'pending', 'count' => 2],
                ['status' => 'cancelled', 'count' => 1],
            ];
        }

        // --- Statistiques Avis ---
        require_once __DIR__ . '/../model/ReviewModel.php';
        $reviewModel = new \ReviewModel();
        // Par note
        $reviewsByNote = [];
        for ($i = 5; $i >= 1; $i--) {
            $sql = "SELECT COUNT(*) AS count FROM reviews WHERE rating = $i";
            $stmt = $reviewModel->getDb()->query($sql);
            $count = $stmt->fetchColumn();
            $reviewsByNote[] = ['note' => $i, 'count' => (int)$count];
        }
        // Par statut
        $reviewsByStatus = [];
        $sql = "SELECT status, COUNT(*) AS count FROM reviews GROUP BY status";
        $stmt = $reviewModel->getDb()->query($sql);
        $reviewsByStatus = $stmt->fetchAll();
        if (empty($reviewsByStatus)) {
            $reviewsByStatus = [
                ['status' => 'approved', 'count' => 3],
                ['status' => 'pending', 'count' => 1],
                ['status' => 'rejected', 'count' => 0],
            ];
        }

        return [
            'section' => 'statistics',
            'stats' => $stats,
            'participantsByMonth' => $participantsByMonth,
            'activitiesByCategory' => $activitiesByCategory,
            'enterpriseByCategory' => $enterpriseByCategory,
            'enterpriseByMonth' => $enterpriseByMonth,
            'reservationsByMonth' => $reservationsByMonth,
            'reservationsByStatus' => $reservationsByStatus,
            'reviewsByNote' => $reviewsByNote,
            'reviewsByStatus' => $reviewsByStatus
        ];
    }

    public function daily_activity() {
        $dailyActivity = $this->model->getDailyActivity();
        
        return [
            'section' => 'daily_activity',
            'dailyActivity' => $dailyActivity
        ];
    }

    public function history() {
        $history = $this->model->getActivityHistory();
        $upcomingActivities = $this->model->getUpcomingActivities();
        
        return [
            'section' => 'history',
            'history' => $history,
            'upcomingActivities' => $upcomingActivities
        ];
    }

    public function settings() {
        // Placeholder pour de futures fonctionnalités
        return [
            'section' => 'settings'
        ];
    }

    public function logout() {
        // Implémentation de la déconnexion
        // À adapter selon votre système d'authentification
        session_start();
        session_destroy();
        header("Location: login.php");
        exit;
    }

    public function enterprise() {
        // Placeholder pour de futures fonctionnalités
        return [
            'section' => 'enterprise'
        ];
    }

    public function reservations() {
        $reservations = $this->reservationModel->getAllReservations();
        
        return [
            'section' => 'reservations',
            'reservations' => $reservations
        ];
    }

    public function reviews() {
        // Récupérer toutes les données des avis pour le dashboard admin
        require_once __DIR__ . '/ReviewController.php';
        $reviewController = new ReviewController();
        $reviews = $reviewController->getAllReviews();
        $averageRating = $reviewController->getAverageRating();
        $ratingsDistribution = $reviewController->getRatingsDistribution();
        
        $totalReviews = count($reviews);
        $pendingCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;
        
        // Compter les différents statuts
        foreach ($reviews as $review) {
            if ($review['status'] == 'pending') $pendingCount++;
            if ($review['status'] == 'approved') $approvedCount++;
            if ($review['status'] == 'rejected') $rejectedCount++;
        }
        
        return [
            'section' => 'reviews',
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'averageRating' => $averageRating,
            'ratingsDistribution' => $ratingsDistribution
        ];
    }
}
?>
