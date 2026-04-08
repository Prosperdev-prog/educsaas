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
                <h1 class="h3 mb-0 fw-bold">Gestion du Corps Enseignant</h1>
                <p class="text-muted small">Pilotez votre équipe pédagogique et leurs accès.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-toggle="modal" data-target="#enseignantModal">
                 <i class="fas fa-user-plus me-2"></i>Nouveau Professeur
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
                                        <th class="ps-4">Enseignant</th>
                                        <th>Email / Identifiant</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="enseignants-table-body" class="border-top-0">
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

<!-- Modal Enseignant -->
<div class="modal fade" id="enseignantModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold">Compte Enseignant</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="enseignantForm">
          <div class="modal-body p-4">
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Nom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="nom" required placeholder="Dupont">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Prénom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="prenom" required placeholder="Jean">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Email Professionnel</label>
                  <input type="email" class="form-control rounded-pill px-3" name="email" required placeholder="prof@ecole.com">
              </div>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase">Mot de passe provisoire</label>
                  <div class="input-group">
                      <input type="password" class="form-control rounded-start-pill px-3 border-end-0" name="password" id="prof_pass" required placeholder="••••••••">
                      <span class="input-group-text bg-white border-start-0 rounded-end-pill pe-3 c-pointer toggle-password" data-target="prof_pass">
                          <i class="fas fa-eye text-muted"></i>
                      </span>
                  </div>
                  <small class="text-muted">L'enseignant pourra le modifier après connexion.</small>
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Créer le compte</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.bg-primary-light { background-color: rgba(92, 103, 242, 0.1); }
</style>

<script>
    let isEditing = false;
    let currentEditId = null;

    async function loadEnseignants() {
        const res = await apiCall('/enseignants/read.php');
        const tbody = document.getElementById('enseignants-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            if(res.data.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Aucun enseignant enregistré.</td></tr>';
                 return;
            }
            res.data.forEach(e => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-light text-primary p-2 rounded-circle me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">${e.nom} ${e.prenom}</h6>
                                    <small class="text-muted">ID: #${e.id}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-dark">${e.email}</span></td>
                        <td><span class="badge bg-success rounded-pill px-3">Actif</span></td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editEnseignant(${e.id}, '${e.nom}', '${e.prenom}', '${e.email}')"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteEnseignant(${e.id})"><i class="fas fa-trash"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function editEnseignant(id, nom, prenom, email) {
        isEditing = true;
        currentEditId = id;
        document.querySelector('.modal-title').textContent = 'Modifier le compte';
        const form = document.getElementById('enseignantForm');
        form.nom.value = nom;
        form.prenom.value = prenom;
        form.email.value = email;
        form.password.required = false; // Mot de passe facultatif en édition
        $('#enseignantModal').modal('show');
    }
    
    document.getElementById('enseignantForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        
        let res;
        if (isEditing) {
            data.id = currentEditId;
            res = await apiCall('/enseignants/update.php', 'POST', data);
        } else {
            res = await apiCall('/enseignants/create.php', 'POST', data);
        }

        if (res.status === 'success') {
            $('#enseignantModal').modal('hide');
            this.reset();
            isEditing = false;
            showAlert('success', 'Réussi', res.message);
            loadEnseignants();
        } else {
             showAlert('error', 'Erreur', res.message);
        }
    });

    async function deleteEnseignant(id) {
        if (!confirm('Voulez-vous vraiment supprimer ce professeur ?')) return;
        const res = await apiCall('/enseignants/delete.php', 'POST', { id });
        if (res.status === 'success') {
            showAlert('success', 'Supprimé', res.message);
            loadEnseignants();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    }

    // Reset form when modal closed
    $('#enseignantModal').on('hidden.bs.modal', function () {
        isEditing = false;
        document.getElementById('enseignantForm').reset();
        document.querySelector('.modal-title').textContent = 'Compte Enseignant';
        document.getElementById('enseignantForm').password.required = true;
    });

    document.addEventListener('DOMContentLoaded', loadEnseignants);
</script>

