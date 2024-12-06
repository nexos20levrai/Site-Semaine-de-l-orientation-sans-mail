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

// Form validation functions
function validateName(name) {
    return name.length >= 2 && /^[A-Za-zÀ-ÿ\- ]+$/.test(name);
}

function validateForm(formData) {
    const nom = formData.get('nom');
    const prenom = formData.get('prenom');
    const classe = formData.get('classe');
    const password = formData.get('password');

    if (!validateName(nom)) {
        throw new Error('Le nom doit contenir au moins 2 caractères et ne contenir que des lettres');
    }
    if (!validateName(prenom)) {
        throw new Error('Le prénom doit contenir au moins 2 caractères et ne contenir que des lettres');
    }
    if (!classe) {
        throw new Error('Veuillez sélectionner une classe');
    }
    if (password.length < 6) {
        throw new Error('Le mot de passe doit contenir au moins 6 caractères');
    }
}

// Authentication functions
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        validateForm(formData);
        const response = await makeRequest('process.php?action=login', 'POST', {
            nom: formData.get('nom'),
            prenom: formData.get('prenom'),
            classe: formData.get('classe'),
            password: formData.get('password')
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
            title: 'Erreur de validation',
            text: error.message
        });
    }
});

document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    try {
        validateForm(formData);
        const response = await makeRequest('process.php?action=register', 'POST', {
            nom: formData.get('nom'),
            prenom: formData.get('prenom'),
            classe: formData.get('classe'),
            password: formData.get('password')
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
            title: 'Erreur de validation',
            text: error.message
        });
    }
});

// Event registration functions
async function registerForEvent(eventId) {
    try {
        const response = await makeRequest('process.php?action=register_event', 'POST', {
            event_id: eventId
        });
        
        Swal.fire({
            icon: response.success ? 'success' : 'error',
            title: response.success ? 'Succès!' : 'Erreur',
            text: response.message,
            timer: response.success ? 1500 : undefined
        }).then(() => {
            if (response.success) {
                window.location.reload();
            }
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: 'Une erreur est survenue'
        });
    }
}

async function unregisterFromEvent(eventId) {
    const result = await Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Voulez-vous vraiment vous désinscrire de cet événement?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, me désinscrire',
        cancelButtonText: 'Annuler'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await makeRequest('process.php?action=unregister_event', 'POST', {
                event_id: eventId
            });
            
            Swal.fire({
                icon: response.success ? 'success' : 'error',
                title: response.success ? 'Succès!' : 'Erreur',
                text: response.message,
                timer: response.success ? 1500 : undefined
            }).then(() => {
                if (response.success) {
                    window.location.reload();
                }
            });
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue'
            });
        }
    }
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
                window.location.reload();
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

function showLoginRequired() {
    Swal.fire({
        icon: 'info',
        title: 'Connexion requise',
        text: 'Veuillez vous connecter pour vous inscrire à un événement',
        showCancelButton: true,
        confirmButtonText: 'Se connecter',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        }
    });
}

// Auto-capitalize first letter of nom and prenom fields
document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
    });
});

// Theme toggle functionality
function toggleTheme() {
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Update toggle button icon
    const icon = themeToggle.querySelector('i');
    if (newTheme === 'dark') {
        icon.classList.remove('bi-moon-fill');
        icon.classList.add('bi-sun-fill');
        // Update SweetAlert2 theme
        Swal.getContainer()?.classList.add('swal2-dark');
    } else {
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-fill');
        // Update SweetAlert2 theme
        Swal.getContainer()?.classList.remove('swal2-dark');
    }
}

// Set initial theme from localStorage
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const themeToggle = document.getElementById('themeToggle');
    
    document.body.setAttribute('data-theme', savedTheme);
    
    // Set initial toggle button icon
    const icon = themeToggle.querySelector('i');
    if (savedTheme === 'dark') {
        icon.classList.remove('bi-moon-fill');
        icon.classList.add('bi-sun-fill');
        // Set SweetAlert2 dark theme
        Swal.getContainer()?.classList.add('swal2-dark');
    } else {
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-fill');
        // Set SweetAlert2 light theme
        Swal.getContainer()?.classList.remove('swal2-dark');
    }
});

// Update SweetAlert2 theme when toggling
const originalSwalFire = Swal.fire;
Swal.fire = function(...args) {
    const theme = document.body.getAttribute('data-theme');
    if (args[0]?.backdrop === undefined) {
        args[0] = {
            ...args[0],
            background: theme === 'dark' ? '#16213e' : '#ffffff',
            color: theme === 'dark' ? '#e9ecef' : '#2c3e50'
        };
    }
    return originalSwalFire.apply(this, args);
};
