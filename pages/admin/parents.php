<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Gestion des Parents</h1>
                <p class="text-muted small">Suivez les tuteurs et facilitez leur accès aux données de leurs enfants.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddParentModal()">
                 <i class="fas fa-user-plus me-2"></i>Nouveau Parent
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
                                        <th class="ps-4">Parents</th>
                                        <th>Email / Identifiant</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="parents-table-body" class="border-top-0">
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

<!-- Modal Parent -->
<div class="modal fade" id="parentModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold" id="parentModalLabel">Nouveau Parent</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="parentForm">
          <input type="hidden" name="id" id="parent_id">
          <div class="modal-body p-4">
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Nom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="nom" id="parent_nom" required placeholder="Dupont">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Prénom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="prenom" id="parent_prenom" required placeholder="Paul">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Email Personnel</label>
                  <input type="email" class="form-control rounded-pill px-3" name="email" id="parent_email" required placeholder="parent@domaine.com">
              </div>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase" id="passwordLabel">Mot de passe</label>
                  <div class="input-group">
                      <input type="password" class="form-control rounded-start-pill px-3 border-end-0" name="password" id="parent_pass" placeholder="••••••••">
                      <span class="input-group-text bg-white border-start-0 rounded-end-pill pe-3 c-pointer toggle-password" data-target="parent_pass">
                          <i class="fas fa-eye text-muted"></i>
                      </span>
                  </div>
                  <small class="text-muted" id="passwordHelp">Laissez vide pour ne pas modifier.</small>
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Valider</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.bg-parent-light { background-color: rgba(255, 193, 7, 0.05); }
.text-parent { color: #ffc107; }
</style>

<script>
    let parentsData = [];

    async function loadParents() {
        const res = await apiCall('/parents/read.php');
        const tbody = document.getElementById('parents-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            parentsData = res.data;
            if(parentsData.length === 0) {
                 tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">Aucun parent enregistré.</td></tr>';
                 return;
            }
            parentsData.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-parent-light text-parent p-2 rounded-circle me-3" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-friends"></i>
                                </div>
                                <h6 class="fw-bold mb-0">${p.nom} ${p.prenom}</h6>
                            </div>
                        </td>
                        <td><span class="text-dark fw-bold">${p.email}</span></td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editParent(${p.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteParent(${p.id})"><i class="fas fa-trash"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function openAddParentModal() {
        document.getElementById('parentForm').reset();
        document.getElementById('parent_id').value = '';
        document.getElementById('parentModalLabel').textContent = 'Nouveau Parent';
        document.getElementById('parent_password').required = true;
        document.getElementById('passwordHelp').style.display = 'none';
        $('#parentModal').modal('show');
    }

    function editParent(id) {
        const parent = parentsData.find(p => p.id == id);
        if (parent) {
            document.getElementById('parentModalLabel').textContent = 'Modifier Parent';
            document.getElementById('parent_id').value = parent.id;
            document.getElementById('parent_nom').value = parent.nom;
            document.getElementById('parent_prenom').value = parent.prenom;
            document.getElementById('parent_email').value = parent.email;
            document.getElementById('parent_password').required = false;
            document.getElementById('passwordHelp').style.display = 'block';
            $('#parentModal').modal('show');
        }
    }
    
    document.getElementById('parentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const endpoint = data.id ? '/parents/update.php' : '/parents/create.php';
        
        const res = await apiCall(endpoint, 'POST', data);
        if (res.status === 'success') {
            $('#parentModal').modal('hide');
            this.reset();
            showAlert('success', 'Confirmé', res.message);
            loadParents();
        } else {
             showAlert('error', 'Erreur', res.message);
        }
    });

    async function deleteParent(id) {
        if (await confirmDelete()) {
            const res = await apiCall('/parents/delete.php', 'POST', { id });
            if (res.status === 'success') {
                showAlert('success', 'Supprimé', res.message);
                loadParents();
            } else {
                showAlert('error', 'Erreur', res.message);
            }
        }
    }

    async function confirmDelete() {
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action supprimera également l'accès de ce parent.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        });
        return result.isConfirmed;
    }

    document.addEventListener('DOMContentLoaded', loadParents);
</script>
