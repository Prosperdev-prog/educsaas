<?php 
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'enseignant', 'superadmin'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Saisie des Notes</h1>
                <p class="text-muted small">Enregistrez et gérez les notes par élève.</p>
             </div>
             <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="openAddNoteModal()">
                 <i class="fas fa-plus me-2"></i>Saisir une Note
             </button>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 15px;">
                    <label class="form-label fw-bold small text-uppercase">Filtrer par Élève</label>
                    <select class="form-select rounded-pill px-3" id="filter-eleve" onchange="loadNotes()">
                        <option value="">Tous les élèves</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6" id="moyenne-card" style="display:none;">
                <div class="card border-0 shadow-sm p-3 bg-primary text-white" style="border-radius: 15px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold">MOYENNE GÉNÉRALE</h6>
                            <small class="opacity-75">Basée sur les notes filtrées</small>
                        </div>
                        <h2 class="fw-bold mb-0" id="display-moyenne">0.00</h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light-blue text-dark small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Élève</th>
                                        <th>Matière</th>
                                        <th>Note / 20</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="notes-table-body" class="border-top-0">
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

<!-- Modal Note -->
<div class="modal fade" id="noteModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
      <div class="modal-header border-0 pb-0 px-4 pt-4">
        <h4 class="modal-title fw-bold" id="noteModalLabel">Saisir une Note</h4>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="noteForm">
          <input type="hidden" name="id" id="note_id">
          <div class="modal-body p-4">
              <div class="mb-3" id="eleve-select-div">
                  <label class="form-label fw-bold small text-uppercase">Élève</label>
                  <select class="form-select rounded-pill px-3" name="eleve_id" id="modal_eleve_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Matière</label>
                  <select class="form-select rounded-pill px-3" name="matiere_id" id="modal_matiere_id" required>
                      <!-- Options -->
                  </select>
              </div>
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Note / 20</label>
                      <input type="number" step="0.25" min="0" max="20" class="form-control rounded-pill px-3" name="note" id="modal_note" required>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold small text-uppercase">Type</label>
                      <select class="form-select rounded-pill px-3" name="type" id="modal_type">
                          <option value="examen">Examen</option>
                          <option value="devoir">Devoir</option>
                          <option value="participation">Participation</option>
                      </select>
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold small text-uppercase">Date d'évaluation</label>
                  <input type="date" class="form-control rounded-pill px-3" name="date_evaluation" id="modal_date" value="<?= date('Y-m-d') ?>">
              </div>
              <div class="mb-0">
                  <label class="form-label fw-bold small text-uppercase">Commentaire</label>
                  <textarea class="form-control rounded-4 px-3" name="commentaire" id="modal_commentaire" rows="2"></textarea>
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
    let notesData = [];

    async function loadSelectData() {
        const [eRes, mRes] = await Promise.all([
            apiCall('/eleves/read.php'),
            apiCall('/matieres/read.php')
        ]);
        
        const filterEleve = document.getElementById('filter-eleve');
        const modalEleve = document.getElementById('modal_eleve_id');
        const modalMatiere = document.getElementById('modal_matiere_id');

        if (eRes.status === 'success') {
            eRes.data.forEach(e => {
                const opt = `<option value="${e.id}">${e.nom} ${e.prenom} (${e.matricule})</option>`;
                filterEleve.innerHTML += opt;
                modalEleve.innerHTML += opt;
            });
        }
        
        if (mRes.status === 'success') {
            mRes.data.forEach(m => {
                modalMatiere.innerHTML += `<option value="${m.id}">${m.nom} (${m.classe_nom || 'Général'})</option>`;
            });
        }
    }

    async function loadNotes() {
        const eleveId = document.getElementById('filter-eleve').value;
        const res = await apiCall('/notes/read.php' + (eleveId ? '?eleve_id=' + eleveId : ''));
        const tbody = document.getElementById('notes-table-body');
        tbody.innerHTML = '';
        
        if (res.status === 'success') {
            notesData = res.data;
            
            if (eleveId && res.moyenne_generale !== null) {
                document.getElementById('moyenne-card').style.display = 'block';
                document.getElementById('display-moyenne').textContent = res.moyenne_generale;
            } else {
                document.getElementById('moyenne-card').style.display = 'none';
            }

            if(notesData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">Aucune note trouvée.</td></tr>';
                return;
            }

            notesData.forEach(n => {
                tbody.innerHTML += `
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-light text-primary p-2 rounded-circle me-3" style="width:35px;height:35px;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:bold;">
                                    ${n.eleve_nom.charAt(0)}${n.eleve_prenom.charAt(0)}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${n.eleve_nom} ${n.eleve_prenom}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="fw-bold">${n.matiere_nom}</span></td>
                        <td><span class="badge bg-light text-primary border border-primary px-3 rounded-pill fw-bold h5 mb-0">${n.note}</span></td>
                        <td><span class="badge bg-soft-info text-info text-capitalize px-3 rounded-pill">${n.type}</span></td>
                        <td><i class="far fa-calendar-alt text-muted me-2 small"></i>${n.date_evaluation}</td>
                        <td class="text-end pe-4">
                             <div class="btn-group">
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs me-2" onclick="editNote(${n.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-light rounded-circle shadow-xs text-danger" onclick="deleteNote(${n.id})"><i class="fas fa-trash"></i></button>
                             </div>
                        </td>
                    </tr>
                `;
            });
        }
    }

    function openAddNoteModal() {
        document.getElementById('noteForm').reset();
        document.getElementById('note_id').value = '';
        document.getElementById('eleve-select-div').style.display = 'block';
        document.getElementById('noteModalLabel').textContent = 'Saisir une Note';
        $('#noteModal').modal('show');
    }

    function editNote(id) {
        const note = notesData.find(n => n.id == id);
        if (note) {
            document.getElementById('note_id').value = note.id;
            document.getElementById('modal_eleve_id').value = note.eleve_id;
            document.getElementById('eleve-select-div').style.display = 'none'; // Lock student on edit
            document.getElementById('modal_matiere_id').value = note.matiere_id;
            document.getElementById('modal_note').value = note.note;
            document.getElementById('modal_type').value = note.type;
            document.getElementById('modal_date').value = note.date_evaluation;
            document.getElementById('modal_commentaire').value = note.commentaire || '';
            document.getElementById('noteModalLabel').textContent = 'Modifier la Note';
            $('#noteModal').modal('show');
        }
    }

    async function deleteNote(id) {
        if (await confirmDelete()) {
            const res = await apiCall('/notes/delete.php', 'POST', { id });
            if (res.status === 'success') {
                showAlert('success', 'Supprimé', res.message);
                loadNotes();
            } else {
                showAlert('error', 'Erreur', res.message);
            }
        }
    }

    async function confirmDelete() {
        const result = await Swal.fire({
            title: 'Supprimer cette note ?',
            text: "Cette action affectera la moyenne de l'élève.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        });
        return result.isConfirmed;
    }

    document.getElementById('noteForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this));
        const endpoint = data.id ? '/notes/update.php' : '/notes/create.php';
        
        const res = await apiCall(endpoint, 'POST', data);
        if (res.status === 'success') {
            $('#noteModal').modal('hide');
            showAlert('success', 'Réussi', res.message);
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

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.bg-soft-info { background-color: rgba(0, 184, 148, 0.1); }
</style>
