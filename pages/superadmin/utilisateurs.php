<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark fw-bold">Gestion des Utilisateurs Globaux</h2>
            <div class="btn-group shadow-sm">
                <button class="btn btn-outline-primary active" id="btn-all" onclick="filterUsers('all')">Tous</button>
                <button class="btn btn-outline-primary" id="btn-admins" onclick="filterUsers('admin')">Admins Uniquement</button>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Profil</th>
                                <th>Rôle</th>
                                <th>École #ID</th>
                                <th>Statut</th>
                                <th>Inscrit le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-list">
                            <!-- JS Filled -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Statut Utilisateur -->
<div class="modal fade" id="userStatusModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="userModalTitle">Changer le statut</h5>
        <button type="button" class="btn-close" data-dismiss="modal"></button>
      </div>
      <form id="userStatusForm">
          <input type="hidden" name="user_id" id="modal_user_id">
          <input type="hidden" name="is_active" id="modal_is_active">
          <div class="modal-body p-4">
              <p class="text-muted small" id="userModalText">Expliquez la raison de cette action à l'utilisateur.</p>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase">Commentaire / Justification</label>
                  <textarea class="form-control rounded-4" name="comment" id="modal_user_comment" rows="3" placeholder="Ex: Non respect des conditions..."></textarea>
              </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Confirmer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
let allUsers = [];
let currentFilter = 'all';

async function loadAllUsers() {
    const res = await apiCall('/superadmin/read_users.php');
    if(res.status === 'success') {
        allUsers = res.data;
        renderUsers();
    }
}

function renderUsers() {
    const tbody = document.getElementById('users-list');
    tbody.innerHTML = '';
    
    const filtered = currentFilter === 'all' ? allUsers : allUsers.filter(u => u.role === 'admin' || u.role === 'superadmin');

    filtered.forEach(u => {
        let roleBadge = '';
        switch(u.role) {
            case 'admin': roleBadge = '<span class="badge bg-primary px-3 rounded-pill">Admin</span>'; break;
            case 'superadmin': roleBadge = '<span class="badge bg-dark px-3 rounded-pill">S-Admin</span>'; break;
            case 'enseignant': roleBadge = '<span class="badge bg-success px-3 rounded-pill">Enseignant</span>'; break;
            case 'eleve': roleBadge = '<span class="badge bg-info px-3 rounded-pill">Élève</span>'; break;
            case 'parent': roleBadge = '<span class="badge bg-warning px-3 rounded-pill">Parent</span>'; break;
        }

        const statusBadge = u.is_active == 1 ? '<span class="badge bg-soft-success text-success">Actif</span>' : '<span class="badge bg-soft-danger text-danger">Bloqué</span>';
        const actionBtn = u.is_active == 1 ? 
            `<button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="openUserModal(${u.id}, 0)">Désactiver</button>` :
            `<button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="openUserModal(${u.id}, 1)">Activer</button>`;

        tbody.innerHTML += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="bg-light text-primary p-2 rounded-circle me-3" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                            ${u.nom.charAt(0)}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold small">${u.nom} ${u.prenom}</h6>
                            <div class="text-muted" style="font-size: 0.7rem;">${u.email}</div>
                        </div>
                    </div>
                </td>
                <td>${roleBadge}</td>
                <td><span class="badge bg-light text-dark">#${u.school_id}</span></td>
                <td>${statusBadge}</td>
                <td class="small text-muted">${new Date(u.created_at).toLocaleDateString()}</td>
                <td class="text-end">
                    ${u.role !== 'superadmin' ? actionBtn : ''}
                </td>
            </tr>
        `;
    });
}

function filterUsers(type) {
    currentFilter = type;
    document.getElementById('btn-all').classList.toggle('active', type === 'all');
    document.getElementById('btn-admins').classList.toggle('active', type === 'admin');
    renderUsers();
}

function openUserModal(userId, newState) {
    document.getElementById('modal_user_id').value = userId;
    document.getElementById('modal_is_active').value = newState;
    document.getElementById('modal_user_comment').value = '';
    document.getElementById('userModalTitle').textContent = newState == 1 ? 'Réactiver l\'utilisateur' : 'Désactiver l\'utilisateur';
    $('#userStatusModal').modal('show');
}

document.getElementById('userStatusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));
    const res = await apiCall('/superadmin/update_user_status.php', 'POST', data);
    
    if (res.status === 'success') {
        $('#userStatusModal').modal('hide');
        showAlert('success', 'Réussi', res.message);
        loadAllUsers();
    } else {
        showAlert('error', 'Erreur', res.message);
    }
});

document.addEventListener('DOMContentLoaded', loadAllUsers);
</script>

<style>
.bg-soft-success { background-color: rgba(46, 204, 113, 0.1); }
.bg-soft-danger { background-color: rgba(231, 76, 60, 0.1); }
</style>

<?php include '../../includes/footer.php'; ?>
