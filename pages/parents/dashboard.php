<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4 text-dark">
        <!-- Parent Header -->
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="h3 fw-bold mb-1">Espace Parent <span class="text-primary">• Suivi</span></h1>
                <p class="text-muted">Consultez en temps réel l'évolution scolaire et la présence de vos enfants.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <button class="btn btn-primary rounded-pill shadow-sm"><i class="fas fa-file-pdf me-2"></i>Rapport Global</button>
            </div>
        </div>

        <!-- Metric Summary -->
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 15px; border-left: 5px solid #00cec9;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-light-info rounded-circle d-inline-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 55px; height: 55px;">
                            <i class="fas fa-child text-info fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0" id="totalChildren">0</h2>
                            <span class="text-muted small fw-bold text-uppercase">Enfants Connectés</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 15px; border-left: 5px solid #ff7675;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-light-danger rounded-circle d-inline-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 55px; height: 55px;">
                            <i class="fas fa-exclamation-circle text-danger fa-lg"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0" id="totalAbsences">0</h2>
                            <span class="text-muted small fw-bold text-uppercase">Absences Signalées</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card shadow-sm border-0 h-100 bg-gradient-success text-white" style="border-radius: 15px;">
                    <div class="card-body p-4 d-flex align-items-center">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center me-3 shadow-sm text-success" style="width: 55px; height: 55px;">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="fw-bold mb-0 text-white">Consulter Notes</h3>
                            <a href="/saas/pages/parents/mes_notes.php" class="text-white-50 text-uppercase small text-decoration-none fw-bold"><i class="fas fa-arrow-right"></i> Voir Tableaux</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Children Cards -->
            <div class="col-xl-7 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">Mes Enfants</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row" id="childCards">
                            <!-- JS Filled -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Notifications / Activity -->
            <div class="col-xl-5 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0">Résultats Récents</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="recentActivity">
                            <!-- JS Filled -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #1d976c 0%, #93f9b9 100%);
}
.bg-light-info { background-color: rgba(0, 206, 201, 0.1); }
.bg-light-danger { background-color: rgba(255, 118, 117, 0.1); }
.child-card {
    transition: transform 0.2s ease;
    border-radius: 15px;
    background-color: #f8f9fa;
}
.child-card:hover { transform: translateY(-5px); }
</style>

<script>
async function loadParentStats() {
    const res = await apiCall('/parents/stats.php');
    if(res.status === 'success') {
        const d = res.data;
        document.getElementById('totalChildren').textContent = d.children.length;
        document.getElementById('totalAbsences').textContent = d.total_absences;

        // Children cards
        const childContainer = document.getElementById('childCards');
        childContainer.innerHTML = '';
        if(d.children.length > 0) {
            d.children.forEach(c => {
                childContainer.innerHTML += `
                    <div class="col-md-6 mb-3">
                        <div class="card child-card border-0 p-3 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white p-3 rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">${c.prenom} ${c.nom}</h6>
                                    <small class="text-muted"><i class="fas fa-graduation-cap me-1"></i>${c.classe_nom}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="/saas/pages/parents/mon_bulletin.php?eleve_id=${c.id}" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-pill"><i class="fas fa-file-alt"></i> Bulletin</a>
                                <a href="/saas/pages/parents/mes_devoirs.php?eleve_id=${c.id}" class="btn btn-sm btn-outline-warning rounded-pill"><i class="fas fa-book"></i></a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Recent Notes Activity
        const activityList = document.getElementById('recentActivity');
        activityList.innerHTML = '';
        if(d.recent_notes.length > 0) {
            d.recent_notes.forEach(n => {
                activityList.innerHTML += `
                    <li class="list-group-item d-flex align-items-center justify-content-between p-3 border-0 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-light p-2 rounded me-3 text-center" style="width: 45px;">
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">${n.eleve_prenom}</h6>
                                <small class="text-muted">${n.matiere_nom}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="h5 fw-bold mb-0 text-primary">${n.note}</span>
                            <br><small class="text-muted">${new Date(n.date_evaluation).toLocaleDateString()}</small>
                        </div>
                    </li>
                `;
            });
        } else {
            activityList.innerHTML = '<li class="list-group-item text-center py-4 text-muted">Aucune note récente publiée.</li>';
        }
    }
}
document.addEventListener('DOMContentLoaded', loadParentStats);
</script>

<?php include '../../includes/footer.php'; ?>
