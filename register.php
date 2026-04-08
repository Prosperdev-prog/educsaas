<?php 
session_start();
// La page est désormais publique pour permettre l'auto-inscription
?>
<!DOCTYPE html>
<html lang="fr text-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS École - Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif !important; background: #fdfdfd; height: 100vh; overflow: hidden; display: flex; }
        .hero { background: linear-gradient(45deg, #1e3c72, #2a5298); width: 50%; color: white; display: flex; flex-direction: column; justify-content: center; padding: 5rem; }
        .form-side { width: 50%; overflow-y: auto; padding: 4rem 6rem; background: white; }
        .form-control { border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; border: 1px solid #ddd; background: #fafafa; }
        .form-label { font-weight: bold; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
        .btn-primary { border-radius: 12px; padding: 14px; width: 100%; font-weight: 700; background: #5c67f2; border: none; box-shadow: 0 10px 30px rgba(92,103,242,0.3); }
        .btn-link { color: #666; text-decoration: none; font-weight: bold; }
        .password-toggle {
            position: absolute; right: 15px; top: 12px;
            cursor: pointer; color: #aaa; transition: color 0.3s;
        }
        .password-toggle:hover { color: #5c67f2; }

        /* Mobile Optimization */
        @media (max-width: 991px) {
            body { display: block; overflow-y: auto; height: auto; }
            .hero { display: none !important; }
            .form-side { width: 100%; padding: 2rem 1.5rem; }
            h1.display-3 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

<div class="hero">
    <i class="fas fa-rocket mb-4" style="font-size: 3rem; color: #7d85f5;"></i>
    <h1 class="display-3 fw-bold mb-3">Lancez votre École en 2 minutes.</h1>
    <p class="lead mb-5 opacity-75">Rejoignez des centaines d'écoles qui utilisent notre SaaS pour leur gestion quotidienne. Gestion des élèves, notes, présences et bulletins simplifiée.</p>
    <ul class="list-unstyled p-0 m-0 fs-5">
        <li class="mb-2"><i class="fas fa-check-circle me-3 text-info"></i>Multi-tenant & Sécurisé</li>
        <li class="mb-2"><i class="fas fa-check-circle me-3 text-info"></i>Gestion des rôles flexible</li>
        <li class="mb-2"><i class="fas fa-check-circle me-3 text-info"></i>Bulletins dynamiques</li>
    </ul>
</div>

<div class="form-side">
    <h2 class="fw-bold mb-1">Inscription Institutionnelle</h2>
    <p class="text-muted mb-4 small">Configurez votre compte école dès aujourd'hui.</p>

    <form id="registerForm">
        <div class="mb-1"><label class="form-label">Nom de l'Institution</label></div>
        <input type="text" class="form-control" name="school_name" required placeholder="Lycée de l'Élite">
        
        <div class="row">
           <div class="col-md-6">
                <div class="mb-1"><label class="form-label">Nom de l'Admin</label></div>
                <input type="text" class="form-control" name="admin_nom" required placeholder="Ex: Bakary">
           </div>
           <div class="col-md-6">
                <div class="mb-1"><label class="form-label">Prénom de l'Admin</label></div>
                <input type="text" class="form-control" name="admin_prenom" required placeholder="Ex: Jean">
           </div>
        </div>

        <div class="mb-1"><label class="form-label">Email Professionnel</label></div>
        <input type="email" class="form-control" name="email" required placeholder="admin@votre-ecole.com">

        <div class="mb-4">
            <div class="mb-1"><label class="form-label">Mot de Passe</label></div>
            <div class="position-relative">
                <input type="password" class="form-control" name="password" id="regPassword" required placeholder="••••••••">
                <span class="password-toggle toggle-password" data-target="regPassword">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-3">Créer mon espace école</button>
        
        <div class="text-center mt-3">
             Vous avez déjà un compte ? <a href="index.php" class="btn-link">Identifiez-vous ici</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/api.js"></script>
<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        
        // Utilisation de l'API publique d'inscription
        const res = await apiCall('/auth/register.php', 'POST', data);
        if (res.status === 'success') {
            Swal.fire({
                icon: 'success', title: 'Félicitations!', text: 'Votre école a été créée. Redirection...',
                showConfirmButton: false, timer: 2000
            }).then(() => window.location.href = 'index.php');
        } else {
             Swal.fire({ icon: 'error', title: 'Erreur', text: res.message });
        }
    });
</script>
</body>
</html>
