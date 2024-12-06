<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'events.php';

header('Content-Type: application/json');

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

$response = ['success' => false, 'message' => 'Action non valide'];

switch ($action) {
    case 'login':
        if (isset($data['nom']) && isset($data['prenom']) && isset($data['classe']) && isset($data['password'])) {
            $response = login($data['nom'], $data['prenom'], $data['classe'], $data['password']);
        } else {
            $response = ['success' => false, 'message' => 'Données manquantes'];
        }
        break;

    case 'register':
        if (isset($data['nom']) && isset($data['prenom']) && isset($data['classe']) && isset($data['password'])) {
            $response = register($data['nom'], $data['prenom'], $data['classe'], $data['password']);
        } else {
            $response = ['success' => false, 'message' => 'Données manquantes'];
        }
        break;

    case 'logout':
        $response = logout();
        break;

    case 'register_event':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Vous devez être connecté'];
            break;
        }
        
        if (isset($data['event_id'])) {
            $response = registerForEvent($_SESSION['user_id'], $data['event_id']);
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'événement manquant'];
        }
        break;

    case 'unregister_event':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Vous devez être connecté'];
            break;
        }
        
        if (isset($data['event_id'])) {
            $response = unregisterFromEvent($_SESSION['user_id'], $data['event_id']);
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'événement manquant'];
        }
        break;

    // Admin actions
    case 'create_event':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        
        if (isset($data['title']) && isset($data['description']) && isset($data['event_date']) && 
            isset($data['salle']) && isset($data['max_participants']) && isset($data['registration_deadline_hours'])) {
            $response = createEvent(
                $data['title'], 
                $data['description'], 
                $data['event_date'], 
                $data['salle'],
                $data['max_participants'],
                $data['registration_deadline_hours']
            );
        } else {
            $response = ['success' => false, 'message' => 'Données manquantes'];
        }
        break;

    case 'delete_event':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        
        if (isset($data['event_id'])) {
            $response = deleteEvent($data['event_id']);
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'événement manquant'];
        }
        break;

    case 'get_participants':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        
        if (isset($data['event_id'])) {
            $response = getEventParticipants($data['event_id']);
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'événement manquant'];
        }
        break;

    case 'download_participants':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        
        if (isset($_GET['event_id'])) {
            downloadParticipantsExcel($_GET['event_id']);
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'événement manquant'];
        }
        break;

    case 'get_events':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        $response = ['success' => true, 'events' => getEvents()];
        break;

    case 'get_users':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        $response = ['success' => true, 'users' => getAllUsers()];
        break;

    case 'toggle_admin':
        if (!isLoggedIn() || !isAdmin()) {
            $response = ['success' => false, 'message' => 'Accès non autorisé'];
            break;
        }
        
        if (isset($data['user_id'])) {
            $response = toggleAdmin($data['user_id']);
            // Si l'utilisateur se retire lui-même les droits admin
            if ($response['success'] && isset($data['current_user_id']) && 
                $data['current_user_id'] == $data['user_id'] && !$response['is_admin']) {
                $response['logout_required'] = true;
            }
        } else {
            $response = ['success' => false, 'message' => 'ID de l\'utilisateur manquant'];
        }
        break;
}

echo json_encode($response);
?>
