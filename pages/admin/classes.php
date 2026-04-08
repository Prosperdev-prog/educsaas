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
                <h1 class="h3 mb-0 fw-bold">Gestion des Classes</h1>
                <p class="text-muted small">Organisez les niveaux et les salles de classe de votre établissement.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddModal()">
                 <i class="fas fa-plus me-2"></i>Nouvelle Classe
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
                                        <th class="ps-4">#ID</th>
                                        <th>Nom de la Classe</th>
                                        <th>Niveau Enseignement</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="classes-table-body" class="border-top-0">
                                    <!-- Remplissage JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Classe -->
<div class="modal fade" id="classeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold" id="modalTitle">Ajouter une Classe</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="classeForm">
          <input type="hidden" name="id" id="classe_id">
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Nom de la Classe</label>
                  <input type="text" class="form-control rounded-pill px-3" name="nom" id="classe_nom" required placeholder="Ex: 6ème B, Terminale C">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Cycle / Niveau</label>
                  <select class="form-select rounded-pill px-3" name="niveau" id="classe_niveau" required>
                      <option value="Primaire">Primaire (Maternelle-CM2)</option>
                      <option value="Secondaire">Secondaire (6ème-Terminale)</option>
                      <option value="Supérieur">Enseignement Supérieur</option>
                  </select>
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
    let classesData = [];

    async function loadClasses() {
        const res = await apiCall('/classes/read.php');
        const tbody = document.getElementById('classes-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            classesData = res.data;
            if(classesData.length === 0){
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted h5">Aucune classe enregistrée.</td></tr>';
                return;
            }
            classesData.forEach(c => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4 small text-muted">#${c.id}</td>
                        <td><span class="fw-bold">${c.nom}</span></td>
                        <td><span class="badge bg-light text-dark border">${c.niveau || '-'}</span></td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editClasse(${c.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteClasse(${c.id})"><i class="fas fa-trash"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function openAddModal() {
        document.getElementById('classeForm').reset();
        document.getElementById('classe_id').value = '';
        document.getElementById('modalTitle').textContent = 'Ajouter une Classe';
        $('#classeModal').modal('show');
    }

    function editClasse(id) {
        const classe = classesData.find(c => c.id == id);
        if (classe) {
            document.getElementById('classe_id').value = classe.id;
            document.getElementById('classe_nom').value = classe.nom;
            document.getElementById('classe_niveau').value = classe.niveau;
            document.getElementById('modalTitle').textContent = 'Modifier la Classe';
            $('#classeModal').modal('show');
        }
    }

    async function deleteClasse(id) {
        if (!confirm('Voulez-vous vraiment supprimer cette classe ?')) return;
        const res = await apiCall('/classes/delete.php', 'POST', { id });
        if (res.status === 'success') {
            showAlert('success', 'Supprimé', res.message);
            loadClasses();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    }
    
    document.getElementById('classeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const endpoint = data.id ? '/classes/update.php' : '/classes/create.php';
        
        const res = await apiCall(endpoint, 'POST', data);
        if (res.status === 'success') {
            $('#classeModal').modal('hide');
            showAlert('success', 'Réussi', res.message);
            loadClasses();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', loadClasses);
</script>
