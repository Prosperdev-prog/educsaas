<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Cahier de Textes / Devoirs</h1>
                <p class="text-muted small">Suivez les travaux et leçons de vos enfants.</p>
             </div>
        </div>
        
        <div class="row" id="devoirs-container">
            <!-- JS Filled -->
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    async function loadDevoirs() {
        const res = await apiCall('/devoirs/read_mes_devoirs.php');
        if (res.status === 'success') {
            renderDevoirs(res.data);
        }
    }

    function renderDevoirs(data) {
        const container = document.getElementById('devoirs-container');
        container.innerHTML = '';
        
        if (data.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-5"><div class="card border-0 shadow-sm p-5" style="border-radius:20px;"><i class="fas fa-check-circle text-success fa-3x mb-3"></i><h4 class="fw-bold">Aucun devoir !</h4><p class="text-muted">Tout est à jour pour vos enfants.</p></div></div>';
            return;
        }

        data.forEach(d => {
            const dateLimite = new Date(d.date_limite);
            const today = new Date();
            const diffTime = dateLimite - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            let statusBadge = '';
            if (diffDays < 0) statusBadge = '<span class="badge bg-danger rounded-pill px-3">En retard</span>';
            else if (diffDays <= 2) statusBadge = '<span class="badge bg-warning text-dark rounded-pill px-3">Urgent</span>';
            else statusBadge = '<span class="badge bg-success rounded-pill px-3">À venir</span>';

            container.innerHTML += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-primary-light text-primary p-3 rounded-circle" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-book-open fa-lg"></i>
                                </div>
                                ${statusBadge}
                            </div>
                            <h5 class="fw-bold text-dark mb-1">${d.titre}</h5>
                            <p class="text-primary small fw-bold mb-3"><i class="fas fa-book me-2"></i>${d.matiere_nom}</p>
                            <p class="text-muted small mb-4 text-truncate-2">${d.description || 'Pas de description supplémentaire.'}</p>
                            <div class="d-flex align-items-center pt-3 border-top mt-auto">
                                <div class="text-muted small">
                                    <i class="far fa-calendar-alt me-2"></i>Date limite : <span class="fw-bold text-dark">${d.date_limite}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    document.addEventListener('DOMContentLoaded', loadDevoirs);
</script>

<style>
.bg-primary-light { background-color: rgba(92, 103, 242, 0.1); }
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
