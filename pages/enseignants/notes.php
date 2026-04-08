<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-4 text-dark">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
            <div>
                <h1 class="h3 mb-0 fw-bold">Gestion des Notes</h1>
                <p class="text-muted mb-0">Saisie et suivi des évaluations pour vos classes assignées.</p>
            </div>
            <button class="btn btn-primary rounded-pill shadow-sm px-4" data-toggle="modal" data-target="#noteModal">
                <i class="fas fa-plus me-2 text-white-50"></i> Saisir une évaluation
            </button>
        </div>
        
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Élève (Classe)</th>
                                <th>Matière</th>
                                <th>Note / 20</th>
                                <th>Type</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="notes-table-body">
                            <!-- Remplissage JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Note -->
<div class="modal fade text-dark" id="noteModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0 px-4">
        <h4 class="modal-title fw-bold">Nouvelle Évaluation</h4>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form id="noteForm">
          <div class="modal-body p-4">
              <div class="row">
                  <div class="col-md-7 mb-3">
                      <label class="form-label fw-semibold">Sélectionner l'Élève</label>
                      <select class="form-select rounded-3" name="eleve_id" id="eleve_id" required>
                          <!-- Options filtrées via JS -->
                      </select>
                      <small class="text-muted">Seuls vos élèves assignés apparaissent ici.</small>
                  </div>
                  <div class="col-md-5 mb-3">
                      <label class="form-label fw-semibold">Matière</label>
                      <select class="form-select rounded-3" name="matiere_id" id="matiere_id" required>
                          <!-- Options -->
                      </select>
                  </div>
              </div>
              <div class="row mt-2">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Note / 20</label>
                      <input type="number" step="0.25" class="form-control rounded-3" name="note" required max="20" min="0" placeholder="0.00">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Type d'Évaluation</label>
                      <select class="form-select rounded-3" name="type">
                          <option value="examen">Examen Trimestriel</option>
                          <option value="devoir">Devoir Surveillé</option>
                          <option value="participation">Participation</option>
                      </select>
                  </div>
              </div>
              <div class="mb-0">
                  <label class="form-label fw-semibold">Commentaire (Optionnel)</label>
                  <textarea class="form-control rounded-3" name="commentaire" rows="2" placeholder="Observation sur l'élève..."></textarea>
              </div>
          </div>
          <div class="modal-footer border-0 p-4 pt-0">
            <button type="button" class="btn btn-light rounded-pill" data-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Enregistrer la note</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script>
    async function loadSelectData() {
        const [elevesRes, matieresRes] = await Promise.all([
            apiCall('/enseignants/read_mes_eleves.php'),
            apiCall('/matieres/read.php')
        ]);
        
        const selEleve = document.getElementById('eleve_id');
        if (elevesRes.status === 'success') {
            selEleve.innerHTML = '<option value="">Choisir un élève...</option>';
            elevesRes.data.forEach(e => {
                selEleve.innerHTML += `<option value="${e.id}">${e.nom} ${e.prenom} (${e.classe_nom})</option>`;
            });
        }
        
        const selMatiere = document.getElementById('matiere_id');
        if (matieresRes.status === 'success') {
            selMatiere.innerHTML = '<option value="">Choisir une matière...</option>';
            matieresRes.data.forEach(m => {
                selMatiere.innerHTML += `<option value="${m.id}">${m.nom}</option>`;
            });
        }
    }

    async function loadNotes() {
        const res = await apiCall('/notes/read.php'); // L'API notes read gère déjà le school_id
        if (res.status === 'success') {
            const tbody = document.getElementById('notes-table-body');
            tbody.innerHTML = '';
            if(res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4">Aucune note enregistrée.</td></tr>';
                return;
            }
            res.data.forEach(n => {
                tbody.innerHTML += `
                    <tr>
                        <td class="small text-muted">${n.date_evaluation}</td>
                        <td class="fw-bold text-dark">${n.eleve_nom} ${n.eleve_prenom} <br><small class="badge bg-light text-muted">${n.classe_nom || ''}</small></td>
                        <td><span class="badge bg-light text-primary border border-primary px-2">${n.matiere_nom}</span></td>
                        <td><strong class="${n.note < 10 ? 'text-danger' : 'text-success'} h6 mb-0">${n.note}</strong></td>
                        <td><small class="text-uppercase fw-bold text-secondary">${n.type}</small></td>
                        <td class="text-end">
                             <button class="btn btn-sm btn-light rounded-circle shadow-sm"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    document.getElementById('noteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const res = await apiCall('/notes/create.php', 'POST', data);
        if (res.status === 'success') {
            $('#noteModal').modal('hide');
            this.reset();
            showAlert('success', 'Succès', res.message);
            loadNotes();
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadSelectData();
        loadNotes();
    });
</script>
