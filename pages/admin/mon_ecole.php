<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Mon Établissement</h1>
                <p class="text-muted small">Gérez les informations de votre école et votre abonnement.</p>
             </div>
        </div>
        
        <div class="row">
            <!-- Informations de l'école -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">Informations Générales</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="schoolForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Nom de l'école</label>
                                <input type="text" class="form-control rounded-pill px-3" name="name" id="school_name" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Téléphone</label>
                                    <input type="text" class="form-control rounded-pill px-3" name="phone" id="school_phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-uppercase">Email</label>
                                    <input type="email" class="form-control rounded-pill px-3" name="email" id="school_email">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-uppercase">Adresse</label>
                                <textarea class="form-control rounded-4 px-3" name="address" id="school_address" rows="3"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Abonnement & SaaS Status -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Statut de l'Abonnement</h5>
                        
                        <div class="p-4 rounded-4 bg-primary text-white mb-4 position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="fas fa-crown fa-4x"></i>
                            </div>
                            <h6 class="text-white-50 text-uppercase small fw-bold mb-1">Plan Actuel</h6>
                            <h2 class="fw-bold mb-3" id="plan-name">Chargement...</h2>
                            <div class="d-flex align-items-center small">
                                <i class="far fa-clock me-2"></i>Expire le : <span class="fw-bold ms-1" id="expiry-date">-</span>
                            </div>
                        </div>

                        <div class="list-group list-group-flush mb-4">
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Bulletins PDF</span>
                                <span id="feat-bulletins"><i class="fas fa-times text-danger"></i></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Accès Parents</span>
                                <span id="feat-parents"><i class="fas fa-times text-danger"></i></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Statistiques Avancées</span>
                                <span id="feat-stats"><i class="fas fa-times text-danger"></i></span>
                            </div>
                        </div>

                        <a href="pricing.php" class="btn btn-outline-primary rounded-pill w-100 fw-bold">Upgrade vers PREMIUM</a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Logo de l'école</h5>
                        <div class="text-center py-3">
                            <img id="school-logo-preview" src="/saas/assets/images/logo.png" class="img-fluid mb-3 rounded shadow-sm" style="max-height: 100px;">
                            <form id="logoForm" enctype="multipart/form-data">
                                <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                                <button type="button" class="btn btn-light rounded-pill px-4 btn-sm" onclick="document.getElementById('logoInput').click()">Changer le logo</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    async function loadSchoolData() {
        const res = await apiCall('/schools/read.php');
        if (res.status === 'success') {
            const s = res.data.school;
            const sub = res.data.subscription;

            document.getElementById('school_name').value = s.name;
            document.getElementById('school_phone').value = s.phone || '';
            document.getElementById('school_email').value = s.email || '';
            document.getElementById('school_address').value = s.address || '';
            if(s.logo) document.getElementById('school-logo-preview').src = s.logo;

            if (sub) {
                document.getElementById('plan-name').textContent = sub.plan_name;
                document.getElementById('expiry-date').textContent = sub.end_date;
                
                const check = '<i class="fas fa-check-circle text-success"></i>';
                const cross = '<i class="fas fa-times-circle text-danger"></i>';
                
                document.getElementById('feat-bulletins').innerHTML = sub.has_bulletins ? check : cross;
                document.getElementById('feat-parents').innerHTML = sub.has_parents ? check : cross;
                document.getElementById('feat-stats').innerHTML = sub.has_stats ? check : cross;
            } else {
                document.getElementById('plan-name').textContent = 'FREE (Limité)';
                // Calculer une date fictive ou laisser vide mais pas "Illimité"
                document.getElementById('expiry-date').textContent = 'Plan gratuit - expire après quota';
            }
        }
    }

    document.getElementById('schoolForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const res = await apiCall('/schools/update.php', 'POST', data);
        if (res.status === 'success') {
            showAlert('success', 'Mis à jour', res.message);
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    document.getElementById('logoInput').addEventListener('change', async function() {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('logo', this.files[0]);
            
            // Note: apiCall handle json, for file upload we use fetch directly or improve apiCall
            try {
                const response = await fetch('/saas/api/schools/upload_logo.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();
                if (res.status === 'success') {
                    document.getElementById('school-logo-preview').src = res.logo_url;
                    showAlert('success', 'Logo mis à jour');
                } else {
                    showAlert('error', 'Erreur', res.message);
                }
            } catch (e) {
                showAlert('error', 'Erreur lors de l\'upload');
            }
        }
    });

    document.addEventListener('DOMContentLoaded', loadSchoolData);
</script>
