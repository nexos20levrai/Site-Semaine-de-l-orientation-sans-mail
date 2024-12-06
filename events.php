<?php
require_once 'config.php';

function createEvent($title, $description, $date, $salle, $max_participants, $registration_deadline_hours) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, salle, max_participants, registration_deadline_hours) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $date, $salle, $max_participants, $registration_deadline_hours]);
        return ['success' => true, 'message' => 'Événement créé avec succès!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la création de l\'événement'];
    }
}

function isRegistrationOpen($event) {
    $event_date = strtotime($event['event_date']);
    $deadline = $event_date - ($event['registration_deadline_hours'] * 3600); // Convertir les heures en secondes
    return time() < $deadline;
}

function deleteOldEvents() {
    global $pdo;
    
    try {
        // Delete events that are more than 7 days old
        $stmt = $pdo->prepare("DELETE FROM events WHERE DATE_ADD(event_date, INTERVAL 7 DAY) < NOW()");
        $stmt->execute();
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function getEvents() {
    global $pdo;
    
    try {
        // First, clean up old events
        deleteOldEvents();
        
        $stmt = $pdo->query("SELECT e.*, 
            (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) as current_participants,
            CASE 
                WHEN (UNIX_TIMESTAMP(e.event_date) - (e.registration_deadline_hours * 3600)) > UNIX_TIMESTAMP(NOW()) 
                THEN 1 
                ELSE 0 
            END as registration_open
            FROM events e 
            ORDER BY event_date ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function deleteEvent($event_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        return ['success' => true, 'message' => 'Événement supprimé avec succès!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la suppression de l\'événement'];
    }
}

function registerForEvent($user_id, $event_id) {
    global $pdo;
    
    try {
        // Clean up old events first
        deleteOldEvents();
        
        // Check if already registered
        $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Vous êtes déjà inscrit à cet événement'];
        }
        
        // Check if event is full and registration is still open
        $stmt = $pdo->prepare("SELECT e.*, 
            (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) as current_participants,
            CASE 
                WHEN (UNIX_TIMESTAMP(e.event_date) - (e.registration_deadline_hours * 3600)) > UNIX_TIMESTAMP(NOW()) 
                THEN 1 
                ELSE 0 
            END as registration_open
            FROM events e WHERE e.id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if (!$event['registration_open']) {
            return ['success' => false, 'message' => 'Les inscriptions sont closes pour cet événement'];
        }
        
        if ($event['current_participants'] >= $event['max_participants']) {
            return ['success' => false, 'message' => 'L\'événement est complet'];
        }
        
        // Register user
        $stmt = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $event_id]);
        
        return ['success' => true, 'message' => 'Inscription réussie!'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
}

function unregisterFromEvent($user_id, $event_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt->execute([$user_id, $event_id]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Désinscription réussie!'];
        }
        return ['success' => false, 'message' => 'Vous n\'étiez pas inscrit à cet événement'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la désinscription'];
    }
}

function getUserRegistrations($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT event_id FROM registrations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch(PDOException $e) {
        return [];
    }
}

function getEventParticipants($event_id) {
    global $pdo;
    
    try {
        // Clean up old events first
        deleteOldEvents();
        
        $stmt = $pdo->prepare("
            SELECT u.nom, u.prenom, u.classe, u.id, r.created_at as registration_date
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            WHERE r.event_id = ?
            ORDER BY u.classe, u.nom, u.prenom ASC
        ");
        $stmt->execute([$event_id]);
        return ['success' => true, 'participants' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la récupération des participants'];
    }
}

function downloadParticipantsExcel($event_id) {
    global $pdo;
    
    try {
        // Get event details
        $stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();
        
        if (!$event) {
            return ['success' => false, 'message' => 'Événement non trouvé'];
        }
        
        // Get participants
        $participants = getEventParticipants($event_id);
        if (!$participants['success']) {
            return $participants;
        }
        
        // Clean event title for filename
        $filename = 'Participants_' . preg_replace('/[^a-zA-Z0-9]/', '_', $event['title']) . '.csv';
        
        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Generate CSV content
        $output = "sep=;\n";
        $output .= "Nom;Prenom;Classe\n";
        
        foreach ($participants['participants'] as $participant) {
            // Escape fields that might contain semicolons
            $nom = str_replace(';', ',', $participant['nom']);
            $prenom = str_replace(';', ',', $participant['prenom']);
            $classe = str_replace(';', ',', $participant['classe']);
            
            $output .= sprintf("%s;%s;%s\n", $nom, $prenom, $classe);
        }
        
        echo $output;
        exit;
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de la génération du fichier'];
    }
}
?>
