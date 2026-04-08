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
            <h1 class="h3 mb-0 text-gray-800">Cahier de Texte (Devoirs)</h1>
            <button class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#devoirModal"><i class="fas fa-edit fa-sm text-white-50"></i> Ajouter Devoir</button>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="form-select" id="filter-classe" onchange="loadDevoirs()">
                    <!-- Data classes -->
                </select>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-dark" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th>Date Limite</th>
                                <th>Classe</th>
                                <th>Matière</th>
                                <th>Titre du Devoir</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody id="devoirs-table-body">
                            <!-- Remplissage JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Devoirs -->
<div class="modal fade text-dark" id="devoirModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nouveau Devoir</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="devoirForm">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Titre du devoir</label>
                  <input type="text" class="form-control" name="titre" required placeholder="Ex: Exercices 1 à 3 page 15">
              </div>
              <div class="mb-3">
                  <label class="form-label">Classe concernée</label>
                  <select class="form-select" name="classe_id" id="input_classe_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Matière</label>
                  <select class="form-select" name="matiere_id" id="input_matiere_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Date limite de rendu</label>
                  <input type="date" class="form-control" name="date_limite" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Description (Optionnel)</label>
                  <textarea class="form-control" name="description" rows="3"></textarea>
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
    

    async function loadSelectData() {
        const [classesRes, matieresRes] = await Promise.all([
            apiCall('/classes/read.php'),
            apiCall('/matieres/read.php')
        ]);
        
        const filterCls = document.getElementById('filter-classe');
        const inputCls = document.getElementById('input_classe_id');
        if (classesRes.status === 'success') {
            filterCls.innerHTML = '<option value="">Toutes les classes</option>';
            inputCls.innerHTML = '<option value="">Choisir...</option>';
            classesRes.data.forEach(c => {
                let opt = `<option value="${c.id}">${c.nom}</option>`;
                filterCls.innerHTML += opt;
                inputCls.innerHTML += opt;
            });
        }
        
        const inputMat = document.getElementById('input_matiere_id');
        if (matieresRes.status === 'success') {
            inputMat.innerHTML = '<option value="">Choisir...</option>';
            matieresRes.data.forEach(m => {
                inputMat.innerHTML += `<option value="${m.id}">${m.nom}</option>`;
            });
        }
    }

    async function loadDevoirs() {
        const c_id = document.getElementById('filter-classe').value;
        const res = await apiCall('/devoirs/read.php?classe_id=' + c_id);
        if (res.status === 'success') {
            const tbody = document.getElementById('devoirs-table-body');
            tbody.innerHTML = '';
            
            if(res.data.length === 0){
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3">Aucun devoir enregistré.</td></tr>';
            }

            res.data.forEach(d => {
                const limitDate = new Date(d.date_limite);
                const today = new Date();
                let badgeDate = (limitDate < today) ? 'text-danger fw-bold' : 'text-success';
                
                tbody.innerHTML += `
                    <tr>
                        <td class="${badgeDate}">${d.date_limite}</td>
                        <td><span class="badge bg-secondary">${d.classe_nom}</span></td>
                        <td><span class="badge bg-primary">${d.matiere_nom}</span></td>
                        <td><strong>${d.titre}</strong></td>
                        <td><small>${d.description || '-'}</small></td>
                    </tr>
                `;
            });
        }
    }

    document.getElementById('devoirForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        
        const res = await apiCall('/devoirs/create.php', 'POST', data);
        if (res.status === 'success') {
            $('#devoirModal').modal('hide');
            this.reset();
            showAlert('success', 'Succès', res.message);
            loadDevoirs();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSelectData();
        loadDevoirs();
    });
</script>
