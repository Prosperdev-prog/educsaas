<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-dark fw-bold">Gestion des Licences & Abonnements</h2>
                <p class="text-muted mb-0">Suivez l'état des abonnements SaaS par établissement.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light text-uppercase small letter-spacing-1">
                                    <tr>
                                        <th>École</th>
                                        <th>Statut</th>
                                        <th>Expiration</th>
                                        <th>Dernier Commentaire</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="licences-list">
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

<!-- Modal Prolongation / Statut -->
<div class="modal fade" id="licenseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="licenseModalTitle">Gérer la Licence</h5>
        <button type="button" class="btn-close" data-dismiss="modal"></button>
      </div>
      <form id="licenseForm">
          <input type="hidden" name="school_id" id="modal_school_id">
          <input type="hidden" name="action" id="modal_action">
          <div class="modal-body p-4">
              <div id="prolongation_fields" style="display:none;">
                  <label class="form-label fw-bold small text-uppercase">Durée de prolongation</label>
                  <select class="form-select rounded-pill mb-3" name="days" id="modal_days">
                      <option value="30">30 Jours (Mensuel - 5 000 FCFA)</option>
                      <option value="365">365 Jours (Annuel - 50 000 FCFA)</option>
                  </select>
              </div>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase">Commentaire pour l'admin</label>
                  <textarea class="form-control rounded-4" name="comment" id="modal_comment" rows="3" placeholder="Expliquez la raison..."></textarea>
              </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btn-submit-license">Confirmer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
async function loadLicences() {
    const res = await apiCall('/superadmin/read_schools.php');
    const tbody = document.getElementById('licences-list');
    tbody.innerHTML = '';
    
    if(res.status === 'success' && res.data.length > 0) {
        res.data.forEach(s => {
            let statusBadge = '';
            let actionBtn = '';
            
            if (s.license_status === 'suspended') {
                statusBadge = '<span class="badge bg-dark rounded-pill px-3">Suspendu</span>';
                actionBtn = `<button class="btn btn-sm btn-success rounded-pill px-3 me-2" onclick="openModal(${s.id}, 'activer')">Activer</button>`;
            } else {
                statusBadge = s.license_status === 'active' ? '<span class="badge bg-success rounded-pill px-3">Actif</span>' : '<span class="badge bg-danger rounded-pill px-3">Expiré</span>';
                actionBtn = `<button class="btn btn-sm btn-outline-danger rounded-pill px-3 me-2" onclick="openModal(${s.id}, 'desactiver')">Désactiver</button>`;
            }

            tbody.innerHTML += `
                <tr>
                    <td class="fw-bold text-dark">${s.name}</td>
                    <td>${statusBadge}</td>
                    <td><i class="fas fa-calendar-alt text-muted me-2"></i>${s.license_expiry || 'Non activée'}</td>
                    <td><small class="text-muted">${s.status_comment || '-'}</small></td>
                    <td class="text-end">
                        ${actionBtn}
                        <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openModal(${s.id}, 'prolonger')"><i class="fas fa-sync-alt me-2"></i>Prolonger</button>
                    </td>
                </tr>
            `;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5">Aucune donnée de licence.</td></tr>';
    }
}

function openModal(schoolId, action) {
    document.getElementById('modal_school_id').value = schoolId;
    document.getElementById('modal_action').value = action;
    document.getElementById('modal_comment').value = '';
    
    const title = document.getElementById('licenseModalTitle');
    const prolongFields = document.getElementById('prolongation_fields');
    
    if (action === 'prolonger') {
        title.textContent = 'Prolonger l\'abonnement';
        prolongFields.style.display = 'block';
    } else if (action === 'desactiver') {
        title.textContent = 'Désactiver l\'établissement';
        prolongFields.style.display = 'none';
    } else {
        title.textContent = 'Réactiver l\'établissement';
        prolongFields.style.display = 'none';
    }
    
    $('#licenseModal').modal('show');
}

document.getElementById('licenseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    const res = await apiCall('/superadmin/update_school_status.php', 'POST', data);
    
    if (res.status === 'success') {
        $('#licenseModal').modal('hide');
        showAlert('success', 'Réussi', res.message);
        loadLicences();
    } else {
        showAlert('error', 'Erreur', res.message);
    }
});

document.addEventListener('DOMContentLoaded', loadLicences);
</script>

<?php include '../../includes/footer.php'; ?>
