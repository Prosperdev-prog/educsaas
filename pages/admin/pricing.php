<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 

$current_plan = $_SESSION['plan_name'] ?? 'FREE';
// Note: Dans une version réelle, on vérifierait aussi la durée (30j vs 365j) via la date d'expiration
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="text-center mb-5 pt-3">
            <h1 class="display-4 fw-bold mb-2">Statut de votre Abonnement</h1>
            <p class="text-muted lead">Votre école bénéficie actuellement du plan <span class="badge bg-primary px-3 rounded-pill"><?= $current_plan ?></span></p>
        </div>
        
        <div class="row justify-content-center">
            <!-- Plan Mensuel -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-lg overflow-hidden position-relative <?= ($current_plan === 'PREMIUM' ) ? 'opacity-75' : '' ?>" style="border-radius: 24px;">
                    <div class="p-5 text-center">
                        <h5 class="text-primary text-uppercase fw-bold mb-4">Pack Mensuel</h5>
                        <h2 class="display-4 fw-bold mb-0">5 000 <small class="h4">FCFA</small></h2>
                        <p class="text-muted">Valable pour 30 jours</p>
                    </div>
                    <div class="card-body bg-light-blue p-5">
                        <ul class="list-unstyled mb-5">
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Élèves illimités</li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Bulletins PDF professionnels</li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Module Accès Parents</li>
                            <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Statistiques & Classement</li>
                        </ul>
                        <?php if ($current_plan === 'FREE'): ?>
                            <button class="btn btn-primary rounded-pill w-100 fw-bold py-3 shadow-lg">Choisir ce Plan</button>
                        <?php else: ?>
                            <button class="btn btn-secondary rounded-pill w-100 fw-bold py-3 disabled" disabled>Plan Actif</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Plan Annuel -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100 border-0 shadow-lg overflow-hidden position-relative" style="border-radius: 24px; border: 3px solid #ffc107 !important;">
                    <div class="bg-warning text-dark text-center py-2 fw-bold text-uppercase small">Économisez 10 000 FCFA</div>
                    <div class="p-5 text-center">
                        <h5 class="text-warning text-uppercase fw-bold mb-4">Pack Annuel</h5>
                        <h2 class="display-4 fw-bold mb-0">50 000 <small class="h4">FCFA</small></h2>
                        <p class="text-muted">Valable pour 365 jours</p>
                    </div>
                    <div class="card-body bg-white p-5">
                        <ul class="list-unstyled mb-5">
                            <li class="mb-3"><i class="fas fa-check text-warning me-2"></i> <b>Tout du Pack Mensuel</b></li>
                            <li class="mb-3"><i class="fas fa-check text-warning me-2"></i> <b>Économie de 2 mois</b></li>
                            <li class="mb-3"><i class="fas fa-check text-warning me-2"></i> Accès prioritaire</li>
                            <li class="mb-0"><i class="fas fa-check text-warning me-2"></i> Support VIP 24/7</li>
                        </ul>
                        <button class="btn btn-warning rounded-pill w-100 fw-bold py-3 shadow-lg text-dark">Passer à l'Annuel</button>
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
