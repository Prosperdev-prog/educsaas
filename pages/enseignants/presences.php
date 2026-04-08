<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<!-- Content body start -->
<div class="content-body">
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4 text-dark pt-3">
            <h1 class="h3 mb-0 text-gray-800">Cahier d'Appel (Présences)</h1>
            <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#presenceModal"><i class="fas fa-check-circle fa-sm text-white-50"></i> Faire l'Appel</button>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="date" class="form-control" id="filter-date" aria-label="Date">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" onclick="loadPresences()">Filtrer</button>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-dark" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Matricule</th>
                                <th>Élève</th>
                                <th>Statut</th>
                                <th>Motif (Si absent/retard)</th>
                            </tr>
                        </thead>
                        <tbody id="presences-table-body">
                            <!-- Remplissage JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Présence -->
<div class="modal fade text-dark" id="presenceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Saisir Présence</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="presenceForm">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Date</label>
                  <input type="date" class="form-control" name="date_presence" required id="input_date_presence">
              </div>
              <div class="mb-3">
                  <label class="form-label">Élève</label>
                  <select class="form-select" name="eleve_id" id="eleve_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Statut</label>
                  <select class="form-select" name="statut" id="statut" required>
                      <option value="présent">Présent</option>
                      <option value="absent">Absent</option>
                      <option value="retard">En Retard</option>
                  </select>
              </div>
              <div class="mb-3" id="motif_div" style="display:none;">
                  <label class="form-label">Motif</label>
                  <textarea class="form-control" name="motif" rows="2" placeholder="Maladie, Retard transport..."></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    
    
    // Auto date to today
    document.getElementById('input_date_presence').valueAsDate = new Date();
    document.getElementById('filter-date').valueAsDate = new Date();

    // Toggle motif 
    document.getElementById('statut').addEventListener('change', function() {
        if (this.value === 'absent' || this.value === 'retard') {
            document.getElementById('motif_div').style.display = 'block';
        } else {
            document.getElementById('motif_div').style.display = 'none';
        }
    });

    async function loadSelectData() {
        const res = await apiCall('/eleves/read.php');
        const selEleve = document.getElementById('eleve_id');
        if (res.status === 'success') {
            selEleve.innerHTML = '<option value="">Choisir un élève...</option>';
            res.data.forEach(e => {
                selEleve.innerHTML += `<option value="${e.id}">${e.matricule} - ${e.nom} ${e.prenom}</option>`;
            });
        }
    }

    async function loadPresences() {
        const date = document.getElementById('filter-date').value;
        const res = await apiCall('/presences/read.php?date=' + date);
        if (res.status === 'success') {
            const tbody = document.getElementById('presences-table-body');
            tbody.innerHTML = '';
            
            if(res.data.length === 0){
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3">Aucun appel effectué pour cette date.</td></tr>';
            }

            res.data.forEach(p => {
                let badgeClass = 'bg-success';
                if(p.statut === 'absent') badgeClass = 'bg-danger';
                if(p.statut === 'retard') badgeClass = 'bg-warning text-dark';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${p.date_presence}</td>
                        <td>${p.matricule}</td>
                        <td><strong>${p.eleve_nom}</strong> ${p.eleve_prenom}</td>
                        <td><span class="badge ${badgeClass}">${p.statut.toUpperCase()}</span></td>
                        <td>${p.motif ? p.motif : '-'}</td>
                    </tr>
                `;
            });
        }
    }

    document.getElementById('presenceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        
        const res = await apiCall('/presences/create.php', 'POST', data);
        if (res.status === 'success') {
            $('#presenceModal').modal('hide');
            this.reset();
            document.getElementById('input_date_presence').valueAsDate = new Date();
            document.getElementById('motif_div').style.display = 'none';
            showAlert('success', 'Succès', res.message);
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
