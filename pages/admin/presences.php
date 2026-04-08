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
                <h1 class="h3 mb-0 fw-bold">Cahier d'Appel</h1>
                <p class="text-muted small">Suivez les présences, absences et retards de vos élèves.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-toggle="modal" data-target="#presenceModal">
                 <i class="fas fa-check-circle me-2"></i>Faire l'Appel
             </button>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-2" style="border-radius: 12px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="date" class="form-control border-0" id="filter-date" onchange="loadPresences()">
                        <button class="btn btn-light btn-sm rounded-pill px-3 ms-2" onclick="resetFilter()">Voir tout</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light-blue text-dark small text-uppercase">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Élève</th>
                                <th>Statut</th>
                                <th class="pe-4">Motif / Commentaire</th>
                            </tr>
                        </thead>
                        <tbody id="presences-table-body" class="border-top-0">
                            <!-- JS Filled -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Présence -->
<div class="modal fade" id="presenceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold">Saisir une Présence</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="presenceForm">
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Date</label>
                  <input type="date" class="form-control rounded-pill px-3" name="date_presence" required id="input_date_presence" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Élève</label>
                  <select class="form-select rounded-pill px-3" name="eleve_id" id="modal_eleve_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Statut</label>
                  <select class="form-select rounded-pill px-3" name="statut" id="modal_statut" required>
                      <option value="présent">Présent</option>
                      <option value="absent">Absent</option>
                      <option value="retard">En Retard</option>
                  </select>
              </div>
              <div class="mb-0" id="motif_div" style="display:none;">
                  <label class="form-label fw-bold small text-uppercase">Motif / Observation</label>
                  <textarea class="form-control rounded-4 px-3" name="motif" rows="2" placeholder="Ex: Maladie, Retard bus..."></textarea>
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Enregistrer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    document.getElementById('modal_statut').addEventListener('change', function() {
        document.getElementById('motif_div').style.display = (this.value === 'absent' || this.value === 'retard') ? 'block' : 'none';
    });

    async function loadSelectData() {
        const res = await apiCall('/eleves/read.php');
        const selEleve = document.getElementById('modal_eleve_id');
        if (res.status === 'success') {
            selEleve.innerHTML = '<option value="">Choisir un élève...</option>';
            res.data.forEach(e => {
                selEleve.innerHTML += `<option value="${e.id}">${e.nom} ${e.prenom} (${e.matricule})</option>`;
            });
        }
    }

    async function loadPresences() {
        const date = document.getElementById('filter-date').value;
        const res = await apiCall('/presences/read.php' + (date ? '?date=' + date : ''));
        const tbody = document.getElementById('presences-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            if(res.data.length === 0){
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Aucun enregistrement trouvé.</td></tr>';
                return;
            }

            res.data.forEach(p => {
                let badgeClass = 'bg-soft-success text-success';
                if(p.statut === 'absent') badgeClass = 'bg-soft-danger text-danger';
                if(p.statut === 'retard') badgeClass = 'bg-soft-warning text-warning';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4 fw-bold text-dark">${p.date_presence}</td>
                        <td>
                            <div class="fw-bold">${p.eleve_nom} ${p.eleve_prenom}</div>
                            <small class="text-muted">${p.matricule}</small>
                        </td>
                        <td><span class="badge ${badgeClass} rounded-pill px-3 text-capitalize">${p.statut}</span></td>
                        <td class="pe-4 text-muted small">${p.motif ? p.motif : '-'}</td>
                    </tr>
                `;
            });
        }
    }

    function resetFilter() {
        document.getElementById('filter-date').value = '';
        loadPresences();
    }

    document.getElementById('presenceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const res = await apiCall('/presences/create.php', 'POST', data);
        if (res.status === 'success') {
            $('#presenceModal').modal('hide');
            this.reset();
            document.getElementById('motif_div').style.display = 'none';
            showAlert('success', 'Réussi', res.message);
            loadPresences();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSelectData();
        loadPresences();
    });
</script>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.bg-soft-success { background-color: rgba(0, 184, 148, 0.1); }
.bg-soft-danger { background-color: rgba(231, 76, 60, 0.1); }
.bg-soft-warning { background-color: rgba(241, 196, 15, 0.1); }
</style>
