<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'events.php';

// Redirect if not admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

$events = getEvents();
$users = getAllUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="<?php echo $_SESSION['user_id']; ?>">
    <title>Panel Administrateur - Semaine de l'orientation - Lycée Paul Duez Cambrai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Panel Admin</a>
            <div class="navbar-nav ms-auto">
                <a href="index.php" class="nav-link">Retour au site</a>
                <span class="nav-item nav-link text-light">Admin: <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['classe'] . ')'); ?></span>
                <button class="btn btn-light ms-2" onclick="logout()">Déconnexion</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="events-tab" data-bs-toggle="tab" href="#events" role="tab">Événements</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users" role="tab">Utilisateurs</a>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <!-- Events Tab -->
            <div class="tab-pane fade show active" id="events" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gestion des Événements</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        <i class="bi bi-plus-circle"></i> Nouvel Événement
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Participants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($event['title']); ?>
                                    <br>
                                    <small class="text-muted">Salle: <?php echo htmlspecialchars($event['salle']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($event['event_date'])); ?></td>
                                <td>
                                    <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?>
                                    <button class="btn btn-sm btn-info ms-2" onclick="viewParticipants(<?php echo $event['id']; ?>)">
                                        Voir <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success ms-2" onclick="downloadParticipants(<?php echo $event['id']; ?>)">
                                        Excel <i class="bi bi-file-excel"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <h2 class="mb-4">Gestion des Utilisateurs</h2>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom et Prénom</th>
                                <th>Classe</th>
                                <th>Date d'inscription</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                <td>
                    <?php 
                        echo htmlspecialchars($user['nom'] . ' ' . $user['prenom']);
                    ?>
                </td>
                <td><?php echo htmlspecialchars($user['classe']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Utilisateur</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="toggleAdmin(<?php echo $user['id']; ?>)">
                                        <i class="bi bi-shield"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Créer un Événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createEventForm">
                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="event_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Heure</label>
                            <input type="time" class="form-control" name="event_time" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salle</label>
                            <input type="text" class="form-control" name="salle" required placeholder="ex: Amphithéâtre A, Salle 201">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre maximum de participants</label>
                            <input type="number" class="form-control" name="max_participants" required min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Délai limite d'inscription (en heures avant l'événement)</label>
                            <input type="number" class="form-control" name="registration_deadline_hours" required min="1" value="24">
                            <small class="form-text text-muted">Les inscriptions seront fermées automatiquement ce nombre d'heures avant l'événement</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Participants Modal -->
    <div class="modal fade" id="participantsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Participants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="participantsList"></div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>Fait avec <span class="heart">❤️</span> par Pierre Bouteman</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
