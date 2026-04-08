<?php
session_start();

// Protection de la page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') { 
    header('Location: /saas/index.php'); 
    exit; 
}

require_once '../../config/db.php';

// --- RÉCUPÉRATION DES STATISTIQUES RÉELLES ---
try {
    // 1. Nombre total d'écoles (excluant le système)
    $stmtSchools = $conn->query("SELECT COUNT(*) FROM schools WHERE id > 1");
    $total_schools = $stmtSchools->fetchColumn();

    // 2. Revenus mensuels (Somme des paiements du mois en cours)
    $stmtRevenue = $conn->query("SELECT SUM(amount) FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())");
    $monthly_revenue = $stmtRevenue->fetchColumn() ?: 0;

    // 3. Écoles actives vs expirées
    $stmtActive = $conn->query("SELECT COUNT(*) FROM schools WHERE license_status = 'active' AND id > 1");
    $active_schools = $stmtActive->fetchColumn();

    $stmtExpired = $conn->query("SELECT COUNT(*) FROM schools WHERE license_status = 'expired' AND id > 1");
    $expired_schools = $stmtExpired->fetchColumn();

    // 4. Liste des dernières écoles inscrites
    $stmtRecent = $conn->query("SELECT * FROM schools WHERE id > 1 ORDER BY created_at DESC LIMIT 5");
    $recent_schools = $stmtRecent->fetchAll();

} catch (PDOException $e) {
    $total_schools = $active_schools = $expired_schools = 0;
    $monthly_revenue = 0;
    $recent_schools = [];
}

include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4">
        <!-- Hero Section -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0 bg-dark text-white overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-5 position-relative">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h1 class="display-4 fw-bold mb-3">Panel <span class="text-info">SaaS</span> Developpeur</h1>
                                <p class="lead mb-4 opacity-75">Contrôle global des établissements, gestion des abonnements et suivi des revenus en temps réel.</p>
                                <div class="d-flex gap-3">
                                    <a href="/saas/pages/superadmin/ecoles.php" class="btn btn-info btn-lg px-4 rounded-pill shadow-sm"><i class="fas fa-plus-circle me-2"></i>Gérer les Écoles</a>
                                    <a href="/saas/pages/superadmin/licences.php" class="btn btn-outline-light btn-lg px-4 rounded-pill shadow-sm">Finances</a>
                                </div>
                            </div>
                            <div class="col-md-5 d-none d-md-block text-center">
                                <i class="fas fa-gem opacity-25" style="font-size: 10rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Financières & Écoles -->
        <div class="row mt-4">
            <!-- Revenus -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-shape mb-3 d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle shadow-sm" style="width: 60px; height: 60px;">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 mt-2 text-white"><?= number_format($monthly_revenue, 0, ',', ' ') ?> <small>FCFA</small></h2>
                        <span class="text-white-50 text-uppercase small letter-spacing-1">Revenus ce mois</span>
                    </div>
                </div>
            </div>

            <!-- Écoles Totales -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #00d2ff !important;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-shape mb-3 d-inline-flex align-items-center justify-content-center bg-light text-info rounded-circle" style="width: 60px; height: 60px;">
                            <i class="fas fa-school fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 mt-2 text-dark"><?= $total_schools ?></h2>
                        <span class="text-muted text-uppercase small letter-spacing-1">Écoles Partenaires</span>
                    </div>
                </div>
            </div>

            <!-- Écoles Actives -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #28a745 !important;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-shape mb-3 d-inline-flex align-items-center justify-content-center bg-light text-success rounded-circle" style="width: 60px; height: 60px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 mt-2 text-dark"><?= $active_schools ?></h2>
                        <span class="text-muted text-uppercase small letter-spacing-1">Licences Actives</span>
                    </div>
                </div>
            </div>

            <!-- Écoles Expirées -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #dc3545 !important;">
                    <div class="card-body p-4 text-center">
                        <div class="icon-shape mb-3 d-inline-flex align-items-center justify-content-center bg-light text-danger rounded-circle" style="width: 60px; height: 60px;">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 mt-2 text-dark"><?= $expired_schools ?></h2>
                        <span class="text-muted text-uppercase small letter-spacing-1">Licences Expirées</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Écoles & Activité -->
        <div class="row">
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h4 class="card-title fw-bold mb-0">Dernières Écoles Inscrites</h4>
                        <a href="/saas/pages/superadmin/ecoles.php" class="btn btn-sm btn-primary rounded-pill">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>École</th>
                                        <th>Email Admin</th>
                                        <th>Date Inscription</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_schools as $school): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($school['name']) ?></td>
                                        <td><?= htmlspecialchars($school['email']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($school['created_at'])) ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = [
                                                'active' => 'bg-success',
                                                'trial' => 'bg-info',
                                                'expired' => 'bg-danger',
                                                'suspended' => 'bg-dark'
                                            ][$school['license_status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $statusClass ?> rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                                <?= $school['license_status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                                <ul class="dropdown-menu border-0 shadow-sm">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2 text-primary"></i> Modifier</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-power-off me-2 text-danger"></i> Suspendre</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; if(empty($recent_schools)) echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Aucune école enregistrée.</td></tr>"; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Aide & Infos -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow-sm border-0 bg-info text-white" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">Statut du Business</h4>
                        <p class="small mb-4 opacity-75">Le prix du plan PREMIUM est fixé à 5 000 FCFA / mois. Assurez-vous de relancer les écoles à l'expiration de leur licence pour maintenir un flux de revenus constant.</p>
                        <hr class="bg-white opacity-25">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold">Total Revenus Annuel</span>
                            <span class="h4 mb-0 fw-bold">-- FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.letter-spacing-1 { letter-spacing: 1px; }
.bg-primary { background: linear-gradient(135deg, #5c67f2 0%, #7d85f5 100%) !important; }
.text-uppercase { font-size: 0.75rem; font-weight: 700; }
</style>

<?php include '../../includes/footer.php'; ?>
