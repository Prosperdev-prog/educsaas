<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Gestion des Établissements</h1>
                <p class="text-muted small">Supervisez les écoles inscrites sur votre plateforme SaaS.</p>
             </div>
             <button class="btn btn-dark rounded-pill px-4 shadow-sm" data-toggle="modal" data-target="#schoolModal">
                 <i class="fas fa-plus me-2"></i>Inscrire une École
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
                                        <th class="ps-4">Institution</th>
                                        <th>Admin Manager</th>
                                        <th>Date Inscription</th>
                                        <th>Status Licence</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="schools-table-body" class="border-top-0">
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

<!-- Modal School -->
<div class="modal fade" id="schoolModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold">Nouvelle Instance École</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="schoolForm">
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Nom de l'Établissement</label>
                  <input type="text" class="form-control rounded-pill px-4" name="school_name" required placeholder="Lycée X">
              </div>
              <div class="row mb-3">
                  <div class="col-6">
                      <label class="form-label fw-bold small text-uppercase">Prénom Admin</label>
                      <input type="text" class="form-control rounded-pill px-4" name="admin_prenom" required placeholder="Jean">
                  </div>
                  <div class="col-6">
                      <label class="form-label fw-bold small text-uppercase">Nom Admin</label>
                      <input type="text" class="form-control rounded-pill px-4" name="admin_nom" required placeholder="Dupont">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Email Authentification</label>
                  <input type="email" class="form-control rounded-pill px-4" name="email" required placeholder="admin@ecole.com">
              </div>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase">Mot de passe provisoire</label>
                  <input type="password" class="form-control rounded-pill px-4" name="password" required placeholder="••••••••">
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm">Créer l'espace</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.school-avatar { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #f8f9fa; border: 1px solid #eee; }
</style>

<script>
    async function loadSchools() {
        const res = await apiCall('/superadmin/read_schools.php');
        const tbody = document.getElementById('schools-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            if(res.data.length === 0){
                 tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5">Aucune école.</td></tr>';
                 return;
            }
            res.data.forEach(s => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="school-avatar me-3">
                                    <i class="fas fa-university text-dark"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">${s.name}</h6>
                            </div>
                        </td>
                        <td><div class="fw-bold mb-0 small">${s.admin_nom} ${s.admin_prenom}</div><div class="text-muted small">${s.admin_email}</div></td>
                        <td><span class="small text-muted">${new Date().toLocaleDateString()}</span></td>
                        <td><span class="badge ${s.license_status === 'active' ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger'} rounded-pill px-3 py-2 fw-bold text-uppercase small-font border">
                            ${s.license_status || 'Trial'}
                        </span></td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-success rounded-pill px-3 me-2" onclick="activateSchool(${s.id})">
                                    <i class="fas fa-check me-1"></i> Activer
                                </button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger"><i class="fas fa-power-off"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    async function activateSchool(id) {
        if (!confirm('Voulez-vous vraiment activer la licence PREMIUM pour cette école ?')) return;
        const res = await apiCall('/superadmin/activate_license.php', 'POST', { school_id: id });
        if (res.status === 'success') {
            showAlert('success', 'Succès', res.message);
            loadSchools();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    }
    
    document.getElementById('schoolForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const res = await apiCall('/superadmin/create_school.php', 'POST', Object.fromEntries(new FormData(this)));
        if (res.status === 'success') {
            $('#schoolModal').modal('hide');
            this.reset();
            showAlert('success', 'Félicitations', 'L\'école et son administrateur ont été créés avec succès.');
            loadSchools();
        } else {
             showAlert('error', 'Oups', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', loadSchools);
</script>

