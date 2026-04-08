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
                <h1 class="h3 mb-0 fw-bold">Base Élèves</h1>
                <p class="text-muted small">Inscrivez et suivez le dossier pédagogique de vos apprenants.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddEleveModal()">
                 <i class="fas fa-plus me-2"></i>Inscrire un élève
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
                                        <th class="ps-4">Identité & Matricule</th>
                                        <th>Sexe</th>
                                        <th>Classe</th>
                                        <th>Parents / Tuteurs</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="eleves-table-body" class="border-top-0">
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

<!-- Modal Élève -->
<div class="modal fade" id="eleveModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold" id="eleveModalLabel">Nouvel Élève</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="eleveForm">
          <div class="modal-body p-4">
              <input type="hidden" name="id" id="eleve_id">
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Matricule Unique</label>
                      <input type="text" class="form-control rounded-pill px-3" name="matricule" id="matricule" required placeholder="Ex: MAT-2025-001">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Date de naissance</label>
                      <input type="date" class="form-control rounded-pill px-3" name="date_naissance" id="date_naissance">
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Nom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="nom" id="nom" required placeholder="Diallo">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Prénom</label>
                      <input type="text" class="form-control rounded-pill px-3" name="prenom" id="prenom" required placeholder="Moussa">
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Sexe</label>
                      <select class="form-select rounded-pill px-3" name="sexe" id="sexe">
                          <option value="M">Masculin</option>
                          <option value="F">Féminin</option>
                      </select>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Classe Assignée</label>
                      <select class="form-select rounded-pill px-3" name="classe_id" id="classe_id" required>
                          <option value="">Sélectionner une classe...</option>
                      </select>
                  </div>
              </div>
              
              <hr class="my-4 opacity-50">
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <h6 class="fw-bold mb-2">Informations Tuteur</h6>
                      <label class="form-label fw-bold small text-uppercase">Nom du Parent</label>
                      <input type="text" class="form-control rounded-pill px-3" name="nom_parent" id="nom_parent" placeholder="Ex: Jean Diallo">
                  </div>
                  <div class="col-md-6 mb-3 align-self-end">
                      <label class="form-label fw-bold small text-uppercase">Téléphone Parent</label>
                      <input type="text" class="form-control rounded-pill px-3" name="telephone_parent" id="telephone_parent">
                  </div>
              </div>
              
              <div class="bg-primary-light p-3 rounded mb-0 mt-2">
                   <label class="form-label fw-bold small text-uppercase text-primary">Lier à un compte parent existant</label>
                   <select class="form-select rounded-pill px-3" name="parent_id" id="parent_id">
                       <option value="">-- Aucun compte parent associé --</option>
                   </select>
                   <small class="text-muted d-block mt-1">Permet au parent de voir les notes de cet élève sur son portail.</small>
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btn-save">Valider</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.bg-primary-light { background-color: rgba(92, 103, 242, 0.08); }
.avatar-soft { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: bold; }
</style>

<script>
    let elevesData = [];

    async function loadSelectData() {
        const [clsRes, pRes] = await Promise.all([
            apiCall('/classes/read.php'),
            apiCall('/parents/read.php')
        ]);
        
        const selCls = document.getElementById('classe_id');
        selCls.innerHTML = '<option value="">Sélectionner une classe...</option>';
        if (clsRes.status === 'success') {
            clsRes.data.forEach(c => { selCls.innerHTML += `<option value="${c.id}">${c.nom}</option>`; });
        }
        
        const selParent = document.getElementById('parent_id');
        selParent.innerHTML = '<option value="">-- Aucun compte parent associé --</option>';
        if (pRes.status === 'success') {
            pRes.data.forEach(p => { selParent.innerHTML += `<option value="${p.id}">${p.nom} ${p.prenom} (${p.email})</option>`; });
        }
    }
    
    async function loadEleves() {
        const res = await apiCall('/eleves/read.php');
        if (res.status === 'success') {
            elevesData = res.data;
            renderTable();
        }
    }

    function renderTable() {
        const tbody = document.getElementById('eleves-table-body');
        tbody.innerHTML = '';
        elevesData.forEach(e => {
            tbody.innerHTML += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-soft bg-light text-primary me-3 border">
                                ${e.nom.charAt(0)}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">${e.nom} ${e.prenom}</h6>
                                <small class="text-muted"><i class="fas fa-id-card me-1 small"></i> ${e.matricule}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge ${e.sexe === 'F' ? 'bg-soft-danger text-danger' : 'bg-soft-info text-info'} rounded-pill px-3">${e.sexe === 'F' ? 'Féminin' : 'Masculin'}</span></td>
                    <td><span class="fw-bold text-dark"><i class="fas fa-chalkboard text-muted me-1 small"></i> ${e.classe_nom || '-'}</span></td>
                    <td><div class="small fw-bold">${e.nom_parent || 'Non spécifié'}</div><div class="small text-muted">${e.telephone_parent || ''}</div></td>
                    <td class="text-end pe-4">
                         <div class="btn-group">
                            <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editEleve(${e.id})" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteEleve(${e.id})" title="Supprimer"><i class="fas fa-trash"></i></button>
                         </div>
                    </td>
                </tr>
            `;
        });
    }

    function openAddEleveModal() {
        document.getElementById('eleveForm').reset();
        document.getElementById('eleve_id').value = '';
        document.getElementById('eleveModalLabel').textContent = 'Nouvel Élève';
        $('#eleveModal').modal('show');
    }

    function editEleve(id) {
        const eleve = elevesData.find(e => e.id == id);
        if (eleve) {
            document.getElementById('eleveModalLabel').textContent = 'Modifier Élève';
            document.getElementById('eleve_id').value = eleve.id;
            document.getElementById('matricule').value = eleve.matricule;
            document.getElementById('nom').value = eleve.nom;
            document.getElementById('prenom').value = eleve.prenom;
            document.getElementById('sexe').value = eleve.sexe;
            document.getElementById('date_naissance').value = eleve.date_naissance;
            document.getElementById('classe_id').value = eleve.classe_id;
            document.getElementById('nom_parent').value = eleve.nom_parent || '';
            document.getElementById('telephone_parent').value = eleve.telephone_parent || '';
            document.getElementById('parent_id').value = eleve.parent_id || '';
            $('#eleveModal').modal('show');
        }
    }

    async function deleteEleve(id) {
        if (await confirmDelete()) {
            const res = await apiCall('/eleves/delete.php', 'POST', { id });
            if (res.status === 'success') {
                showAlert('success', 'Supprimé', res.message);
                loadEleves();
            } else {
                showAlert('error', 'Erreur', res.message);
            }
        }
    }

    async function confirmDelete() {
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            border: 'none',
            borderRadius: '15px'
        });
        return result.isConfirmed;
    }

    document.getElementById('eleveForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const endpoint = data.id ? '/eleves/update.php' : '/eleves/create.php';
        
        const res = await apiCall(endpoint, 'POST', data);
        if (res.status === 'success') {
            $('#eleveModal').modal('hide');
            this.reset();
            showAlert('success', 'Confirmé', res.message);
            loadEleves();
        } else {
             showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSelectData();
        loadEleves();
    });
</script>
