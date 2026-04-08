<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4 text-dark pb-5">
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h1 class="h2 fw-bold text-dark pt-3">Bienvenue, Prof. <?= $_SESSION['nom'] ?> <span class="text-primary">👋</span></h1>
                <p class="text-secondary opacity-75">Gérez vos classes, notes et activités pédagogiques depuis ce centre de contrôle.</p>
            </div>
            <div class="col-4 text-end pt-3">
                 <div class="bg-white p-3 rounded-pill shadow-sm d-inline-block border">
                    <span class="fw-bold small text-muted text-uppercase me-2">Session Active</span>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold">2025-2026</span>
                 </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-sm-6">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                             <div class="bg-primary-light text-primary p-3 rounded-circle" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-users fs-4"></i>
                             </div>
                             <span class="badge bg-light text-success border border-success fw-bold px-3">Assigné</span>
                        </div>
                        <h2 class="fw-bold mb-1" id="stat-eleves">...</h2>
                        <p class="text-muted fw-bold small text-uppercase m-0 letter-spacing-1">Mes Élèves</p>
                    </div>
                    <div class="bg-primary p-1"></div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                             <div class="bg-soft-warning text-warning p-3 rounded-circle border border-warning" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-star fs-4"></i>
                             </div>
                             <span class="badge bg-light text-warning border border-warning fw-bold px-3">Moyenne</span>
                        </div>
                        <h2 class="fw-bold mb-1">12.8 / 20</h2>
                        <p class="text-muted fw-bold small text-uppercase m-0 letter-spacing-1">Performance Classe</p>
                    </div>
                    <div class="bg-warning p-1"></div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                             <div class="bg-soft-success text-success p-3 rounded-circle border border-success" style="width:50px;height:50px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-check-double fs-4"></i>
                             </div>
                             <span class="badge bg-light text-primary border border-primary fw-bold px-3">Présences</span>
                        </div>
                        <h2 class="fw-bold mb-1">94%</h2>
                        <p class="text-muted fw-bold small text-uppercase m-0 letter-spacing-1">Assiduité Globale</p>
                    </div>
                    <div class="bg-success p-1"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px; height: 100%;">
                    <div class="card-header bg-white border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                        <h4 class="card-title fw-bold m-0 text-dark">Journal d'Évaluation Récent</h4>
                        <a href="notes.php" class="btn btn-sm btn-light rounded-pill px-3 shadow-xs">Tout voir</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive px-4 pb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="small text-uppercase text-muted border-top border-bottom">
                                    <tr>
                                        <th class="py-3">Élève</th>
                                        <th>Matière</th>
                                        <th>Date</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody id="table-notes" class="border-top-0">
                                    <!-- JS Filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                 <div class="card border-0 shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #5c67f2 0%, #7d85f5 100%);">
                    <div class="card-body p-4 text-white">
                        <h4 class="fw-bold mb-3">Accès Rapide</h4>
                        <div class="d-grid gap-2">
                            <a href="notes.php" class="btn btn-white bg-white text-primary border-0 rounded-pill fw-bold shadow-sm py-2">
                                <i class="fas fa-star me-2 mt-1"></i> Saisir des Notes
                            </a>
                            <a href="presences.php" class="btn btn-white bg-white text-dark border-0 rounded-pill fw-bold shadow-sm py-2">
                                <i class="fas fa-check-circle me-2 mt-1"></i> Gérer Appels
                            </a>
                            <a href="devoirs.php" class="btn btn-white bg-white text-dark border-0 rounded-pill fw-bold shadow-sm py-2">
                                <i class="fas fa-book-open me-2 mt-1"></i> Cahier de Textes
                            </a>
                        </div>
                    </div>
                 </div>
                 
                 <div class="card border-0 shadow-sm mt-4" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Prochaines Échéances</h5>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item bg-transparent border-0 px-0 d-flex gap-3 mb-2">
                                <div class="bg-soft-danger text-danger p-2 rounded text-center" style="width: 50px;">
                                    <h6 class="m-0 fw-bold">12</h6>
                                    <small class="text-uppercase small-font">Avr</small>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">Rendu Projet Maths</h6>
                                    <small class="text-muted">3ème A • 14:00</small>
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-primary-light { background-color: rgba(92, 103, 242, 0.1); }
.bg-soft-warning { background-color: rgba(241, 196, 15, 0.1); }
.bg-soft-success { background-color: rgba(0, 184, 148, 0.1); }
.bg-soft-danger { background-color: rgba(231, 76, 60, 0.1); }
.small-font { font-size: 0.65rem; }
.shadow-xs { box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.shadow-sm { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.letter-spacing-1 { letter-spacing: 1px; }
.btn-white:hover { background: #f8f9fa !important; transform: scale(1.02); }
</style>

<script>
    async function loadTeacherDashboard() {
        // Charge les notes globales (filtre par school_id géré en back)
        const res = await apiCall('/notes/read.php'); 
        const resStats = await apiCall('/enseignants/stats.php');
        
        if (resStats.status === 'success') {
            document.getElementById('stat-eleves').textContent = resStats.data.total_eleves || '0';
        }

        if (res.status === 'success') {
            const tbody = document.getElementById('table-notes');
            tbody.innerHTML = '';
            if(res.data.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Aucune note récente.</td></tr>';
            } else {
                res.data.slice(0, 6).forEach(n => {
                    tbody.innerHTML += `
                        <tr>
                            <td><div class="fw-bold">${n.eleve_nom} ${n.eleve_prenom}</div> <small class="text-muted">${n.classe_nom || '--'}</small></td>
                            <td><span class="badge bg-light text-dark fw-normal border">${n.matiere_nom}</span></td>
                            <td class="small text-muted">${n.date_evaluation}</td>
                            <td><span class="fw-bold ${n.note < 10 ? 'text-danger' : 'text-primary'}">${n.note} / 20</span></td>
                        </tr>
                    `;
                });
            }
        }
    }
    document.addEventListener('DOMContentLoaded', loadTeacherDashboard);
</script>

