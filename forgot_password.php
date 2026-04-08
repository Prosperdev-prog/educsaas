<!DOCTYPE html>
<html lang="fr text-dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du Mot de passe</title>
    <!-- Outfit font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Outfit', sans-serif !important;
            background: #f8f9fa;
            height: 100vh; display: flex; align-items: center; justify-content: center; 
        }
        .reset-card { 
            max-width: 400px; width: 100%; border-radius: 24px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            background: white; 
            padding: 3rem 2.5rem; 
        }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 1.5px solid #eee; }
        .btn-primary { 
            border-radius: 12px; padding: 12px; font-weight: 700; 
            background: #5c67f2; border: none;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <div class="text-center mb-4">
        <div class="bg-light p-3 rounded-circle d-inline-block mb-3">
            <i class="fas fa-key text-primary h3 m-0"></i>
        </div>
        <h3 class="fw-bold text-dark">Aide au Compte</h3>
        <p class="text-muted small">Entrez votre email pour recevoir les instructions de réinitialisation.</p>
    </div>
    
    <form id="resetForm">
        <div class="mb-4">
            <label class="form-label text-dark fw-bold small text-uppercase">Email Enregistré</label>
            <input type="email" class="form-control" name="email" required placeholder="nom@votre-domaine.com">
        </div>

        <button type="submit" class="btn btn-primary w-100 shadow-sm mb-3">
            Envoyer les instructions
        </button>

        <div class="text-center mt-3">
            <a href="index.php" class="text-decoration-none text-muted small fw-bold"><i class="fas fa-arrow-left me-2"></i>Retour à la connexion</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/api.js"></script>
<script>
    document.getElementById('resetForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Simuler l'envoi d'email
        Swal.fire({
            icon: 'info',
            title: 'Traitement...',
            text: 'Nous vérifions vos informations dans notre système SaaS.',
            allowOutsideClick: false,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Instructions Envoyées',
                text: 'Si cet email existe dans notre base, vous recevrez bientôt un lien de réinitialisation.',
                confirmButtonColor: '#5c67f2'
            }).then(() => window.location.href = 'index.php');
        });
    });
</script>
</body>
</html>
