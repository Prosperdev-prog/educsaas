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
                <h1 class="h3 mb-0 fw-bold">Bulletins Scolaires</h1>
                <p class="text-muted small">Récapitulatif des performances de vos enfants.</p>
             </div>
             <button class="btn btn-outline-primary rounded-pill px-4 shadow-sm" id="btn-print" onclick="window.print()">
                 <i class="fas fa-print me-2"></i>Imprimer
             </button>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-4 border-bottom bg-light-blue" style="border-radius: 20px 20px 0 0;">
                        <h5 class="fw-bold mb-0" id="eleve-name">Chargement...</h5>
                        <small class="text-muted" id="eleve-info">-</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="bg-light text-dark small text-uppercase text-center">
                                    <tr>
                                        <th class="text-start ps-4">Matières</th>
                                        <th>Coef.</th>
                                        <th>Moyenne / 20</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody id="bulletin-table-body">
                                    <!-- JS Filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm bg-primary text-white mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4 text-center">
                        <h6 class="text-white-50 text-uppercase fw-bold mb-3">Moyenne Générale</h6>
                        <h1 class="display-3 fw-bold mb-0" id="moyenne-generale">0.00</h1>
                        <p class="mt-2 mb-0 opacity-75" id="mention-text">-</p>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm" style="border-radius: 20px;" id="premium-notice">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="fas fa-crown text-warning fa-3x"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Version PREMIUM</h5>
                        <p class="text-muted small">Activez le <b>Pack Parent Premium</b> pour recevoir les notes par SMS et télécharger le bulletin officiel signé.</p>
                        <a href="pricing.php" class="btn btn-warning btn-sm rounded-pill w-100 fw-bold shadow-sm">Débloquer le Premium</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    async function loadBulletin() {
        const res = await apiCall('/bulletins/read_mon_bulletin.php');
        if (res.status === 'success') {
            renderBulletin(res.data);
        } else {
            document.getElementById('bulletin-table-body').innerHTML = `<tr><td colspan="4" class="text-center py-5 text-danger">${res.message}</td></tr>`;
        }
    }

    function renderBulletin(data) {
        document.getElementById('eleve-name').textContent = `${data.eleve.nom} ${data.eleve.prenom}`;
        document.getElementById('eleve-info').textContent = `Classe : ${data.eleve.classe_nom || '-'} | Matricule : ${data.eleve.matricule}`;

        const tbody = document.getElementById('bulletin-table-body');
        tbody.innerHTML = '';
        
        if (!data.lignes || data.lignes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Aucune donnée disponible pour le moment.</td></tr>';
            return;
        }

        data.lignes.forEach(l => {
            const points = (l.moyenne * l.coefficient).toFixed(2);
            tbody.innerHTML += `
                <tr class="text-center">
                    <td class="text-start ps-4 fw-bold">${l.matiere}</td>
                    <td>${l.coefficient}</td>
                    <td><span class="badge bg-light text-dark rounded-pill px-3">${l.moyenne.toFixed(2)}</span></td>
                    <td class="fw-bold">${points}</td>
                </tr>
            `;
        });

        document.getElementById('moyenne-generale').textContent = data.moyenne_generale.toFixed(2);
        
        let mention = "Insuffisant";
        if (data.moyenne_generale >= 16) mention = "Excellent";
        else if (data.moyenne_generale >= 14) mention = "Très Bien";
        else if (data.moyenne_generale >= 12) mention = "Bien";
        else if (data.moyenne_generale >= 10) mention = "Passable";
        
        document.getElementById('mention-text').textContent = `Mention : ${mention}`;
    }

    document.addEventListener('DOMContentLoaded', loadBulletin);
</script>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
@media print {
    .nk-sidebar, .header, #btn-print, #premium-notice, .footer { display: none !important; }
    .content-body { margin-left: 0 !important; padding-top: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #eee !important; }
}
</style>
