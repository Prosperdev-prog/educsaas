<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /saas/index.php'); exit; }
include '../../includes/header.php'; 
include '../../includes/sidebar.php'; 
?>

<!-- Content body start -->
<div class="content-body">
    <div class="container-fluid mt-3 text-dark">
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
            <h1 class="h3 mb-0 text-gray-800">Génération des Bulletins</h1>
        </div>
        
        <div class="card shadow mb-4 d-print-none">
            <div class="card-header bg-white pb-0 border-0">
                <h4 class="card-title">Configuration de l'en-tête</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Nom de l'établissement</label>
                        <input type="text" id="config_ecole" class="form-control" value="Lycée d'Excellence SaaS">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Année Scolaire</label>
                        <input type="text" id="config_annee" class="form-control" value="2023-2024">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Période (Trimestre/Semestre)</label>
                        <input type="text" id="config_periode" class="form-control" value="1er Trimestre">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sélectionner un Élève</label>
                        <select class="form-select form-control" id="eleve_select">
                            <option value="">Choisir un élève...</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 text-end">
                        <button class="btn btn-primary" onclick="generateBulletin()">
                            <i class="fas fa-magic"></i> Générer / Actualiser
                        </button>
                        <button class="btn btn-secondary ms-2" onclick="window.print()" id="btn-print" disabled>
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                        <button class="btn btn-danger ms-2" onclick="downloadPDF()" id="btn-pdf" disabled>
                            <i class="fas fa-file-pdf"></i> Télécharger PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone du Bulletin Imprimable -->
        <div class="card shadow d-none mt-4" id="bulletin-zone">
            <div class="card-body p-5 bg-white text-dark border border-2 border-dark" id="bulletin-content">
                <!-- Résultat JS -->
            </div>
        </div>
    </div>
</div>
<!-- Content body end -->

<?php include '../../includes/footer.php'; ?>

<!-- HTML2PDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* CSS Spécifique pour l'impression du bulletin */
@media print {
    body * {
        visibility: hidden;
    }
    #bulletin-zone, #bulletin-zone * {
        visibility: visible;
    }
    #bulletin-zone {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        box-shadow: none !important;
        border: none !important;
    }
    .d-print-none {
        display: none !important;
    }
}
</style>

<script>
    async function loadEleves() {
        const res = await apiCall('/eleves/read.php');
        if (res.status === 'success') {
            const select = document.getElementById('eleve_select');
            res.data.forEach(e => {
                select.innerHTML += `<option value="${e.id}">${e.nom} ${e.prenom} (${e.matricule})</option>`;
            });
        }
    }

    async function generateBulletin() {
        const eleveId = document.getElementById('eleve_select').value;
        if (!eleveId) {
            showAlert('warning', 'Erreur', 'Veuillez sélectionner un élève');
            return;
        }
        
        // Configuration de l'entête
        const ecoleNom = document.getElementById('config_ecole').value;
        const anneeSco = document.getElementById('config_annee').value;
        const periode = document.getElementById('config_periode').value;
        
        const res = await apiCall(`/bulletins/read.php?eleve_id=${eleveId}`);
        if (res.status === 'success') {
            const data = res.data;
            const bZone = document.getElementById('bulletin-zone');
            const bContent = document.getElementById('bulletin-content');
            
            const eleve = data.eleve;
            const lignes = data.lignes;
            const moyGenerale = data.moyenne_generale;
            
            let dateObj = new Date();
            let dateStr = ("0" + dateObj.getDate()).slice(-2) + "/" + ("0" + (dateObj.getMonth() + 1)).slice(-2) + "/" + dateObj.getFullYear();
            
            let html = `
                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-6">
                        <h2 class="text-uppercase m-0"><strong>${ecoleNom}</strong></h2>
                        <h5 class="text-muted mt-2">Année Scolaire: ${anneeSco}</h5>
                    </div>
                    <div class="col-6 text-end">
                        <h2 class="text-primary text-uppercase m-0">BULLETIN DE NOTES</h2>
                        <h5 class="text-muted mt-2">${periode}</h5>
                    </div>
                </div>
                
                <div class="row mb-4 border p-3 rounded" style="background-color: #f8f9fa;">
                    <div class="col-6">
                        <p class="mb-1"><strong>Nom de l'élève :</strong> ${eleve.nom}</p>
                        <p class="mb-1"><strong>Prénom :</strong> ${eleve.prenom}</p>
                        <p class="mb-0"><strong>Matricule :</strong> ${eleve.matricule}</p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Classe :</strong> ${eleve.classe_nom || 'Non assigné'}</p>
                        <p class="mb-0"><strong>Date d'édition :</strong> ${dateStr}</p>
                    </div>
                </div>

                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Matières</th>
                            <th>Notes Obtenues</th>
                            <th>Moyenne (/20)</th>
                            <th>Coef.</th>
                            <th>Total (Moy x Coef)</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            if (lignes.length === 0) {
                html += `<tr><td colspan="5" class="py-4">Aucune note enregistrée pour cet élève.</td></tr>`;
            } else {
                lignes.forEach(l => {
                    let textNotes = l.notes.join(' ; ');
                    let moyStyle = l.moyenne < 10 ? 'text-danger font-weight-bold' : '';
                    let moy_x_coef = (l.moyenne * l.coefficient).toFixed(2);
                    
                    html += `
                        <tr>
                            <td class="text-start"><strong>${l.matiere}</strong></td>
                            <td class="small text-muted">${textNotes}</td>
                            <td class="${moyStyle}">${l.moyenne}</td>
                            <td>${l.coefficient}</td>
                            <td>${moy_x_coef}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-end mt-4 mb-5">
                    <div class="border p-3 rounded bg-light" style="width: 350px;">
                        <h4 class="mb-0 text-center">
                            Moyenne Générale : 
                            <span class="${moyGenerale < 10 ? 'text-danger' : 'text-success'} ms-2"><strong>${moyGenerale}</strong></span> / 20
                        </h4>
                    </div>
                </div>
                
                <div class="row mt-5 pt-5">
                    <div class="col-6 text-center">
                        <p class="mb-5"><strong>Signature du Parent</strong></p>
                        <p>_____________________</p>
                    </div>
                    <div class="col-6 text-center">
                        <p class="mb-5"><strong>Le Directeur / La Directrice</strong></p>
                        <p>_____________________</p>
                    </div>
                </div>
            `;
            
            bContent.innerHTML = html;
            bZone.classList.remove('d-none');
            document.getElementById('btn-print').disabled = false;
            document.getElementById('btn-pdf').disabled = false;
        } else {
            showAlert('error', 'Erreur', res.message);
        }
    }

    function downloadPDF() {
        const element = document.getElementById('bulletin-content');
        
        // Nom du fichier personnalisé avec le nom de l'élève
        const select = document.getElementById('eleve_select');
        const eleveName = select.options[select.selectedIndex].text;
        const periode = document.getElementById('config_periode').value;
        const filenameStr = `Bulletin_${eleveName}_${periode}.pdf`.replace(/[^a-z0-9]/gi, '_').toLowerCase();

        const opt = {
          margin:       15,
          filename:     filenameStr,
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        // Modification temporaire pour forcer le design sur le PDF (comme bg-light)
        element.style.padding = '20px';
        
        html2pdf().set(opt).from(element).save().then(() => {
            element.style.padding = ''; // Reset
            showAlert('success', 'Succès', 'Le téléchargement du PDF a été lancé.');
        });
    }

    document.addEventListener('DOMContentLoaded', loadEleves);
</script>
