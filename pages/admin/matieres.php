<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Gestion des Matières</h1>
                <p class="text-muted small">Configurez les matières enseignées et leurs coefficients par classe.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddModal()">
                 <i class="fas fa-plus me-2"></i>Nouvelle Matière
             </button>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light-blue text-dark small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Matière</th>
                                        <th>Classe</th>
                                        <th>Coefficient</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="matieres-table-body" class="border-top-0">
                                    <!-- JS Filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Matière -->
<div class="modal fade" id="matiereModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold" id="modalTitle">Détail de la Matière</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="matiereForm">
          <input type="hidden" name="id" id="matiere_id">
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Classe</label>
                  <select class="form-select rounded-pill px-3" name="classe_id" id="classe_id" required>
                      <!-- Options classes -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Nom de la Matière</label>
                  <input type="text" class="form-control rounded-pill px-3" name="nom" id="matiere_nom" required placeholder="Ex: Informatique">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Coefficient</label>
                  <input type="number" class="form-control rounded-pill px-3" name="coefficient" id="matiere_coefficient" required min="1" value="1">
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
    let matieresData = [];

    async function loadSelectData() {
        const res = await apiCall('/classes/read.php');
        const selCls = document.getElementById('classe_id');
        selCls.innerHTML = '<option value="">Sélectionner une classe...</option>';
        if (res.status === 'success') {
            res.data.forEach(c => {
                selCls.innerHTML += `<option value="${c.id}">${c.nom}</option>`;
            });
        }
    }

    async function loadMatieres() {
        const res = await apiCall('/matieres/read.php');
        const tbody = document.getElementById('matieres-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            matieresData = res.data;
            if(matieresData.length === 0){
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Aucune matière enregistrée.</td></tr>';
                return;
            }
            matieresData.forEach(m => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-light text-primary p-2 rounded me-3" style="width:35px;height:35px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-book-open small"></i>
                                </div>
                                <span class="fw-bold">${m.nom}</span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">${m.classe_nom || 'Toutes'}</span></td>
                        <td><span class="badge bg-light text-primary border border-primary px-3 fw-bold rounded-pill shadow-xs">coef. ${m.coefficient}</span></td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editMatiere(${m.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteMatiere(${m.id})"><i class="fas fa-trash"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function openAddModal() {
        document.getElementById('matiereForm').reset();
        document.getElementById('matiere_id').value = '';
        document.getElementById('modalTitle').textContent = 'Ajouter une Matière';
        $('#matiereModal').modal('show');
    }

    function editMatiere(id) {
        const matiere = matieresData.find(m => m.id == id);
        if (matiere) {
            document.getElementById('matiere_id').value = matiere.id;
            document.getElementById('classe_id').value = matiere.classe_id || '';
            document.getElementById('matiere_nom').value = matiere.nom;
            document.getElementById('matiere_coefficient').value = matiere.coefficient;
            document.getElementById('modalTitle').textContent = 'Modifier la Matière';
            $('#matiereModal').modal('show');
        }
    }

    async function deleteMatiere(id) {
        if (await confirmDelete()) {
            const res = await apiCall('/matieres/delete.php', 'POST', { id });
            if (res.status === 'success') {
                showAlert('success', 'Supprimé', res.message);
                loadMatieres();
            } else {
                showAlert('error', 'Erreur', res.message);
            }
        }
    }

    async function confirmDelete() {
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Supprimer cette matière affectera les notes associées.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        });
        return result.isConfirmed;
    }
    
    document.getElementById('matiereForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const endpoint = data.id ? '/matieres/update.php' : '/matieres/create.php';
        
        const res = await apiCall(endpoint, 'POST', data);
        if (res.status === 'success') {
            $('#matiereModal').modal('hide');
            showAlert('success', 'Réussi', res.message);
            loadMatieres();
        } else {
             showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSelectData();
        loadMatieres();
    });
</script>

<style>
.bg-primary-light { background-color: rgba(92, 103, 242, 0.05); }
</style>
