<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'events.php';

$events = getEvents();
$userRegistrations = [];
if (isLoggedIn()) {
    $userRegistrations = getUserRegistrations($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semaine de l'orientation - Lycée Paul Duez Cambrai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body data-theme="light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Semaine de l'orientation - Lycée Paul Duez Cambrai</a>
            <div class="navbar-nav ms-auto d-flex align-items-center">
                <?php if (isLoggedIn()): ?>
                    <span class="nav-item nav-link text-light">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' (' . $_SESSION['classe'] . ')'); ?></span>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="btn btn-warning ms-2">Panel Admin</a>
                    <?php endif; ?>
                    <button class="btn btn-light ms-2" onclick="logout()">Déconnexion</button>
                <?php else: ?>
                    <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Connexion</button>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#registerModal">Inscription</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Événements à venir</h2>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                            <p class="card-text">
                                <small class="text-muted">
                                    Date: <?php echo date('d/m/Y à H:i', strtotime($event['event_date'])); ?><br>
                                    Participants: <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?><br>
                                    Salle: <?php echo htmlspecialchars($event['salle']); ?>
                                    <?php if (!$event['registration_open']): ?>
                                        <br><span class="badge bg-danger">Inscriptions closes</span>
                                    <?php endif; ?>
                                </small>
                            </p>
                            <?php if (isLoggedIn()): ?>
                                <?php if (in_array($event['id'], $userRegistrations)): ?>
                                    <button class="btn btn-danger" onclick="unregisterFromEvent(<?php echo $event['id']; ?>)">
                                        Se désinscrire
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-primary" onclick="registerForEvent(<?php echo $event['id']; ?>)"
                                            <?php echo ($event['current_participants'] >= $event['max_participants'] || !$event['registration_open']) ? 'disabled' : ''; ?>>
                                        S'inscrire
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="showLoginRequired()">S'inscrire</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Connexion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Classe</label>
                            <select class="form-control" name="classe" required>
                                <option value="">Sélectionnez votre classe</option>
                                <optgroup label="Seconde">
                                    <option value="Seconde A">Seconde A</option>
                                    <option value="Seconde B">Seconde B</option>
                                    <option value="Seconde C">Seconde C</option>
                                    <option value="Seconde D">Seconde D</option>
                                    <option value="Seconde E">Seconde E</option>
                                    <option value="Seconde F">Seconde F</option>
                                    <option value="Seconde G">Seconde G</option>
                                    <option value="Seconde H">Seconde H</option>
                                    <option value="Seconde I">Seconde I</option>
                                    <option value="Seconde J">Seconde J</option>
                                    <option value="Seconde K">Seconde K</option>
                                    <option value="Seconde L">Seconde L</option>
                                    <option value="Seconde M">Seconde M</option>
                                </optgroup>
                                <optgroup label="Première">
                                    <option value="Première A">Première A</option>
                                    <option value="Première B">Première B</option>
                                    <option value="Première C">Première C</option>
                                    <option value="Première D">Première D</option>
                                    <option value="Première E">Première E</option>
                                    <option value="Première F">Première F</option>
                                    <option value="Première G">Première G</option>
                                    <option value="Première H">Première H</option>
                                    <option value="Première I">Première I</option>
                                    <option value="Première J">Première J</option>
                                    <option value="Première K">Première K</option>
                                    <option value="Première L">Première L</option>
                                    <option value="Première M">Première M</option>
                                </optgroup>
                                <optgroup label="Terminale">
                                    <option value="Terminale A">Terminale A</option>
                                    <option value="Terminale B">Terminale B</option>
                                    <option value="Terminale C">Terminale C</option>
                                    <option value="Terminale D">Terminale D</option>
                                    <option value="Terminale E">Terminale E</option>
                                    <option value="Terminale F">Terminale F</option>
                                    <option value="Terminale G">Terminale G</option>
                                    <option value="Terminale H">Terminale H</option>
                                    <option value="Terminale I">Terminale I</option>
                                    <option value="Terminale J">Terminale J</option>
                                    <option value="Terminale K">Terminale K</option>
                                    <option value="Terminale L">Terminale L</option>
                                    <option value="Terminale M">Terminale M</option>
                                </optgroup>
                                <optgroup label="Autres">
                                    <option value="Administrateur">Administrateur</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prénom</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Classe</label>
                            <select class="form-control" name="classe" required>
                                <option value="">Sélectionnez votre classe</option>
                                <optgroup label="Seconde">
                                    <option value="Seconde A">Seconde A</option>
                                    <option value="Seconde B">Seconde B</option>
                                    <option value="Seconde C">Seconde C</option>
                                    <option value="Seconde D">Seconde D</option>
                                    <option value="Seconde E">Seconde E</option>
                                    <option value="Seconde F">Seconde F</option>
                                    <option value="Seconde G">Seconde G</option>
                                    <option value="Seconde H">Seconde H</option>
                                    <option value="Seconde I">Seconde I</option>
                                    <option value="Seconde J">Seconde J</option>
                                    <option value="Seconde K">Seconde K</option>
                                    <option value="Seconde L">Seconde L</option>
                                    <option value="Seconde M">Seconde M</option>
                                </optgroup>
                                <optgroup label="Première">
                                    <option value="Première A">Première A</option>
                                    <option value="Première B">Première B</option>
                                    <option value="Première C">Première C</option>
                                    <option value="Première D">Première D</option>
                                    <option value="Première E">Première E</option>
                                    <option value="Première F">Première F</option>
                                    <option value="Première G">Première G</option>
                                    <option value="Première H">Première H</option>
                                    <option value="Première I">Première I</option>
                                    <option value="Première J">Première J</option>
                                    <option value="Première K">Première K</option>
                                    <option value="Première L">Première L</option>
                                    <option value="Première M">Première M</option>
                                </optgroup>
                                <optgroup label="Terminale">
                                    <option value="Terminale A">Terminale A</option>
                                    <option value="Terminale B">Terminale B</option>
                                    <option value="Terminale C">Terminale C</option>
                                    <option value="Terminale D">Terminale D</option>
                                    <option value="Terminale E">Terminale E</option>
                                    <option value="Terminale F">Terminale F</option>
                                    <option value="Terminale G">Terminale G</option>
                                    <option value="Terminale H">Terminale H</option>
                                    <option value="Terminale I">Terminale I</option>
                                    <option value="Terminale J">Terminale J</option>
                                    <option value="Terminale K">Terminale K</option>
                                    <option value="Terminale L">Terminale L</option>
                                    <option value="Terminale M">Terminale M</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </form>
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
    <script src="assets/js/main.js"></script>
</body>
</html>
