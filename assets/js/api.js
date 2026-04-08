const API_BASE = '/saas/api';

// Fonction générique pour les appels API
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${API_BASE}${endpoint}`, options);
        
        // Gérer le cas où la réponse n'est pas du JSON valide
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Réponse non-JSON du serveur:', text);
            throw new Error('Erreur de communication avec le serveur');
        }

        if (result.status === 'error' && result.message === 'Non autorisé') {
            // Redirection vers login si non autorisé
            window.location.href = '/saas/index.php';
        }

        return result;
    } catch (error) {
        console.error('Erreur API:', error);
        return { status: 'error', message: error.message };
    }
}

// Helper pour afficher les alertes facilement
function showAlert(icon, title, text = '') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            confirmButtonColor: '#5c67f2', // Brand primary
            customClass: {
                confirmButton: 'rounded-pill px-4'
            }
        });
    } else {
        alert(`${title}\n${text}`);
    }
}

// Fonction de déconnexion
async function logout() {
    const res = await apiCall('/auth/logout.php', 'POST');
    if (res.status === 'success') {
        window.location.href = '/saas/index.php';
    }
}

// Global Password Toggle Init
function initPasswordToggle() {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        // Éviter les doublons d'écouteurs si la fonction est appelée plusieurs fois
        if (btn.getAttribute('data-init')) return;
        btn.setAttribute('data-init', 'true');

        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
}

// Auto-init on page load
document.addEventListener('DOMContentLoaded', initPasswordToggle);
