<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr text-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS École - Connexion Sécurisée</title>
    <!-- Outfit font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Outfit', sans-serif !important;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center; 
            margin: 0;
        }
        .login-card { 
            max-width: 450px; width: 100%; border-radius: 28px; 
            box-shadow: 0 25px 70px rgba(0,0,0,0.12); 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(15px);
            padding: 3.5rem 3rem; 
            border: 1px solid rgba(255,255,255,0.6);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .login-card:hover { transform: translateY(-8px); box-shadow: 0 35px 90px rgba(0,0,0,0.18); }
        .form-control { 
            border-radius: 14px; padding: 14px 18px; border: 1.5px solid #eee; 
            background: #fff; transition: border-color 0.3s;
        }
        .form-control:focus { border-color: #5c67f2; box-shadow: none; }
        .btn-primary { 
            border-radius: 14px; padding: 14px; font-weight: 700; 
            background: linear-gradient(45deg, #5c67f2, #7d85f5); border: none;
            box-shadow: 0 8px 25px rgba(92, 103, 242, 0.35);
            transition: all 0.3s;
        }
        .btn-primary:hover { transform: scale(1.02); box-shadow: 0 10px 30px rgba(92, 103, 242, 0.45); }
        .icon-box {
            width: 70px; height: 70px; background: rgba(92, 103, 242, 0.1);
            color: #5c67f2; display: flex; align-items: center; justify-content: center;
            border-radius: 20px; margin: 0 auto 1.8rem; font-size: 2.2rem;
        }
        .password-toggle {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #aaa; transition: color 0.3s;
        }
        .password-toggle:hover { color: #5c67f2; }
        .input-group-text { border-radius: 0 14px 14px 0; background: transparent; border-left: none; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center">
        <div class="icon-box"><i class="fas fa-shield-alt"></i></div>
        <h2 class="fw-bold text-dark mb-1">Portail d'Accès</h2>
        <p class="text-muted mb-4 pb-2">Entrez vos identifiants pour continuer.</p>
    </div>
    
    <form id="loginForm">
        <div class="mb-3">
            <label class="form-label text-dark fw-bold small text-uppercase letter-spacing-1">Identifiant Email</label>
            <input type="email" class="form-control" name="email" required placeholder="nom@ecole.com">
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label text-dark fw-bold small text-uppercase m-0">Mot de passe</label>
                <a href="forgot_password.php" class="text-decoration-none text-primary small fw-bold">Oublié ?</a>
            </div>
            <div class="position-relative">
                <input type="password" class="form-control" name="password" id="passwordInput" required placeholder="••••••••">
                <span class="password-toggle" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 shadow-sm mb-4">
            Connexion au Tableau de Bord <i class="fas fa-chevron-right ms-2 small"></i>
        </button>

        <div class="text-center border-top pt-4">
            <p class="text-muted small mb-1">Vous êtes un Chef d'établissement ?</p>
            <a href="register.php" class="text-decoration-none text-dark fw-bold h6">Ouvrir une nouvelle École <i class="fas fa-external-link-alt ms-1 small"></i></a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/api.js"></script>

<script>
    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#passwordInput');

    togglePassword.addEventListener('click', function (e) {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Form Submission
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Show loading state
        const btn = this.querySelector('button');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Authentification...';

        const data = Object.fromEntries(new FormData(this));
        const res = await apiCall('/auth/login.php', 'POST', data);
        
        btn.disabled = false;
        btn.innerHTML = origText;

        if (res.status === 'success') {
            Swal.fire({ 
                icon: 'success', 
                title: 'Connexion réussie', 
                text: 'Redirection vers votre espace...',
                showConfirmButton: false, 
                timer: 1500,
                background: '#fff',
                color: '#2c3e50'
            }).then(() => {
                const userRole = res.data.role;
                // Redirection basée sur le rôle
                switch(userRole) {
                    case 'admin': window.location.href = '/saas/pages/admin/dashboard.php'; break;
                    case 'superadmin': window.location.href = '/saas/pages/superadmin/dashboard.php'; break;
                    case 'enseignant': window.location.href = '/saas/pages/enseignants/dashboard.php'; break;
                    case 'eleve': window.location.href = '/saas/pages/eleves/dashboard.php'; break;
                    case 'parent': window.location.href = '/saas/pages/parents/dashboard.php'; break;
                    default: window.location.href = '/saas/pages/admin/dashboard.php';
                }
            });
        } else {
            Swal.fire({ 
                icon: 'error', 
                title: 'Échec d\'authentification', 
                text: res.message || 'Identifiants invalides.',
                confirmButtonColor: '#5c67f2'
            });
        }
    });
</script>
</body>
</html>