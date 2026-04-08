<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Mon Profil</h1>
                <p class="text-muted small">Gérez vos informations personnelles et votre sécurité.</p>
             </div>
        </div>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm text-center p-4" style="border-radius: 20px;">
                    <div class="mb-3 position-relative d-inline-block mx-auto">
                        <img src="<?= $_SESSION['user_photo'] ?? '/saas/assets/images/user/1.png' ?>" width="120" height="120" class="rounded-circle shadow-sm object-fit-cover" id="profile-img-preview">
                        <button class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 shadow" onclick="document.getElementById('photoInput').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                        <form id="photoForm" enctype="multipart/form-data">
                            <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                        </form>
                    </div>
                    <h4 class="fw-bold mb-1" id="profile-full-name"><?= $_SESSION['nom'].' '.$_SESSION['prenom'] ?></h4>
                    <p class="text-primary small fw-bold text-uppercase mb-3"><?= $_SESSION['role'] ?></p>
                    <div class="border-top pt-3 text-start">
                        <div class="mb-2">
                            <small class="text-muted d-block small text-uppercase">Email</small>
                            <span class="fw-bold" id="info-email">-</span>
                        </div>
                        <div>
                            <small class="text-muted d-block small text-uppercase">Téléphone</small>
                            <span class="fw-bold" id="info-phone">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">Modifier mes informations</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="profileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Nom</label>
                                    <input type="text" class="form-control rounded-pill px-3" name="nom" id="profile_nom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Prénom</label>
                                    <input type="text" class="form-control rounded-pill px-3" name="prenom" id="profile_prenom" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Email</label>
                                    <input type="email" class="form-control rounded-pill px-3" name="email" id="profile_email" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                    <input type="text" class="form-control rounded-pill px-3" name="phone" id="profile_phone">
                                </div>
                            </div>
                            
                            <hr class="my-4 opacity-50">
                            
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-lock me-2"></i>Changement de mot de passe</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Nouveau mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control rounded-start-pill px-3 border-end-0" name="password" id="profile_password" placeholder="••••••••">
                                        <span class="input-group-text bg-white border-start-0 rounded-end-pill pe-3 c-pointer toggle-password" data-target="profile_password">
                                            <i class="fas fa-eye text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Confirmer le mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control rounded-start-pill px-3 border-end-0" id="confirm_password" placeholder="••••••••">
                                        <span class="input-group-text bg-white border-start-0 rounded-end-pill pe-3 c-pointer toggle-password" data-target="confirm_password">
                                            <i class="fas fa-eye text-muted"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-4">Laissez les champs de mot de passe vides si vous ne souhaitez pas le modifier.</small>
                            
                            <div class="text-end pt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    async function loadProfile() {
        const res = await apiCall('/auth/get_profile.php');
        if (res.status === 'success') {
            const u = res.data;
            document.getElementById('info-email').textContent = u.email;
            document.getElementById('info-phone').textContent = u.phone || 'Non renseigné';
            
            document.getElementById('profile_nom').value = u.nom;
            document.getElementById('profile_prenom').value = u.prenom;
            document.getElementById('profile_email').value = u.email;
            document.getElementById('profile_phone').value = u.phone || '';
        }
    }

    // Gestion du changement de photo
    document.getElementById('photoInput').addEventListener('change', async function() {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('photo', this.files[0]);
            
            try {
                const response = await fetch('/saas/api/auth/upload_photo.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                if (res.status === 'success') {
                    document.getElementById('profile-img-preview').src = res.photo_url;
                    showAlert('success', 'Photo mise à jour');
                    // Optionnel: rafraîchir le header
                    location.reload();
                } else {
                    showAlert('error', 'Erreur', res.message);
                }
            } catch (e) {
                showAlert('error', 'Erreur lors de l\'upload');
            }
        }
    });

    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const confirmPass = document.getElementById('confirm_password').value;

        if (data.password && data.password !== confirmPass) {
            showAlert('error', 'Erreur', 'Les mots de passe ne correspondent pas');
            return;
        }

        const res = await apiCall('/auth/update_profile.php', 'POST', data);
        if (res.status === 'success') {
            showAlert('success', 'Mis à jour', res.message);
            loadProfile();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
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

    document.addEventListener('DOMContentLoaded', loadProfile);
</script>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.object-fit-cover { object-fit: cover; }
.c-pointer { cursor: pointer; }
.rounded-start-pill { border-top-left-radius: 50rem !important; border-bottom-left-radius: 50rem !important; }
.rounded-end-pill { border-top-right-radius: 50rem !important; border-bottom-right-radius: 50rem !important; }
</style>
