// Utility function for making AJAX requests
async function makeRequest(url, method, data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    const response = await fetch(url, options);
    return await response.json();
}

// Event management functions
document.getElementById('createEventForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        const response = await makeRequest('process.php?action=create_event', 'POST', {
            title: formData.get('title'),
            description: formData.get('description'),
            event_date: formData.get('event_date') + ' ' + formData.get('event_time'),
            salle: formData.get('salle'),
            max_participants: formData.get('max_participants'),
            registration_deadline_hours: formData.get('registration_deadline_hours')
        });
        
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Succès!',
                text: response.message,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue'
        });
    }
});

async function deleteEvent(eventId) {
    const result = await Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await makeRequest('process.php?action=delete_event', 'POST', {
                event_id: eventId
            });
            
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé!',
                    text: response.message,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: response.message
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue'
            });
        }
    }
}

async function viewParticipants(eventId) {
    try {
        const response = await makeRequest('process.php?action=get_participants', 'POST', {
            event_id: eventId
        });
        
        if (response.success) {
            const participantsList = document.getElementById('participantsList');
            participantsList.innerHTML = '';
            
            if (response.participants.length > 0) {
                const table = document.createElement('table');
                table.className = 'table table-striped';
                
                // Create table header
                const thead = document.createElement('thead');
                thead.innerHTML = `
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Classe</th>
                        <th>Date d'inscription</th>
                    </tr>
                `;
                table.appendChild(thead);
                
                // Create table body
                const tbody = document.createElement('tbody');
                response.participants.forEach(participant => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${participant.nom}</td>
                        <td>${participant.prenom}</td>
                        <td>${participant.classe}</td>
                        <td>${new Date(participant.registration_date).toLocaleDateString('fr-FR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</td>
                    `;
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);
                
                participantsList.appendChild(table);
            } else {
                participantsList.innerHTML = '<p class="text-muted">Aucun participant inscrit</p>';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('participantsModal'));
            modal.show();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue'
        });
    }
}

async function downloadParticipants(eventId) {
    try {
        window.location.href = `process.php?action=download_participants&event_id=${eventId}`;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue lors du téléchargement'
        });
    }
}

async function toggleAdmin(userId) {
    try {
        const response = await makeRequest('process.php?action=toggle_admin', 'POST', {
            user_id: userId,
            current_user_id: document.querySelector('meta[name="user-id"]')?.content
        });
        
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Succès!',
                text: response.message,
                timer: 1500
            }).then(() => {
                // Si l'utilisateur se retire lui-même les droits admin
                if (response.logout_required) {
                    window.location.href = 'index.php';
                } else {
                    window.location.reload();
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue'
        });
    }
}

// Rafraîchissement automatique des données toutes les 30 secondes
setInterval(async () => {
    try {
        // Rafraîchir la liste des événements
        const eventsResponse = await makeRequest('process.php?action=get_events', 'GET');
        if (eventsResponse.success) {
            updateEventsTable(eventsResponse.events);
        }

        // Rafraîchir la liste des utilisateurs
        const usersResponse = await makeRequest('process.php?action=get_users', 'GET');
        if (usersResponse.success) {
            updateUsersTable(usersResponse.users);
        }
    } catch (error) {
        console.error('Erreur lors du rafraîchissement des données:', error);
    }
}, 30000);

// Fonction pour mettre à jour le tableau des événements
function updateEventsTable(events) {
    const tbody = document.querySelector('#events table tbody');
    if (!tbody) return;

    tbody.innerHTML = events.map(event => `
        <tr>
            <td>
                ${escapeHtml(event.title)}
                <br>
                <small class="text-muted">Salle: ${escapeHtml(event.salle)}</small>
            </td>
            <td>
                ${new Date(event.event_date).toLocaleDateString('fr-FR')} à ${new Date(event.event_date).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}
                ${!event.registration_open ? '<br><span class="badge bg-danger">Inscriptions closes</span>' : ''}
            </td>
            <td>
                ${event.current_participants}/${event.max_participants}
                <button class="btn btn-sm btn-info ms-2" onclick="viewParticipants(${event.id})">
                    Voir <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-success ms-2" onclick="downloadParticipants(${event.id})">
                    Excel <i class="bi bi-file-excel"></i>
                </button>
            </td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Fonction pour mettre à jour le tableau des utilisateurs
function updateUsersTable(users) {
    const tbody = document.querySelector('#users table tbody');
    if (!tbody) return;

    tbody.innerHTML = users.map(user => `
        <tr>
            <td>${escapeHtml(user.nom)} ${escapeHtml(user.prenom)}</td>
            <td>${escapeHtml(user.classe)}</td>
            <td>${new Date(user.created_at).toLocaleDateString('fr-FR')}</td>
            <td>
                <span class="badge bg-${user.is_admin ? 'primary' : 'secondary'}">
                    ${user.is_admin ? 'Admin' : 'Utilisateur'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="toggleAdmin(${user.id})">
                    <i class="bi bi-shield"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Fonction utilitaire pour échapper le HTML
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function logout() {
    try {
        const response = await makeRequest('process.php?action=logout', 'POST');
        
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Au revoir!',
                text: response.message,
                timer: 1500
            }).then(() => {
                window.location.href = 'index.php';
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue'
        });
    }
}
