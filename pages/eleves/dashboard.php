<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'eleve') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4">
        <!-- Student Hero -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg border-0 bg-primary text-white overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-white p-2 shadow-sm me-3">
                                <img src="/saas/assets/images/user/1.png" width="80" height="80" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=<?= $_SESSION['nom'] ?>+<?= $_SESSION['prenom'] ?>&background=random'">
                            </div>
                            <div>
                                <h2 class="text-white fw-bold mb-1">Salut, <?= $_SESSION['prenom'] ?> ! 👋</h2>
                                <p class="mb-0 opacity-75">Bon retour dans ton espace. Prêt pour tes cours d'aujourd'hui ?</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Key Metrics -->
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <div class="bg-light-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 55px; height: 55px;">
                            <i class="fas fa-tasks text-info fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark" id="total-devoirs">0</h2>
                        <span class="text-muted small fw-bold text-uppercase">Devoirs à faire</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <div class="bg-light-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 55px; height: 55px;">
                            <i class="fas fa-check-double text-success fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark" id="total-notes">0</h2>
                        <span class="text-muted small fw-bold text-uppercase">Notes acquises</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm bg-info text-white" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 text-info shadow-sm" style="width: 55px; height: 55px;">
                            <i class="fas fa-star fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 text-white">16.5</h2>
                        <span class="text-white-50 small fw-bold text-uppercase">Moyenne Génér.</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <div class="bg-light-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 55px; height: 55px;">
                            <i class="fas fa-calendar-alt text-warning fa-lg"></i>
                        </div>
                        <h2 class="fw-bold mb-1 text-dark">85%</h2>
                        <span class="text-muted small fw-bold text-uppercase">Présence Globale</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity and schedule -->
        <div class="row">
            <div class="col-xl-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <h4 class="card-title fw-bold mb-0"><i class="fas fa-bell me-2 text-primary"></i>Dernièrement</h4>
                        <a href="/saas/pages/eleves/mes_notes.php" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">Tout voir</a>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush" id="recent-notes">
                            <!-- JS Fill -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                        <h4 class="card-title fw-bold mb-0"><i class="fas fa-book-open me-2 text-warning"></i>Prochains Devoirs</h4>
                        <a href="/saas/pages/eleves/mes_devoirs.php" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">Détails</a>
                    </div>
                    <div class="card-body">
                        <div id="upcoming-devoirs"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light-info { background-color: rgba(0, 191, 191, 0.1) !important; }
.bg-light-success { background-color: rgba(40, 199, 111, 0.1) !important; }
.bg-light-warning { background-color: rgba(255, 159, 67, 0.1) !important; }
.note-badge {
    padding: 8px 12px;
    border-radius: 10px;
}
</style>

<script>
async function loadEleveStats() {
    const res = await apiCall('/eleves/stats.php');
    if (res.status === 'success') {
        const d = res.data;
        document.getElementById('total-devoirs').textContent = d.total_devoirs;
        document.getElementById('total-notes').textContent = d.total_notes;

        // Notes table logic
        const notesDiv = document.getElementById('recent-notes');
        notesDiv.innerHTML = '';
        if(d.recent_notes.length > 0) {
            d.recent_notes.forEach(n => {
                notesDiv.innerHTML += `
                    <li class="list-group-item d-flex align-items-center border-0 px-0 mb-2">
                        <div class="bg-primary-light text-primary me-3 p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-feather-alt"></i>
                        </div>
                        <div>
                            <p class="fw-bold text-dark mb-0">${n.matiere_nom}</p>
                            <span class="text-muted small">${n.date_evaluation}</span>
                        </div>
                        <div class="ms-auto">
                            <span class="note-badge bg-light text-primary font-weight-bold h5 mb-0">${n.note}</span>
                        </div>
                    </li>
                `;
            });
        } else {
            notesDiv.innerHTML = '<li class="list-group-item border-0 text-center py-4">Pas de notes enregistrées.</li>';
        }

        // Devoirs logic
        const devoirsDiv = document.getElementById('upcoming-devoirs');
        devoirsDiv.innerHTML = '';
        if(d.upcoming_devoirs.length > 0) {
            d.upcoming_devoirs.forEach(dev => {
                devoirsDiv.innerHTML += `
                    <div class="mb-3 d-flex align-items-start p-3 bg-light rounded-4">
                        <div class="me-3 mt-1 text-warning"><i class="fas fa-clock fa-lg"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">${dev.titre}</h6>
                            <p class="text-muted mb-1 small">${dev.matiere_nom}</p>
                            <span class="badge bg-warning-light text-warning small px-2 py-1 rounded-pill">Pour : ${dev.date_limite}</span>
                        </div>
                    </div>
                `;
            });
        } else {
            devoirsDiv.innerHTML = '<div class="text-center py-4 text-muted">Aucun devoir à l\'horizon ! 🎉</div>';
        }
    }
}
document.addEventListener('DOMContentLoaded', loadEleveStats);
</script>

<?php include '../../includes/footer.php'; ?>
