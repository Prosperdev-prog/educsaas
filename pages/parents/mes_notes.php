<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<div class="content-body">
    <div class="container-fluid mt-3 text-dark pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
             <div>
                <h1 class="h3 mb-0 fw-bold">Suivi des Notes</h1>
                <p class="text-muted small">Consultez les résultats scolaires de vos enfants.</p>
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
                                        <th class="ps-4">Enfant</th>
                                        <th>Matière</th>
                                        <th>Note / 20</th>
                                        <th>Date</th>
                                        <th class="pe-4 text-end">Commentaire</th>
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

<?php include '../../includes/footer.php'; ?>

<style>
.bg-light-blue { background-color: rgba(92, 103, 242, 0.05); }
.note-badge {
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: bold;
}
.note-excellent { background-color: rgba(0, 184, 148, 0.1); color: #00b894; }
.note-good { background-color: rgba(92, 103, 242, 0.1); color: #5c67f2; }
.note-average { background-color: rgba(241, 196, 15, 0.1); color: #f39c12; }
.note-poor { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; }
</style>

<script>
    async function loadNotes() {
        const res = await apiCall('/notes/read_mes_notes.php');
        if (res.status === 'success') {
            renderTable(res.data);
        }
    }

    function renderTable(data) {
        const tbody = document.getElementById('notes-table-body');
        tbody.innerHTML = '';
        
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">Aucune note disponible pour le moment.</td></tr>';
            return;
        }

        data.forEach(n => {
            let noteClass = 'note-average';
            if (n.note >= 16) noteClass = 'note-excellent';
            else if (n.note >= 12) noteClass = 'note-good';
            else if (n.note < 10) noteClass = 'note-poor';

            tbody.innerHTML += `
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-soft bg-primary text-white me-2" style="width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                ${n.eleve_nom.charAt(0)}
                            </div>
                            <span class="fw-bold">${n.eleve_nom} ${n.eleve_prenom}</span>
                        </div>
                    </td>
                    <td>
                        <span class="text-dark fw-bold">${n.matiere_nom}</span>
                        <div class="small text-muted text-capitalize">${n.type}</div>
                    </td>
                    <td>
                        <span class="note-badge ${noteClass}">${n.note}</span>
                    </td>
                    <td><i class="far fa-calendar-alt text-muted me-2"></i>${n.date_evaluation}</td>
                    <td class="pe-4 text-end text-muted small italic">${n.commentaire || '-'}</td>
                </tr>
            `;
        });
    }

    document.addEventListener('DOMContentLoaded', loadNotes);
</script>
