<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="text-center mb-5 pt-3">
            <h1 class="display-4 fw-bold mb-2">Choisissez votre Plan</h1>
            <p class="text-muted lead">Des solutions adaptées aux réalités du marché scolaire africain.</p>
        </div>
        
        <div class="row justify-content-center">
            <!-- Plan FREE -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 24px;">
                    <div class="p-5 text-center">
                        <h5 class="text-muted text-uppercase fw-bold mb-4">Plan FREE</h5>
                        <h2 class="display-4 fw-bold mb-0">0 <small class="h4">FCFA</small></h2>
                        <p class="text-muted">Pour tester ou petites écoles</p>
                    </div>
                    <div class="card-body bg-light-blue p-5">
                        <ul class="list-unstyled mb-5">
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Jusqu'à 50 élèves</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Saisie des notes</li>
                            <li class="mb-3"><i class="fas fa-check text-success me-2"></i> Cahier d'appel</li>
                            <li class="mb-3 text-muted opacity-50"><i class="fas fa-times me-2"></i> Bulletins PDF</li>
                            <li class="mb-3 text-muted opacity-50"><i class="fas fa-times me-2"></i> Accès Parents</li>
                            <li class="mb-0 text-muted opacity-50"><i class="fas fa-times me-2"></i> Statistiques avancées</li>
                        </ul>
                        <button class="btn btn-outline-primary rounded-pill w-100 fw-bold py-3">Plan Actuel</button>
                    </div>
                </div>
            </div>

            <!-- Plan PREMIUM -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-lg overflow-hidden position-relative" style="border-radius: 24px; border: 2px solid var(--primary) !important;">
                    <div class="bg-primary text-white text-center py-2 fw-bold text-uppercase small">Recommandé</div>
                    <div class="p-5 text-center">
                        <h5 class="text-primary text-uppercase fw-bold mb-4">Plan PREMIUM</h5>
                        <h2 class="display-4 fw-bold mb-0">5 000 <small class="h4">FCFA</small></h2>
                        <p class="text-muted">Par mois (ou 50 000 FCFA/an)</p>
                    </div>
                    <div class="card-body bg-white p-5">
                        <ul class="list-unstyled mb-5">
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> <b>Élèves illimités</b></li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> <b>Bulletins PDF</b> professionnels</li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> <b>Module Accès Parents</b></li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Statistiques & Classement</li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Support prioritaire</li>
                            <li class="mb-0"><i class="fas fa-check text-primary me-2"></i> Notifications (SMS/WhatsApp)</li>
                        </ul>
                        <a href="<?= $_SESSION['role'] === 'admin' ? '/saas/pages/admin/mon_ecole.php' : '#' ?>" class="btn btn-primary rounded-pill w-100 fw-bold py-3 shadow-lg">Passer au Premium</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12 text-center">
                <div class="card border-0 bg-dark text-white p-4" style="border-radius: 20px;">
                    <div class="card-body d-flex align-items-center justify-content-center flex-wrap">
                        <i class="fas fa-headset fa-2x me-3 text-warning"></i>
                        <span class="me-3">Besoin d'un accompagnement personnalisé ?</span>
                        <a href="mailto:contact@antigravity.com" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold">Nous contacter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.display-4 { font-weight: 800; }
</style>
